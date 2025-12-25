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
            'user_id' => 1,
            'is_primary' => 1,
        ]);
    }

    // Bind workspace helper if your app uses it
    $this->app->bind('getWorkspaceId', fn() => $this->workspace->id);

    // Get an existing role
    $this->role = Role::firstOrCreate(['name' => 'admin'], ['guard_name' => 'web']);
    $this->userRole = Role::firstOrCreate(['name' => 'user'], ['guard_name' => 'web']);

    // Create an admin user for authenticated requests
    $this->adminUser = User::create([
        'id' => 1,
        'first_name' => 'Admin',
        'last_name' => 'User',
        'email' => 'admin@example.com',
        'password' => Hash::make('password123'),
        'status' => 1,
        'email_verified_at' => now(),
    ]);

    $this->adminUser->assignRole('admin');
    $this->adminUser->workspaces()->attach($this->workspace->id);

    // Create a regular user
    $this->regularUser = User::create([
        'id' => 2,
        'first_name' => 'Regular',
        'last_name' => 'User',
        'email' => 'user@example.com',
        'password' => Hash::make('password123'),
        'status' => 1,
        'email_verified_at' => now(),
    ]);

    $this->regularUser->assignRole('user');
    $this->regularUser->workspaces()->attach($this->workspace->id);

    // Create a test client
    $this->testClient = Client::create([
        'first_name' => 'Test',
        'last_name' => 'Client',
        'email' => 'client@example.com',
        'password' => Hash::make('password123'),
        'status' => 1,
    ]);

    $this->workspace->clients()->attach($this->testClient->id);

    // Authenticate as admin user by default
    $this->actingAs($this->adminUser);
    $this->withSession(['workspace_id' => $this->workspace->id]);
});

describe('Workspace Management - Index & Listing', function () {

    it('displays workspaces index page', function () {
        $response = $this->get('/workspaces');

        $response->assertStatus(200);
        $response->assertViewIs('workspaces.workspaces');
        $response->assertViewHas('workspaces');
    });

    it('lists workspaces with pagination', function () {
        // Create additional workspaces
        $workspace1 = Workspace::create([
            'title' => 'Workspace 1',
            'user_id' => $this->adminUser->id,
            'is_primary' => 0,
        ]);
        $workspace2 = Workspace::create([
            'title' => 'Workspace 2',
            'user_id' => $this->adminUser->id,
            'is_primary' => 0,
        ]);

        $response = $this->getJson('/workspaces/list?limit=10');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'rows' => [
                '*' => [
                    'id',
                    'title',
                    'is_default',
                    'users',
                    'clients',
                    'created_at',
                    'updated_at',
                    'actions'
                ]
            ],
            'total'
        ]);
    });

    it('filters workspaces by search term', function () {
        Workspace::create([
            'title' => 'Development Workspace',
            'user_id' => $this->adminUser->id,
            'is_primary' => 0,
        ]);

        $response = $this->getJson('/workspaces/list?search=Development');


        $response->assertStatus(200);
        $response->assertSee('Development Workspace');
    });

    it('filters workspaces by user_ids', function () {
        $workspace = Workspace::create([
            'title' => 'User Specific Workspace',
            'user_id' => $this->adminUser->id,
            'is_primary' => 0,
        ]);
        $workspace->users()->attach($this->regularUser->id);

        $response = $this->getJson('/workspaces/list?user_ids[]=' . $this->regularUser->id);

        $response->assertStatus(200);
        $response->assertSee( 'User Specific Workspace');
    });
});

describe('Workspace Creation', function () {

    it('can create workspace with valid data', function () {
        $workspaceData = [
            'title' => 'New Test Workspace',
            'user_ids' => [$this->regularUser->id],
            'client_ids' => [$this->testClient->id],
        ];

        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->postJson('/workspaces/store', $workspaceData);

        $response->assertStatus(200);
        $response->assertJson(['error' => false]);
        $response->assertJsonStructure([
            'error',
            'message',
            'id',
            'data' => [
                'id',
                'title',
                'primaryWorkspace',
                'users',
                'clients',
                'created_at',
                'updated_at'
            ]
        ]);

        $this->assertDatabaseHas('workspaces', [
            'title' => 'New Test Workspace'
        ]);
    });

    it('validates required title field', function () {
        $workspaceData = [
            'user_ids' => [$this->regularUser->id],
        ];

        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->postJson('/workspaces/store', $workspaceData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title']);
    });

    it('validates user_ids exist in database', function () {
        $workspaceData = [
            'title' => 'Test Workspace',
            'user_ids' => [999999], // Non-existent user ID
        ];

        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->postJson('/workspaces/store', $workspaceData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['user_ids.0']);
    });

    it('validates client_ids exist in database', function () {
        $workspaceData = [
            'title' => 'Test Workspace',
            'client_ids' => [999999], // Non-existent client ID
        ];

        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->postJson('/workspaces/store', $workspaceData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['client_ids.0']);
    });

    it('automatically adds creator as participant for non-admin users', function () {
        $workspaceData = [
            'title' => 'Auto Participant Workspace',
            'user_ids' => [],
        ];

        $response = $this->actingAs($this->regularUser)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->postJson('/workspaces/store', $workspaceData);

        $response->assertStatus(200);

        $workspace = Workspace::where('title', 'Auto Participant Workspace')->first();
        expect($workspace->users->pluck('id')->toArray())->toContain($this->regularUser->id);
    });

    it('creates primary workspace for admin users', function () {
        $workspaceData = [
            'title' => 'Primary Test Workspace',
            'user_ids' => [$this->adminUser->id],
            'primaryWorkspace' => 'on',
        ];

        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->postJson('/workspaces/store', $workspaceData);

        $response->assertStatus(200);

        $workspace = Workspace::where('title', 'Primary Test Workspace')->first();
        expect($workspace->is_primary)->toBe(1);

        // Check that other workspaces are set to non-primary
        $this->assertDatabaseHas('workspaces', [
            'id' => $this->workspace->id,
            'is_primary' => 0,
        ]);
    });

    it('does not create primary workspace for non-admin users', function () {
        $workspaceData = [
            'title' => 'Non-Primary Workspace',
            'user_ids' => [$this->regularUser->id],
            'primaryWorkspace' => 'on',
        ];

        $response = $this->actingAs($this->regularUser)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->postJson('/workspaces/store', $workspaceData);

        $response->assertStatus(200);

        $workspace = Workspace::where('title', 'Non-Primary Workspace')->first();
        expect($workspace->is_primary)->toBe(0);
    });
});

describe('Workspace Updates', function () {

    it('can update workspace with valid data', function () {
        $updateData = [
            'id' => $this->workspace->id,
            'title' => 'Updated Workspace Title',
            'user_ids' => [$this->adminUser->id, $this->regularUser->id],
            'client_ids' => [$this->testClient->id],
        ];

        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->postJson('/workspaces/update', $updateData);

        $response->assertStatus(200);
        $response->assertJson(['error' => false]);

        $this->assertDatabaseHas('workspaces', [
            'id' => $this->workspace->id,
            'title' => 'Updated Workspace Title',
        ]);
    });

    it('validates workspace id exists on update', function () {
        $updateData = [
            'id' => 999999,
            'title' => 'Updated Title',
        ];

        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->postJson('/workspaces/update', $updateData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['id']);
    });

    it('can set workspace as primary by admin', function () {
        $updateData = [
            'id' => $this->workspace->id,
            'title' => $this->workspace->title,
            'primaryWorkspace' => true,
        ];

        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->postJson('/workspaces/update', $updateData);

        $response->assertStatus(200);

        $this->workspace->refresh();
        expect($this->workspace->is_primary)->toBe(1);
    });

    it('cannot set workspace as primary by non-admin', function () {
        $updateData = [
            'id' => $this->workspace->id,
            'title' => $this->workspace->title,
            'primaryWorkspace' => true,
        ];

        $response = $this->actingAs($this->regularUser)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->postJson('/workspaces/update', $updateData);

        $response->assertStatus(200);

        $this->workspace->refresh();
        expect($this->workspace->is_primary)->toBe(1); // Should remain unchanged
    });
});

describe('Workspace Retrieval', function () {

    it('can get workspace details', function () {
        $response = $this->getJson("/workspaces/get/{$this->workspace->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'error',
            'workspace' => [
                'id',
                'title',
                'users',
                'clients',
            ]
        ]);
    });

    it('returns 404 for non-existent workspace', function () {
        $response = $this->getJson('/workspaces/get/999999');

        $response->assertStatus(404);
    });
});

describe('Workspace API List', function () {

    it('retrieves workspaces via api', function () {
        $response = $this->getJson('/api/workspaces');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'error',
            'message',
            'total',
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'primaryWorkspace',
                    'users',
                    'clients',
                    'created_at',
                    'updated_at'
                ]
            ]
        ]);
    });

    it('retrieves single workspace via api', function () {
        $response = $this->getJson("/api/workspaces/{$this->workspace->id}");

        $response->assertStatus(200);
        $response->assertJsonFragment(['title' => $this->workspace->title]);
    });

    it('returns error for non-existent workspace via api', function () {
        $response = $this->getJson('/api/workspaces/999999');

        $response->assertStatus(200);
        $response->assertJson([
            'error' => false,
            'message' => 'Workspace not found',
        ]);
    });

    it('filters workspaces by user_id via api', function () {
        $workspace = Workspace::create([
            'title' => 'User Filtered Workspace',
            'user_id' => $this->adminUser->id,
            'is_primary' => 0,
        ]);
        $workspace->users()->attach($this->regularUser->id);

        $response = $this->getJson("/api/workspaces?user_id={$this->regularUser->id}");

        $response->assertStatus(200);
        $response->assertJsonFragment(['title' => 'User Filtered Workspace']);
    });

    it('filters workspaces by client_id via api', function () {
        $workspace = Workspace::create([
            'title' => 'Client Filtered Workspace',
            'user_id' => $this->adminUser->id,
            'is_primary' => 0,
        ]);
        $workspace->clients()->attach($this->testClient->id);

        $response = $this->getJson("/api/workspaces?client_id={$this->testClient->id}");

        $response->assertStatus(200);
        $response->assertJsonFragment(['title' => 'Client Filtered Workspace']);
    });
});

describe('Workspace Default Setting', function () {

    it('can set workspace as default', function () {
        $requestData = ['is_default' => 1];

        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->patchJson("/workspaces/{$this->workspace->id}/default", $requestData);

        $response->assertStatus(200);
        $response->assertJson(['error' => false]);

        $this->adminUser->refresh();
        expect($this->adminUser->default_workspace_id)->toBe($this->workspace->id);
    });

    it('can remove workspace as default', function () {
        // First set as default
        $this->adminUser->default_workspace_id = $this->workspace->id;
        $this->adminUser->save();

        $requestData = ['is_default' => 0];

        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->patchJson("/workspaces/{$this->workspace->id}/default", $requestData);

        $response->assertStatus(200);
        $response->assertJson(['error' => false]);

        $this->adminUser->refresh();
        expect($this->adminUser->default_workspace_id)->toBeNull();
    });

    it('validates is_default field', function () {
        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->patchJson("/workspaces/{$this->workspace->id}/default", []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['is_default']);
    });

    it('returns error for non-existent workspace in default setting', function () {
        $requestData = ['is_default' => 1];

        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->patchJson('/workspaces/999999/default', $requestData);

        $response->assertStatus(200);
        $response->assertJson([
            'error' => true,
            'message' => 'Workspace not found',
        ]);
    });
});

describe('Workspace Switching', function () {

    it('can switch to existing workspace', function () {
        $newWorkspace = Workspace::create([
            'title' => 'Switch Target Workspace',
            'user_id' => $this->adminUser->id,
            'is_primary' => 0,
        ]);
        $newWorkspace->users()->attach($this->adminUser->id);

        $response = $this->get("/workspaces/switch/{$newWorkspace->id}");

        $response->assertStatus(302); // Redirect back
        $response->assertSessionHas('message', 'Workspace changed successfully.');
        $response->assertSessionHas('workspace_id', $newWorkspace->id);
    });

    it('returns error for non-existent workspace switch', function () {
        $response = $this->getJson('/workspaces/switch/999999');

        $response->assertStatus(200);
        $response->assertJson([
            'error' => true,
            'message' => 'Workspace not found.',
        ]);
    });
});

describe('Workspace Deletion', function () {

    it('can delete non-primary workspace', function () {
        $workspace = Workspace::create([
            'title' => 'Deletable Workspace',
            'user_id' => $this->adminUser->id,
            'is_primary' => 0,
        ]);

        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->deleteJson("/workspaces/destroy/{$workspace->id}");

        $response->assertStatus(200);
        $response->assertJson(['error' => false]);

        $this->assertDatabaseMissing('workspaces', ['id' => $workspace->id]);
    });

    it('cannot delete primary workspace', function () {
        $primaryWorkspace = Workspace::create([
            'title' => 'Primary Workspace',
            'user_id' => $this->adminUser->id,
            'is_primary' => 1,
        ]);

        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->deleteJson("/workspaces/destroy/{$primaryWorkspace->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'error' => true,
            'message' => 'Primary workspace cannot be deleted.',
        ]);

        $this->assertDatabaseHas('workspaces', ['id' => $primaryWorkspace->id]);
    });

    it('cannot delete current workspace', function () {
        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->deleteJson("/workspaces/destroy/{$this->workspace->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'error' => true,
            'message' => 'Current workspace cannot be deleted. Please switch to a different workspace first.',
        ]);

        $this->assertDatabaseHas('workspaces', ['id' => $this->workspace->id]);
    });

    it('returns error for non-existent workspace deletion', function () {
        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->deleteJson('/workspaces/destroy/999999');

        $response->assertStatus(200);
        $response->assertJson([
            'error' => true,
            'message' => 'Workspace not found.',
        ]);
    });

    it('clears default workspace when deleted', function () {
        $workspace = Workspace::create([
            'title' => 'Default Workspace to Delete',
            'user_id' => $this->adminUser->id,
            'is_primary' => 0,
        ]);

        // Set as default
        $this->adminUser->default_workspace_id = $workspace->id;
        $this->adminUser->save();

        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->deleteJson("/workspaces/destroy/{$workspace->id}");

        $response->assertStatus(200);

        $this->adminUser->refresh();
        expect($this->adminUser->default_workspace_id)->toBeNull();
    });
});

describe('Workspace Multiple Deletion', function () {

    it('can delete multiple non-primary workspaces', function () {
        $workspace1 = Workspace::create([
            'title' => 'Delete Me 1',
            'user_id' => $this->adminUser->id,
            'is_primary' => 0,
        ]);
        $workspace2 = Workspace::create([
            'title' => 'Delete Me 2',
            'user_id' => $this->adminUser->id,
            'is_primary' => 0,
        ]);

        $requestData = ['ids' => [$workspace1->id, $workspace2->id]];

        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->postJson('/workspaces/destroy_multiple', $requestData);

        $response->assertStatus(200);
        $response->assertJson(['error' => false]);

        $this->assertDatabaseMissing('workspaces', ['id' => $workspace1->id]);
        $this->assertDatabaseMissing('workspaces', ['id' => $workspace2->id]);
    });

    it('validates ids array in multiple deletion', function () {
        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->postJson('/workspaces/destroy_multiple', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['ids']);
    });

    it('handles mixed deletion scenarios correctly', function () {
        $deletableWorkspace = Workspace::create([
            'title' => 'Deletable',
            'user_id' => $this->adminUser->id,
            'is_primary' => 0,
        ]);
        $primaryWorkspace = Workspace::create([
            'title' => 'Primary',
            'user_id' => $this->adminUser->id,
            'is_primary' => 1,
        ]);

        $requestData = ['ids' => [$deletableWorkspace->id, $primaryWorkspace->id]];

        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->postJson('/workspaces/destroy_multiple', $requestData);

        $response->assertStatus(200);
        $response->assertJsonFragment(['message' => 'Workspace(s) deleted successfully except primary one.']);

        $this->assertDatabaseMissing('workspaces', ['id' => $deletableWorkspace->id]);
        $this->assertDatabaseHas('workspaces', ['id' => $primaryWorkspace->id]);
    });
});

describe('Workspace Participant Management', function () {

    it('can remove participant from workspace', function () {
        // Add user to workspace first
        $this->workspace->users()->syncWithoutDetaching($this->regularUser->id);

        $response = $this->actingAs($this->regularUser)
            ->getJson('/workspaces/remove_participant');

        $response->assertStatus(200);
        $response->assertJson(['error' => false]);

        expect($this->workspace->users()->where('user_id', $this->regularUser->id)->exists())->toBeFalse();
    });

    it('handles client participant removal', function () {
        // Add client to workspace
        $testClient = Client::create([
            'first_name' => 'Remove',
            'last_name' => 'Client',
            'email' => 'remove@client.com',
            'password' => Hash::make('password123'),
            'status' => 1,
        ]);

        $this->workspace->clients()->attach($testClient->id);

        // Ensure the workspace ID is set for getWorkspaceId()
        session(['workspace_id' => $this->workspace->id]);

        $response = $this->actingAs($testClient, 'client')
            ->getJson('/workspaces/remove_participant');

        $response->assertStatus(200);
        $response->assertJson(['error' => false]);

        // Fix: Don't specify the column name, let Laravel handle it
        expect(
            $this->workspace->fresh()->clients()->where('clients.id', $testClient->id)->exists()
        )->toBeFalse();

        // Or alternatively, check the pivot table directly:
        // expect(
        //     DB::table('client_workspace')
        //         ->where('workspace_id', $this->workspace->id)
        //         ->where('client_id', $testClient->id)
        //         ->exists()
        // )->toBeFalse();
    });
});

describe('Workspace Duplication', function () {

    it('can duplicate workspace with default options', function () {
        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->getJson("/workspaces/duplicate/{$this->workspace->id}?title=Default Workspace");

        $response->assertStatus(200);
        $response->assertJson(['error' => false]);

        $this->assertDatabaseHas('workspaces', [
            'title' => $this->workspace->title . ' (Copy)',
            'is_primary' => 0,
        ]);
    });

    it('can duplicate workspace with custom title', function () {
        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->getJson("/workspaces/duplicate/{$this->workspace->id}?title=Custom Duplicated Title");

        $response->assertStatus(200);

        $this->assertDatabaseHas('workspaces', [
            'title' => 'Custom Duplicated Title',
            'is_primary' => 0,
        ]);
    });

    it('can duplicate workspace with specific options', function () {
        $options = ['projects', 'meetings', 'users', 'clients'];

        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->getJson("/workspaces/duplicate/{$this->workspace->id}?options=" . implode(',', $options));

        $response->assertStatus(200);
        $response->assertJson(['error' => false]);
    });

    it('validates tasks option requires projects option', function () {
        $options = ['tasks']; // Tasks without projects

        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->getJson("/workspaces/duplicate/{$this->workspace->id}?options=" . implode(',', $options));

        $response->assertStatus(200);
        $response->assertJson([
            'error' => true,
            'message' => 'Tasks can only be duplicated if Projects is selected.',
        ]);
    });

    it('returns error for non-existent workspace duplication', function () {
        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->getJson('/workspaces/duplicate/999999');

        $response->assertStatus(200)
        ->assertJson(['error' => true]);
    });
});
