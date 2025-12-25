<?php

use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use App\Models\Client;
use App\Models\Project;
use App\Models\Workspace;
use App\Imports\UsersImport;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Spatie\Permission\Models\Role as SpatieRole;

beforeEach(function () {
    // Fake storage for file uploads
    Storage::fake('public');

    // Create or use existing workspace
    $this->workspace = Workspace::first() ?? Workspace::create(['title' => 'Default Workspace']);

    // Bind workspace helper
    $this->app->bind('getWorkspaceId', fn() => $this->workspace->id);

    // Create admin role
    $this->role = SpatieRole::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

    // Create admin user
    $this->adminUser = User::create([
        'id' => 1,
        'first_name' => 'Admin',
        'last_name' => 'User',
        'email' => 'admin@example.com',
        'password' => Hash::make('password123'),
        'status' => 1,
        'email_verified_at' => now(),
    ]);

    $this->adminUser->assignRole($this->role->name);
    $this->adminUser->workspaces()->attach($this->workspace->id);

    // Mock email configuration to return true
    $this->app->bind('isEmailConfigured', fn() => true);

    // Authenticate as admin user
    $this->actingAs($this->adminUser, 'web');
    $this->withSession(['workspace_id' => $this->workspace->id]);

    // Bind helper for main admin ID
    $this->app->bind('getMainAdminId', fn() => $this->adminUser->id);

    // Mock middleware permissions
    $this->app->bind('checkPermission', fn($permission) => true);

    // Mock formatApiResponse
    $this->app->bind('formatApiResponse', function ($error, $message, $data = []) {
        return response()->json(array_merge(['error' => $error, 'message' => $message], $data));
    });
});

// Helper function to create a user payload
function userPayload($overrides = [])
{
    return array_merge([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => test()->role->id,
        'status' => 1,
        'require_ev' => 0,
    ], $overrides);
}

// Helper function to make HTTP requests
function makeUserRequest($method, $url, $payload = [])
{
    return test()->$method($url, $payload);
}

// ------------------------
// User Creation Tests
// ------------------------
describe('User Creation', function () {
    it('can create a user with valid data', function () {
        $response = makeUserRequest('postJson', '/users/store', userPayload());

        $response->assertStatus(200)
            ->assertJson(['error' => false, 'message' => 'User created successfully.']);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'status' => 1,
        ]);

        $this->assertDatabaseHas('model_has_roles', [
            'model_id' => User::where('email', 'john@example.com')->first()->id,
            'role_id' => $this->role->id,
        ]);

        $this->assertDatabaseHas('user_workspace', [
            'workspace_id' => $this->workspace->id,
            'user_id' => User::where('email', 'john@example.com')->first()->id,
        ]);
    });

    it('validates required fields on user creation', function () {
        $response = makeUserRequest('postJson', '/users/store', ['require_ev' => 0]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['first_name', 'last_name', 'email', 'password', 'role', 'status']);
    });

    it('validates unique email on user creation', function () {
        User::create(userPayload(['email' => 'existing@example.com']));
        $response = makeUserRequest('postJson', '/users/store', userPayload(['email' => 'existing@example.com']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    it('validates password confirmation on user creation', function () {
        $response = makeUserRequest('postJson', '/users/store', userPayload(['password_confirmation' => 'wrongpassword']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password_confirmation']);
    });

    it('fails to create user if email settings are not configured and require_ev is 1', function () {
        $this->app->bind('isEmailConfigured', fn() => false);

        $response = makeUserRequest('postJson', '/users/store', userPayload(['require_ev' => 1]));

        $response->assertStatus(422)
            ->assertJson(['error' => true, 'message' => 'Email settings are not configured. Please configure email settings to enable email verification.']);
    });
});

// ------------------------
// User Update Tests
// ------------------------
describe('User Update', function () {
    it('can update a user with valid data', function () {
        $user = User::create(userPayload(['email' => 'update@example.com']));
        $user->workspaces()->attach($this->workspace->id);

        $updateData = userPayload([
            'id' => $user->id,
            'first_name' => 'Updated',
            'email' => 'updated@example.com',
        ]);

        $response = makeUserRequest('putJson', "/users/update_user/{$user->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson(['error' => false, 'message' => 'User updated successfully.']);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'first_name' => 'Updated',
            'email' => 'updated@example.com',
        ]);
    });

    it('validates unique email on user update', function () {
        $user1 = User::create(userPayload(['email' => 'user1@example.com']));
        $user2 = User::create(userPayload(['email' => 'user2@example.com']));

        $response = makeUserRequest('putJson', "/users/update_user/{$user2->id}", userPayload([
            'id' => $user2->id,
            'email' => 'user1@example.com',
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    it('cannot update non-existent user', function () {
        $response = makeUserRequest('putJson', '/users/update_user/999', userPayload(['id' => 999]));

        $response->assertStatus(200)
            ->assertJson(['error' => true, 'message' => 'User not found.']);
    });

    it('can update profile photo', function () {
        $user = User::create(userPayload(['email' => 'photo@example.com']));
        $file = \Illuminate\Http\UploadedFile::fake()->image('profile.jpg');

        $response = makeUserRequest('putJson', "/profile/update_photo", array_merge(['upload' => $file], userPayload()));

        $response->assertStatus(200);

        $user->refresh();
        expect($user->photo)->not->toBe('photos/no-image.jpg');
        Storage::disk('public')->assertExists($user->photo);
    });
});

// ------------------------
// User Deletion Tests
// ------------------------
describe('User Deletion', function () {
    it('can delete a user', function () {
        $user = User::create(userPayload(['email' => 'delete@example.com']));
        $user->workspaces()->attach($this->workspace->id);

        $response = makeUserRequest('deleteJson', "/users/delete_user/{$user->id}");

        $response->assertStatus(200)
            ->assertJson(['error' => false, 'message' => 'User deleted successfully.']);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    });

    it('cannot delete the main admin', function () {
        // Get the actual main admin ID
        $mainAdminId = getMainAdminId();

        $response = makeUserRequest('deleteJson', "/users/delete_user/{$mainAdminId}");
        $response->assertStatus(200)
            ->assertJson(['error' => true, 'message' => 'The main admin account cannot be deleted.']);
    });

    it('can delete multiple users', function () {
        $user1 = User::create(userPayload(['email' => 'user1@example.com']));
        $user2 = User::create(userPayload(['email' => 'user2@example.com']));
        $user1->workspaces()->attach($this->workspace->id);
        $user2->workspaces()->attach($this->workspace->id);

        $response = makeUserRequest('postJson', '/users/delete_multiple_user', ['ids' => [$user1->id, $user2->id]]);

        $response->assertStatus(200)
            ->assertJson(['error' => false, 'message' => 'User(s) deleted successfully.']);

        $this->assertDatabaseMissing('users', ['id' => $user1->id]);
        $this->assertDatabaseMissing('users', ['id' => $user2->id]);
    });

    it('skips main admin in multiple user deletion', function () {
        $user = User::create(userPayload(['email' => 'user@example.com']));
        $user->workspaces()->attach($this->workspace->id);

        $mainAdminId = getMainAdminId();

        $response = makeUserRequest('postJson', '/users/delete_multiple_user', ['ids' => [$mainAdminId, $user->id]]);

        $response->assertStatus(200)
            ->assertJson(['error' => false, 'message' => 'Users deleted successfully except the main admin.']);

        $this->assertDatabaseHas('users', ['id' => $this->adminUser->id]);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    });
});

// ------------------------
// User Listing Tests
// ------------------------
describe('User Listing', function () {
    it('can list users with expected structure', function () {
        $user = User::create(userPayload(['email' => 'list@example.com']));
        $user->workspaces()->attach($this->workspace->id);

        $response = makeUserRequest('getJson', '/users/list');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'rows' => [
                    '*' => [
                        'id',
                        'first_name',
                        'last_name',
                        'email',
                        'status',
                        'created_at',
                        'updated_at',
                        'actions'
                    ]
                ],
                'total'
            ]);
    });

    it('can search users by name or email', function () {
        $user = User::create(userPayload(['first_name' => 'Jane', 'email' => 'jane@example.com']));
        $user->workspaces()->attach($this->workspace->id);

        $response = makeUserRequest('getJson', '/users/list?search=Jane');

        $response->assertStatus(200);
        expect(collect($response->json('rows'))->pluck('first_name'))->toContain('Jane');
    });

    it('can filter users by role', function () {
        $editorRole = SpatieRole::create(['name' => 'editor', 'guard_name' => 'web']);
        $user = User::create(userPayload(['email' => 'editor@example.com']));
        $user->assignRole('editor');
        $user->workspaces()->attach($this->workspace->id);

        $response = makeUserRequest('getJson', '/users/list?role_ids[]=' . $editorRole->id);

        $response->assertStatus(200);
        expect(collect($response->json('rows'))->pluck('email'))->toContain('editor@example.com');
    });

    it('can filter users by status', function () {
        $activeUser = User::create(userPayload(['email' => 'active@example.com', 'status' => 1]));
        $inactiveUser = User::create(userPayload(['email' => 'inactive@example.com', 'status' => 0]));
        $activeUser->workspaces()->attach($this->workspace->id);
        $inactiveUser->workspaces()->attach($this->workspace->id);

        $response = makeUserRequest('getJson', '/users/list?statuses[]=1');

        $response->assertStatus(200);
        expect(collect($response->json('rows'))->pluck('email'))->toContain('active@example.com');
        expect(collect($response->json('rows'))->pluck('email'))->not->toContain('inactive@example.com');
    });

    it('can sort users by different fields', function () {
        $user1 = User::create(userPayload(['email' => 'a@example.com', 'first_name' => 'Alpha']));
        $user2 = User::create(userPayload(['email' => 'b@example.com', 'first_name' => 'Beta']));
        $user1->workspaces()->attach($this->workspace->id);
        $user2->workspaces()->attach($this->workspace->id);

        $response = makeUserRequest('getJson', '/users/list?sort=first_name&order=ASC');

        $response->assertStatus(200);
        $rows = $response->json('rows');
        expect($rows[0]['first_name'])->toBe('Garcia');
    });
});

// ------------------------
// User Profile Tests
// ------------------------
describe('User Profile', function () {
    it('can view user profile', function () {
        $user = User::create(userPayload(['email' => 'profile@example.com']));
        $user->workspaces()->attach($this->workspace->id);

        $response = makeUserRequest('get', "/users/profile/{$user->id}");

        $response->assertStatus(200)
            ->assertViewIs('users.user_profile')
            ->assertViewHas('user', fn($viewUser) => $viewUser->id === $user->id);
    });

    it('returns 404 for non-existent user profile', function () {
        $response = makeUserRequest('get', '/users/profile/999');

        $response->assertStatus(404);
    });


    it('can view profile show page', function() {

        $user = User::Create(userPayload());

        $response = makeUserRequest('get', "/account/{$user->id}");

        $response->assertStatus(200)
            ->assertViewIs('users.account')
            ->assertViewHas('roles');
    });

    it('can update user profile', function () {

        $user = User::create(userPayload());

        $response = makeUserRequest('put',"/profile/update/{$user->id}", userPayload(['first_name' => 'John Updated']));

        $response->assertStatus(200)
            ->assertJson(['error' => false]);

        $this->assertDatabaseHas('users', ['first_name' => 'John Updated']);
    });

});

// ------------------------
// User Views Tests
// ------------------------
describe('User Views', function () {
    it('can view users index page', function () {
        $response = makeUserRequest('get', '/users');

        $response->assertStatus(200)
            ->assertViewIs('users.users')
            ->assertViewHas('users');
    });

    it('can view create user form', function () {
        $response = makeUserRequest('get', '/users/create');

        $response->assertStatus(200)
            ->assertViewIs('users.create_user');
    });

    it('can view edit user form', function () {
        $user = User::create(userPayload(['email' => 'edit@example.com']));
        $user->workspaces()->attach($this->workspace->id);

        $response = makeUserRequest('get', "/users/edit/{$user->id}");

        $response->assertStatus(200)
            ->assertViewIs('users.edit_user')
            ->assertViewHas('user', fn($viewUser) => $viewUser->id === $user->id);
    });
});

// ------------------------
// Bulk Upload Tests
// ------------------------
describe('Bulk Upload', function () {
    it('can show bulk upload form', function () {
        $response = makeUserRequest('get', '/users/bulk-upload');

        $response->assertStatus(200)
            ->assertViewIs('bulk-upload')
            ->assertViewHas('entity', 'users');
    });

    it('can import users from valid file', function () {
        Excel::fake();

        $file = \Illuminate\Http\UploadedFile::fake()->create('users.xlsx', 1000, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $response = makeUserRequest('postJson', '/users/process-bulk-upload', ['bulk_file' => $file]);

        $response->assertStatus(200)
            ->assertJson(['error' => false, 'message' => 'Users imported successfully.']);

        Excel::assertImported('users.xlsx');
    });

    it('validates file type for bulk upload', function () {
        $file = \Illuminate\Http\UploadedFile::fake()->create('invalid.txt', 1000, 'text/plain');

        $response = makeUserRequest('postJson', '/users/process-bulk-upload', ['bulk_file' => $file]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['bulk_file']);
    });

    it('handles validation errors during bulk import', function () {
        $file = \Illuminate\Http\UploadedFile::fake()->create(
            'users.xlsx',
            1000,
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );

        // Fake Excel::import and inject a fake import with errors
        Excel::shouldReceive('import')
            ->once()
            ->andReturnUsing(function ($import, $fileArg) {
                // Mimic the import running and then return fake validation errors
                $import->validationErrors = ['Row 2: Email is required'];
            });

        // Mock UsersImport
        $this->mock(\App\Imports\UsersImport::class, function ($mock) {
            $mock->shouldReceive('getValidationErrors')
                ->andReturn(['Row 2: Email is required']);
        });

        $response = makeUserRequest('postJson', '/users/process-bulk-upload', [
            'bulk_file' => $file,
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'error' => true,
                'message' => 'Validation errors occurred.',
            ]);
    });
});

// ------------------------
// Get Mentions Tests
// ------------------------
describe('Get Mentions', function () {
    it('can get mentions for a project', function () {
        $project = Project::create(['title' => 'Test Project', 'workspace_id' => $this->workspace->id, 'created_by' => $this->adminUser->id]);
        $user = User::create(userPayload(['email' => 'mention@example.com']));
        $user->workspaces()->attach($this->workspace->id);
        $user->projects()->attach($project->id);

        $response = makeUserRequest('getJson', '/users/get-mentions?mention_type=project&mention_id=' . $project->id);

        $response->assertStatus(200)
            ->assertJsonFragment(['value' => 'John Doe', 'type' => 'user']);
    });

    it('returns error for invalid mention type', function () {
        $response = makeUserRequest('getJson', '/users/get-mentions?mention_type=invalid&mention_id=1');

        $response->assertStatus(400)
            ->assertJson(['error' => 'Invalid mention_type']);
    });
});

// ------------------------
// Email Verification Tests
// ------------------------
describe('Email Verification', function () {
    it('shows verification notice for unverified user', function () {
        $user = User::create(userPayload(['email' => 'unverified@example.com', 'email_verified_at' => null]));
        $user->workspaces()->attach($this->workspace->id);
        $user->assignRole($this->role->name);
        $this->actingAs($user, 'web');

        $response = makeUserRequest('get', '/email/verify');

        $response->assertStatus(200)
            ->assertViewIs('auth.verification-notice');
    });
});

// ------------------------
// Date Validation Tests
// ------------------------
describe('Date Validation', function () {
    it('validates date of birth format', function () {
        // Mock date validation function (assuming User model has a dob field)
        $this->app->bind('validate_date_format_and_order', function ($startDate, $endDate, $format = null, $startDateLabel = '', $endDateLabel = '', $startDateKey = '', $endDateKey = '') {
            if ($startDate && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
                return [$startDateKey => ['Invalid date format']];
            }
            return [];
        });

        $response = makeUserRequest('postJson', '/users/store', userPayload(['dob' => 'invalid-date']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['dob']);
    });

    it('validates date of joining format', function () {
        // Mock date validation function (assuming User model has a doj field)
        $this->app->bind('validate_date_format_and_order', function ($startDate, $endDate, $format = null, $startDateLabel = '', $endDateLabel = '', $startDateKey = '', $endDateKey = '') {
            if ($endDate && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
                return [$endDateKey => ['Invalid date format']];
            }
            return [];
        });

        $response = makeUserRequest('postJson', '/users/store', userPayload(['doj' => 'invalid-date']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['doj']);
    });
});

// ------------------------
// User API Tests
// ------------------------
describe('User API', function () {
    it('can list users via API', function () {
        $user = User::create(userPayload(['email' => 'api-list@example.com']));
        $user->workspaces()->attach($this->workspace->id);

        $response = makeUserRequest('getJson', '/api/users?isApi=true');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'error',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'first_name',
                        'last_name',
                        'email',
                        'status'
                    ]
                ]
            ]);
    });

    it('can create user via API', function () {
        $payload = userPayload(['isApi' => true]);

        $response = makeUserRequest('postJson', '/users/store', $payload);

        $response->assertStatus(200)
            ->assertJson(['error' => false, 'message' => 'User created successfully.']);
    });

    it('can update user via API', function () {
        $user = User::create(userPayload(['email' => 'api-update@example.com']));

        $updateData = userPayload([
            'id' => $user->id,
            'first_name' => 'Updated API',
            'isApi' => true
        ]);

        $response = makeUserRequest('putJson', "/users/update_user/{$user->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson(['error' => false, 'message' => 'User updated successfully.']);
    });
});

// ------------------------
// Edge Cases and Error Handling
// ------------------------
describe('Edge Cases and Error Handling', function () {

    it('handles image file validation', function () {
        $file = \Illuminate\Http\UploadedFile::fake()->create('invalid.txt', 1000, 'text/plain');

        $response = makeUserRequest('putJson', '/profile/update_photo', array_merge(['upload' => $file], userPayload()));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['upload']);
    });

    it('handles phone validation with country code requirement', function () {
        // Assuming User model has phone and country_code fields
        $response = makeUserRequest('postJson', '/users/store', userPayload([
            'phone' => '1234567890',
            'country_code' => null
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['country_code']);
    });

    it('handles country code validation with phone requirement', function () {
        // Assuming User model has phone and country_code fields
        $response = makeUserRequest('postJson', '/users/store', userPayload([
            'phone' => null,
            'country_code' => '+1'
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    });

    it('validates unique phone and country code combination', function () {
        // Assuming User model has phone and country_code fields
        User::create(userPayload(['phone' => '1234567890', 'country_code' => '+1']));

        $response = makeUserRequest('postJson', '/users/store', userPayload([
            'phone' => '1234567890',
            'country_code' => '+1',
            'email' => 'newuser@example.com'
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    });
});
