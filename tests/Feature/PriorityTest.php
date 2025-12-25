<?php

use App\Models\User;
use App\Models\Priority;
use App\Models\Workspace;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

beforeEach(function () {
    Storage::fake('public');

    // Create or use existing workspace
    $this->workspace = Workspace::first() ?? Workspace::create(['title' => 'Default Workspace']);

    // Create admin user
    $this->adminUser = User::create([
        'first_name' => 'Admin',
        'last_name' => 'User',
        'email' => 'admin@example.com',
        'password' => Hash::make('password'),
        'status' => 1,
        'email_verified_at' => now(),
    ]);

    $this->adminUser->assignRole('admin');
    $this->adminUser->workspaces()->attach($this->workspace->id);

    // Set workspace in session
    Session::put('workspace_id', $this->workspace->id);
    $this->actingAs($this->adminUser);
});

function priorityPayload($overrides = [])
{
    return array_merge([
        'title' => 'Test Priority',
        'color' => '#000000',
        'slug' => 'Test Slug'
    ], $overrides);
}

function makePriorityRequest($method, $url, $payload = [])
{
    return test()->$method($url, $payload);
}

// ------------------------
// Validation Tests
// ------------------------
describe('Validation Tests', function () {
    it('can validate required field on create', function () {
        $response = makePriorityRequest('postJson', '/priority/store', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('title');
    });

    it('can validate required field on update', function () {
        $response = makePriorityRequest('postJson', '/priority/update', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('title');
    });

    it('validates required title on create', function () {
        $response = makePriorityRequest('postJson', '/priority/store', [
            'color' => '#ff0000'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('title');
    });

    it('validates required color on create', function () {
        $response = makePriorityRequest('postJson', '/priority/store', [
            'title' => 'Missing Color'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('color');
    });

    it('validates required fields on update', function () {
        $priority = Priority::create(priorityPayload());

        $response = makePriorityRequest('postJson', '/priority/update', [
            'id' => $priority->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'color']);
    });
});

// ------------------------
// Create Tests
// ------------------------
describe('Create Tests', function () {
    it('can create priority', function () {
        $response = makePriorityRequest('postJson', '/priority/store', priorityPayload());

        $response->assertStatus(200)
            ->assertJson(['error' => false]);

        $this->assertDatabaseHas('priorities', ['title' => 'Test Priority']);
    });
});

// ------------------------
// Update Tests
// ------------------------
describe('Update Tests', function () {
    it('can update priority', function () {
        $priority = Priority::create(priorityPayload());

        $response = makePriorityRequest('postJson', '/priority/update', array_merge(['id' => $priority->id], priorityPayload(['title' => 'Updated Priority'])));

        $response->assertStatus(200)
            ->assertJson(['error' => false]);

        $this->assertDatabaseHas('priorities', ['title' => 'Updated Priority']);
    });

    it('throws error when updating non-existent priority', function () {
        $response = makePriorityRequest('postJson', '/priority/update', [
            'id' => 999999,
            'title' => 'Does Not Exist',
            'color' => '#123456'
        ]);

        $response->assertStatus(422)
            ->assertJson(['error' => true]);
    });
});

// ------------------------
// Get Tests
// ------------------------
describe('Get Tests', function () {
    it('can retrieve a priority by id', function () {
        $priority = Priority::create(priorityPayload());

        $response = makePriorityRequest('getJson', "/priority/get/{$priority->id}");

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'priority' => [
                    'id' => $priority->id,
                    'title' => $priority->title,
                ]
            ]);
    });

    it('returns 404 when retrieving non-existent priority', function () {
        $response = makePriorityRequest('getJson', '/priority/get/999999');

        $response->assertStatus(404)
            ->assertJson([
                'error' => true,
                'message' => 'Priority not found.'
            ]);
    });
});

// ------------------------
// Delete Tests
// ------------------------
describe('Delete Tests', function () {
    it('can delete priority', function () {
        $priority = Priority::create(priorityPayload());

        $response = makePriorityRequest('deleteJson', "/priority/destroy/{$priority->id}");

        $response->assertStatus(200)
            ->assertJson(['error' => false]);

        $this->assertDatabaseMissing('priorities', ['id' => $priority->id]);
    });

    it('can delete multiple priorities', function () {
        $priority1 = Priority::create(priorityPayload());
        $priority2 = Priority::create(priorityPayload(['title' => 'Test Priority2']));

        $response = makePriorityRequest('postJson', "/priority/destroy_multiple", ['ids' => [$priority1->id, $priority2->id]]);

        $response->assertStatus(200)
            ->assertJson(['error' => false]);

        $this->assertDatabaseMissing('priorities', ['id' => $priority1->id]);
        $this->assertDatabaseMissing('priorities', ['id' => $priority2->id]);
    });

    it('returns 404 when deleting non-existent priority', function () {
        $response = makePriorityRequest('deleteJson', '/priority/destroy/999999');

        $response->assertStatus(404)
            ->assertJson([
                'error' => true,
                'message' => 'Priority not found.'
            ]);
    });

    it('prevents deleting default priority id=0', function () {
        $response = makePriorityRequest('postJson', '/priority/destroy_multiple', [
            'ids' => [0]
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'error' => true,
                'message' => 'Default priority cannot be deleted.'
            ]);
    });
});

// ------------------------
// List / API List Tests
// ------------------------
describe('List / API List Tests', function () {
    it('can list priorities with pagination', function () {
        Priority::create(priorityPayload(['title' => 'Urgent']));
        Priority::create(priorityPayload(['title' => 'Normal']));
        Priority::create(priorityPayload(['title' => 'Midium']));

        $response = makePriorityRequest('getJson', '/priority/list?limit=2');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'rows',
                'total'
            ]);
    });

    it('can search priorities by title', function () {
        Priority::create(priorityPayload(['title' => 'Urgent']));
        Priority::create(priorityPayload(['title' => 'Normal']));

        $response = makePriorityRequest('getJson', '/priority/list?search=Urgent');

        $response->assertStatus(200);
        expect(collect($response->json('rows'))->pluck('title'))->toContain('Urgent');
    });
});

