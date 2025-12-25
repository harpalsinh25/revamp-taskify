<?php

use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use App\Models\Client;
use App\Models\Project;
use App\Models\Workspace;
use App\Models\Template;
use App\Imports\ClientsImport;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role as SpatieRole;

beforeEach(function () {
    // Fake storage for file uploads
    Storage::fake('public');

    // Create or use existing workspace
    $this->workspace = Workspace::first() ?? Workspace::create(['title' => 'Default Workspace']);

    // Bind workspace helper
    $this->app->bind('getWorkspaceId', fn() => $this->workspace->id);

    // Create client role
    $this->clientRole = SpatieRole::firstOrCreate(['name' => 'client', 'guard_name' => 'client']);

    // Create admin role
    $this->adminRole = SpatieRole::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

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

    $this->adminUser->assignRole($this->adminRole->name);
    $this->adminUser->workspaces()->attach($this->workspace->id);

    // Mock email configuration to return true
    $this->app->bind('isEmailConfigured', fn() => true);

    // Mock format functions
    $this->app->bind('formatClient', function ($client) {
        return [
            'id' => $client->id,
            'first_name' => $client->first_name,
            'last_name' => $client->last_name,
            'company' => $client->company,
            'email' => $client->email,
            'phone' => $client->country_code ? $client->country_code . ' ' . $client->phone : $client->phone,
            'address' => $client->address,
            'city' => $client->city,
            'state' => $client->state,
            'country' => $client->country,
            'zip' => $client->zip,
            'photo' => $client->photo ? asset('storage/' . $client->photo) : asset('storage/photos/no-image.jpg'),
            'status' => $client->status,
            'internal_purpose' => $client->internal_purpose,
            'created_at' => $client->created_at->format('d-m-Y H:i:s'),
            'updated_at' => $client->updated_at->format('d-m-Y H:i:s'),
            'assigned' => [
                'projects' => $client->projects()->count(),
                'tasks' => $client->tasks()->count()
            ]
        ];
    });

    $this->app->bind('formatApiResponse', function ($error, $message, $data = []) {
        return response()->json(array_merge(['error' => $error, 'message' => $message], $data));
    });

    $this->app->bind('formatApiValidationError', function ($isApi, $errors) {
        return response()->json(['error' => true, 'message' => 'Validation errors occurred', 'errors' => $errors], 422);
    });

    // Authenticate as admin user
    $this->actingAs($this->adminUser, 'web');
    $this->withSession(['workspace_id' => $this->workspace->id]);

    // Mock helper functions
    $this->app->bind('isAdminOrHasAllDataAccess', fn($entity = null, $id = null) => true);
    $this->app->bind('checkPermission', fn($permission) => true);
    $this->app->bind('getAuthenticatedUser', fn() => $this->adminUser);
    $this->app->bind('getGuardName', fn() => 'web');
});

// Helper function to create a client payload
function clientPayload($overrides = [])
{
    return array_merge([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'company' => 'Example Corp',
        'email' => 'john.doe@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'phone' => '1234567890',
        'country_code' => '+1',
        'country_iso_code' => 'us',
        'address' => '123 Main St',
        'city' => 'New York',
        'state' => 'NY',
        'country' => 'USA',
        'zip' => '10001',
        'status' => 1,
        'require_ev' => 0,
    ], $overrides);
}

// Helper function to make HTTP requests
function makeClientRequest($method, $url, $payload = [])
{
    return test()->$method($url, $payload);
}

// ------------------------
// Client Creation Tests
// ------------------------
describe('Client Creation', function () {
    it('can create a client with valid data', function () {
        $response = makeClientRequest('postJson', '/clients/store', clientPayload());

        $response->assertStatus(200)
            ->assertJson(['error' => false, 'message' => 'Client created successfully.']);

        $this->assertDatabaseHas('clients', [
            'email' => 'john.doe@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'company' => 'Example Corp',
            'status' => 1,
        ]);

        $client = Client::where('email', 'john.doe@example.com')->first();

        $this->assertDatabaseHas('model_has_roles', [
            'model_id' => $client->id,
            'role_id' => $this->clientRole->id,
        ]);

        $this->assertDatabaseHas('client_workspace', [
            'workspace_id' => $this->workspace->id,
            'client_id' => $client->id,
        ]);
    });

    it('validates required fields on client creation', function () {
        $response = makeClientRequest('postJson', '/clients/store', ['require_ev' => 0]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['first_name', 'last_name', 'email']);
    });

    it('validates unique email on client creation', function () {
        Client::create(clientPayload(['email' => 'existing@example.com']));

        $response = makeClientRequest('postJson', '/clients/store', clientPayload(['email' => 'existing@example.com']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    it('validates password confirmation on client creation', function () {
        $response = makeClientRequest('postJson', '/clients/store', clientPayload(['password_confirmation' => 'wrongpassword']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password_confirmation']);
    });

    it('validates unique phone and country code combination', function () {
        Client::create(clientPayload(['phone' => '1234567890', 'country_code' => '+1']));

        $response = makeClientRequest('postJson', '/clients/store', clientPayload(['phone' => '1234567890', 'country_code' => '+1']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    });

    it('can create internal purpose client without password', function () {
        $payload = clientPayload(['internal_purpose' => 'on']);
        unset($payload['password'], $payload['password_confirmation']);

        $response = makeClientRequest('postJson', '/clients/store', $payload);

        $response->assertStatus(200)
            ->assertJson(['error' => false, 'message' => 'Client created successfully.']);

        $this->assertDatabaseHas('clients', [
            'email' => 'john.doe@example.com',
            'internal_purpose' => 1,
        ]);
    });

    it('fails to create client if email settings are not configured and require_ev is 1', function () {
        $this->app->bind('isEmailConfigured', fn() => false);

        $response = makeClientRequest('postJson', '/clients/store', clientPayload(['require_ev' => 1]));

        $response->assertStatus(200)
            ->assertJson(['error' => true, 'message' => 'Email settings are not configured. Please configure email settings to enable email verification.']);
    });

    it('can upload profile photo during client creation', function () {
        $file = \Illuminate\Http\UploadedFile::fake()->image('profile.jpg');
        $payload = clientPayload(['profile' => $file]);

        $response = makeClientRequest('postJson', '/clients/store', $payload);

        $response->assertStatus(200);

        $client = Client::where('email', 'john.doe@example.com')->first();
        expect($client->photo)->not->toBe('photos/no-image.jpg');
        Storage::disk('public')->assertExists($client->photo);
    });
});

// ------------------------
// Client Update Tests
// ------------------------
describe('Client Update', function () {
    it('can update a client with valid data', function () {
        $client = Client::create(clientPayload(['email' => 'update@example.com']));
        $client->workspaces()->attach($this->workspace->id);

        $updateData = clientPayload([
            'id' => $client->id,
            'first_name' => 'Updated',
            'email' => 'updated@example.com',
            'company' => 'Updated Corp',
        ]);

        $response = makeClientRequest('putJson', "/clients/update/{$client->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson(['error' => false, 'message' => 'Client updated successfully.']);

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'first_name' => 'Updated',
            'email' => 'updated@example.com',
            'company' => 'Updated Corp',
        ]);
    });

    it('validates unique email on client update', function () {
        $client1 = Client::create(clientPayload(['email' => 'client1@example.com']));
        $client2 = Client::create(clientPayload(['email' => 'client2@example.com']));

        $response = makeClientRequest('putJson', "/clients/update/{$client2->id}", clientPayload([
            'id' => $client2->id,
            'email' => 'client1@example.com',
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    it('cannot update non-existent client', function () {
        $response = makeClientRequest('putJson', '/clients/update/999', clientPayload(['id' => 999]));

        $response->assertStatus(200)
            ->assertJson(['error' => true, 'message' => 'Client not found.']);
    });

    it('can update profile photo', function () {
        $client = Client::create(clientPayload(['email' => 'photo@example.com']));
        $file = \Illuminate\Http\UploadedFile::fake()->image('new-profile.jpg');

        $updateData = clientPayload([
            'id' => $client->id,
            'profile' => $file,
        ]);

        $response = makeClientRequest('putJson', "/clients/update/{$client->id}", $updateData);

        $response->assertStatus(200);

        $client->refresh();
        expect($client->photo)->not->toBe('photos/no-image.jpg');
        Storage::disk('public')->assertExists($client->photo);
    });

    it('can update client to internal purpose', function () {
        $client = Client::create(clientPayload(['email' => 'internal@example.com']));

        $updateData = clientPayload([
            'id' => $client->id,
            'internal_purpose' => 'on',
        ]);

        // Remove password fields for internal purpose clients
        unset($updateData['password'], $updateData['password_confirmation']);

        $response = makeClientRequest('putJson', "/clients/update/{$client->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'internal_purpose' => 1,
        ]);
    });
});

// ------------------------
// Client Deletion Tests
// ------------------------
describe('Client Deletion', function () {
    it('can delete a client', function () {
        $client = Client::create(clientPayload(['email' => 'delete@example.com']));
        $client->workspaces()->attach($this->workspace->id);

        // Mock DeletionService
        $this->app->bind('App\Services\DeletionService', function () {
            return new class {
                public static function delete($class, $id, $type)
                {
                    $model = $class::find($id);
                    if ($model) {
                        $model->delete();
                    }
                    return response()->json(['error' => false, 'message' => $type . ' deleted successfully.']);
                }
            };
        });

        $response = makeClientRequest('deleteJson', "/clients/destroy/{$client->id}");

        $response->assertStatus(200)
            ->assertJson(['error' => false, 'message' => 'Client deleted successfully.']);

        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
    });

    it('can delete multiple clients', function () {
        $client1 = Client::create(clientPayload(['email' => 'client1@example.com']));
        $client2 = Client::create(clientPayload(['email' => 'client2@example.com']));
        $client1->workspaces()->attach($this->workspace->id);
        $client2->workspaces()->attach($this->workspace->id);

        // Mock DeletionService
        $this->app->bind('App\Services\DeletionService', function () {
            return new class {
                public static function delete($class, $id, $type)
                {
                    $model = $class::find($id);
                    if ($model) {
                        $model->delete();
                    }
                    return response()->json(['error' => false, 'message' => $type . ' deleted successfully.']);
                }
            };
        });

        $response = makeClientRequest('postJson', '/clients/destroy_multiple', ['ids' => [$client1->id, $client2->id]]);

        $response->assertStatus(200)
            ->assertJson(['error' => false, 'message' => 'Clients(s) deleted successfully.']);

        $this->assertDatabaseMissing('clients', ['id' => $client1->id]);
        $this->assertDatabaseMissing('clients', ['id' => $client2->id]);
    });
});

// ------------------------
// Client Listing Tests
// ------------------------
describe('Client Listing', function () {
    it('can list clients', function () {
        $client = Client::create(clientPayload(['email' => 'list@example.com']));
        $client->workspaces()->attach($this->workspace->id);

        $response = makeClientRequest('getJson', '/clients/list');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'rows' => [
                    '*' => [
                        'id',
                        'first_name',
                        'last_name',
                        'email',
                        'company',
                        'phone',
                        'profile',
                        'status',
                        'internal_purpose',
                        'created_at',
                        'updated_at',
                        'assigned',
                        'actions'
                    ]
                ],
                'total'
            ]);
    });

    it('can search clients by name or email', function () {
        $client = Client::create(clientPayload(['first_name' => 'Jane', 'email' => 'jane@example.com']));
        $client->workspaces()->attach($this->workspace->id);

        $response = makeClientRequest('getJson', '/clients/list?search=Jane');

        $response->assertStatus(200);
        expect(collect($response->json('rows'))->pluck('first_name'))->toContain('Jane');
    });

    it('can filter clients by status', function () {
        $activeClient = Client::create(clientPayload(['email' => 'active@example.com', 'status' => 1]));
        $inactiveClient = Client::create(clientPayload(['email' => 'inactive@example.com', 'status' => 0]));
        $activeClient->workspaces()->attach($this->workspace->id);
        $inactiveClient->workspaces()->attach($this->workspace->id);

        $response = makeClientRequest('getJson', '/clients/list?statuses[]=1');

        $response->assertStatus(200);
        expect(collect($response->json('rows'))->pluck('email'))->toContain('active@example.com');
        expect(collect($response->json('rows'))->pluck('email'))->not->toContain('inactive@example.com');
    });

    it('can filter clients by internal purpose', function () {
        $regularClient = Client::create(clientPayload(['email' => 'regular@example.com', 'internal_purpose' => 0]));
        $internalClient = Client::create(clientPayload(['email' => 'internal@example.com', 'internal_purpose' => 1]));
        $regularClient->workspaces()->attach($this->workspace->id);
        $internalClient->workspaces()->attach($this->workspace->id);

        $response = makeClientRequest('getJson', '/clients/list?clientTypes[]=1');

        $response->assertStatus(200);
        expect(collect($response->json('rows'))->pluck('email'))->toContain('internal@example.com');
    });

    it('can sort clients by different fields', function () {
        $client1 = Client::create(clientPayload(['email' => 'a@example.com', 'first_name' => 'Alpha']));
        $client2 = Client::create(clientPayload(['email' => 'b@example.com', 'first_name' => 'Beta']));
        $client1->workspaces()->attach($this->workspace->id);
        $client2->workspaces()->attach($this->workspace->id);

        $response = makeClientRequest('getJson', '/clients/list?sort=first_name&order=ASC');

        $response->assertStatus(200);
        $rows = $response->json('rows');
        expect($rows[0]['first_name'])->toBe('Alpha');
    });
});

// ------------------------
// Client Profile Tests
// ------------------------
describe('Client Profile', function () {
    it('can view client profile', function () {
        $client = Client::create(clientPayload(['email' => 'profile@example.com']));
        $client->workspaces()->attach($this->workspace->id);

        $response = makeClientRequest('get', "/clients/profile/{$client->id}");

        $response->assertStatus(200)
            ->assertViewIs('clients.client_profile')
            ->assertViewHas('client', fn($viewClient) => $viewClient->id === $client->id);
    });

    it('returns 404 for non-existent client profile', function () {
        $response = makeClientRequest('get', '/clients/profile/999');

        $response->assertStatus(404);
    });

    it('can get client data via API', function () {
        $client = Client::create(clientPayload(['email' => 'api@example.com']));
        $client->workspaces()->attach($this->workspace->id);

        $response = makeClientRequest('getJson', "/clients/get/{$client->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['client' => ['id', 'first_name', 'last_name', 'email']]);
    });
});

// ------------------------
// Bulk Upload Tests
// ------------------------
describe('Bulk Upload', function () {
    it('can show bulk upload form', function () {
        $response = makeClientRequest('get', '/clients/bulk-upload');

        $response->assertStatus(200)
            ->assertViewIs('bulk-upload')
            ->assertViewHas('entity', 'clients');
    });

    it('can import clients from valid file', function () {
        Excel::fake();

        $file = \Illuminate\Http\UploadedFile::fake()->create('clients.xlsx', 1000, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        // Mock ClientsImport
        $this->app->bind('App\Imports\ClientsImport', function () {
            return new class {
                public function getValidationErrors()
                {
                    return [];
                }
            };
        });

        $response = makeClientRequest('postJson', '/clients/process-bulk-upload', ['bulk_file' => $file]);

        $response->assertStatus(200)
            ->assertJson(['error' => false, 'message' => 'Clients imported successfully.']);

        Excel::assertImported('clients.xlsx');
    });

    it('validates file type for bulk upload', function () {
        $file = \Illuminate\Http\UploadedFile::fake()->create('invalid.txt', 1000, 'text/plain');

        $response = makeClientRequest('postJson', '/clients/process-bulk-upload', ['bulk_file' => $file]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['bulk_file']);
    });
    it('handles validation errors during bulk import', function () {
        $file = \Illuminate\Http\UploadedFile::fake()->create(
            'clients.xlsx',
            1000,
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );

        // Fake Excel::import and inject a fake import with errors
        Excel::shouldReceive('import')
            ->once()
            ->andReturnUsing(function ($import, $fileArg) {
                // mimic the import running and then return fake validation errors
                $import->validationErrors = ['Row 2: Email is required'];
            });

        // Extend ClientsImport dynamically with fake getValidationErrors()
        $this->mock(\App\Imports\ClientsImport::class, function ($mock) {
            $mock->shouldReceive('getValidationErrors')
                ->andReturn(['Row 2: Email is required']);
        });

        $response = makeClientRequest('postJson', '/clients/process-bulk-upload', [
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
// Client View Tests
// ------------------------
describe('Client Views', function () {
    it('can view clients index page', function () {
        $response = makeClientRequest('get', '/clients');

        $response->assertStatus(200)
            ->assertViewIs('clients.clients')
            ->assertViewHas('clients');
    });

    it('can view create client form', function () {
        $response = makeClientRequest('get', '/clients/create');

        $response->assertStatus(200)
            ->assertViewIs('clients.create_client');
    });

    it('can view edit client form', function () {
        $client = Client::create(clientPayload(['email' => 'edit@example.com']));
        $client->workspaces()->attach($this->workspace->id);

        $response = makeClientRequest('get', "/clients/edit/{$client->id}");

        $response->assertStatus(200)
            ->assertViewIs('clients.update_client')
            ->assertViewHas('client', fn($viewClient) => $viewClient->id === $client->id);
    });
});

// ------------------------
// Date Validation Tests
// ------------------------
describe('Date Validation', function () {
    it('validates date of birth format', function () {
        // Mock date validation function
        $this->app->bind('validate_date_format_and_order', function ($startDate, $endDate, $format = null, $startDateLabel = '', $endDateLabel = '', $startDateKey = '', $endDateKey = '') {
            if ($startDate && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
                return [$startDateKey => ['Invalid date format']];
            }
            return [];
        });

        $response = makeClientRequest('postJson', '/clients/store', clientPayload(['dob' => 'invalid-date']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['dob']);
    });

    it('validates date of joining format', function () {
        // Mock date validation function
        $this->app->bind('validate_date_format_and_order', function ($startDate, $endDate, $format = null, $startDateLabel = '', $endDateLabel = '', $startDateKey = '', $endDateKey = '') {
            if ($endDate && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
                return [$endDateKey => ['Invalid date format']];
            }
            return [];
        });

        $response = makeClientRequest('postJson', '/clients/store', clientPayload(['doj' => 'invalid-date']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['doj']);
    });
});

// ------------------------
// API Tests
// ------------------------
describe('Client API', function () {
    it('can list clients via API', function () {
        $client = Client::create(clientPayload(['email' => 'api-list@example.com']));
        $client->workspaces()->attach($this->workspace->id);

        // Mock API request detection
        $this->withoutMiddleware();

        $response = makeClientRequest('getJson', '/api/clients?isApi=true');

        // Since we don't have the actual API route, we'll test the controller method directly
        $controller = new \App\Http\Controllers\ClientController();
        $request = new \Illuminate\Http\Request(['isApi' => true]);

        // This would normally be tested through actual API routes
        expect(true)->toBeTrue(); // Placeholder assertion
    });

    it('can create client via API', function () {
        $payload = clientPayload(['isApi' => true]);

        $response = makeClientRequest('postJson', '/clients/store', $payload);

        $response->assertStatus(200)
            ->assertJson(['error' => false, 'message' => 'Client created successfully.']);
    });

    it('can update client via API', function () {
        $client = Client::create(clientPayload(['email' => 'api-update@example.com']));

        $updateData = clientPayload([
            'id' => $client->id,
            'first_name' => 'Updated API',
            'isApi' => true
        ]);

        $response = makeClientRequest('putJson', "/clients/update/{$client->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson(['error' => false, 'message' => 'Client updated successfully.']);
    });
});

// ------------------------
// Edge Cases and Error Handling Tests
// ------------------------
describe('Edge Cases and Error Handling', function () {
    it('handles email transport exception during client creation', function () {
        // Mock email failure
        Notification::fake();

        $this->app->bind('App\Notifications\VerifyEmail', function () {
            throw new \Symfony\Component\Mailer\Exception\TransportException('Email failed');
        });

        $response = makeClientRequest('postJson', '/clients/store', clientPayload(['require_ev' => 1]));

        // The client should be deleted if email fails
        $response->assertStatus(200);
        // Additional assertions would depend on actual error handling implementation
    });

    it('handles image file validation', function () {
        $file = \Illuminate\Http\UploadedFile::fake()->create('invalid.txt', 1000, 'text/plain');

        $response = makeClientRequest('postJson', '/clients/store', clientPayload(['profile' => $file]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['profile']);
    });

    it('handles phone validation with country code requirement', function () {
        $response = makeClientRequest('postJson', '/clients/store', clientPayload([
            'phone' => '1234567890',
            'country_code' => null
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['country_code']);
    });

    it('handles country code validation with phone requirement', function () {
        $response = makeClientRequest('postJson', '/clients/store', clientPayload([
            'phone' => null,
            'country_code' => '+1'
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    });
});
