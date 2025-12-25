<?php

use App\Models\User;
use App\Models\Client;
use App\Models\Workspace;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    // Use the first workspace in the DB, or create one with existing columns only
    $this->workspace = Workspace::first();
    if (!$this->workspace) {
        $this->workspace = Workspace::create([
            'title' => 'Default Workspace',
            // Only existing columns
        ]);
    }

    // Bind workspace helper if your app uses it
    $this->app->bind('getWorkspaceId', fn() => $this->workspace->id);

    // Get an existing role
    $this->role = Role::firstOrCreate(['name'=> 'admin'], ['guard_name' => 'web']);

    // Create an admin user for authenticated requests
    $this->adminUser = User::create([
        'id' => 1, // Ensure ID 1 for main admin
        'first_name' => 'Admin',
        'last_name'  => 'User',
        'email'      => 'admin@example.com',
        'password'   => Hash::make('password123'),
        'status'     => 1,
        'email_verified_at' => now(), // Ensure email is verified
    ]);

    $this->adminUser->assignRole('admin');
    $this->adminUser->workspaces()->attach($this->workspace->id);

    // Authenticate as admin user for tests that require authentication
    $this->actingAs($this->adminUser);
    $this->withSession(['workspace_id' => $this->workspace->id]);
});

describe('User Authentication', function () {

    it('can login with valid credentials', function () {
        $user = User::create([
            'first_name' => 'Test',
            'last_name'  => 'User',
            'email'      => 'test@example.com',
            'password'   => Hash::make('password123'),
            'status'     => 1, // only include columns that exist
        ]);

        $user->assignRole($this->role->name);
        $this->workspace->users()->attach($user->id);

        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->postJson('/users/authenticate', [
                'email' => 'test@example.com',
                'password' => 'password123',
            ]);

        $response->assertStatus(200);
        $response->assertJson(['error' => false]);
        $this->assertAuthenticatedAs($user, 'web');
    });

    it('cannot login without assigned role', function () {
        $user = User::create([
            'first_name' => 'NoRole',
            'last_name'  => 'User',
            'email'      => 'norole@example.com',
            'password'   => Hash::make('password123'),
            'status'     => 1,
        ]);

        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->postJson('/users/authenticate', [
                'email' => 'norole@example.com',
                'password' => 'password123',
            ]);

        $response->assertStatus(200);
        $response->assertJson(['error' => true]);
        $this->assertGuest();
    });

    it('validates required fields on login', function () {
        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->postJson('/users/authenticate', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email', 'password']);
    });
});

describe('Client Authentication', function () {

    it('can login as client with valid credentials', function () {
        $client = Client::create([
            'first_name' => 'Test',
            'last_name'  => 'Client',
            'email'      => 'client@example.com',
            'password'   => Hash::make('password123'),
            'status'     => 1,
            // only columns that exist
        ]);

        $this->workspace->clients()->attach($client->id);

        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->postJson('/users/authenticate', [
                'email' => 'client@example.com',
                'password' => 'password123',
            ]);

        $response->assertStatus(200);
        $response->assertJson(['error' => false]);
        $this->assertAuthenticatedAs($client, 'client');
    });

    it('cannot login as inactive client', function () {
        $client = Client::create([
            'first_name' => 'Inactive',  // Fixed: removed extra space
            'last_name'  => 'Client',
            'email'      => 'client2@example.com',
            'password'   => Hash::make('password123'),
            'status'     => 0,  // Inactive status
        ]);

        // DON'T attach to workspace - inactive clients shouldn't be attached
        // Or attach but ensure your auth logic checks status
        $this->workspace->clients()->attach($client->id);

        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->postJson('/users/authenticate', [
                'email' => 'client2@example.com',
                'password' => 'password123',
            ]);

        $response->assertStatus(200);
        $response->assertJson(['error' => true]);
        $this->assertGuest('client');
    });
});

describe('User Registration', function () {

    it('can create user with valid data', function () {
        // Mock email configuration to return true
        $this->app->bind('isEmailConfigured', fn() => true);

        $response = $this->actingAs($this->adminUser, 'web')
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->postJson('/users/store', [
                'first_name' => 'John',
                'last_name'  => 'Doe',
                'email'      => 'john@example.com',
                'password'   => 'password123',
                'password_confirmation' => 'password123',
                'role'       => $this->role->id,
                'status'     => 1,
                'require_ev' => 0,  // Set to 0 to avoid email verification issues
                'email_verified_at' => now(), // try faking it
                'workspace_id' => $this->workspace->id, // Ensure workspace ID is sent
        ]);

        // dd($this->adminUser->toArray());
        $response->assertStatus(200);
        $response->assertJson(['error' => false]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
    });

    it('validates password confirmation on user creation', function () {
        $response = $this->actingAs($this->adminUser, 'web')
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->postJson('/users/store', [
                'first_name' => 'John',
                'last_name'  => 'Doe',
                'email'      => 'john2@example.com',
                'password'   => 'password123',
                'password_confirmation' => 'wrongpassword',  // Mismatched password
                'role'       => $this->role->id,
                'status'     => 1,
                'require_ev' => 0,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password_confirmation']);
    });
});

describe('User Update & Deletion', function () {
    it('can update user', function () {
        $user = User::create([
            'first_name' => 'Old',
            'last_name'  => 'Name',
            'email'      => 'update@example.com',
            'password'   => Hash::make('password123'),
            'status'     => 1,
        ]);

        $updateData = [
            'first_name' => 'Updated',  // Simplified - just send the fields to update
            'last_name'  => 'Name',
            'email'      => $user->email,
            'role'       => $this->role->id,
            'status'     => 1,
        ];

        $response = $this->actingAs($this->adminUser, 'web')
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->putJson("/users/update_user/$user->id", $updateData);

        $response->assertStatus(200);

        // Check if the update actually worked
        $user->refresh();
        expect($user->first_name)->toBe('Updated');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'first_name' => 'Updated',
        ]);
    });

    it('can delete user', function () {
        $user = User::create([
            'first_name' => 'Delete',
            'last_name'  => 'Me',
            'email'      => 'deleteme@example.com',
            'password'   => Hash::make('password123'),
            'status'     => 1,
        ]);

        $userId = $user->id;  // Store ID before deletion

        $response = $this->actingAs($this->adminUser, 'web')
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->deleteJson("/users/delete_user/{$user->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('users', ['id' => $userId]);
    });
});

describe('Logout', function () {
    it('can logout user', function () {
        $user = User::create([
            'first_name' => 'Logout',
            'last_name'  => 'User',
            'email'      => 'logout@example.com',
            'password'   => Hash::make('password123'),
            'status'     => 1,
        ]);

        $this->actingAs($user, 'web');

        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    });
});
