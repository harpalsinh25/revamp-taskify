<?php

use App\Models\User;
use App\Models\Status;
use App\Models\Workspace;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

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

function statusPayload($overrides = [])
{
    return array_merge([
        'title' => 'Test Status',
        'color' => '#000000',
        'slug' => 'Test Slug'
    ], $overrides);
}

function makeStatusRequest($method, $url, $payload = [])
{
    return test()->$method($url, $payload);
}

// ------------------------
// Validation Tests
// ------------------------
describe('Validation Tests', function () {
    it('can validate required field on create', function () {
        $response = makeStatusRequest('postJson', '/status/store', []);
        $response->assertStatus(422)->assertJsonValidationErrors('title');
    });

    it('can validate required field on update', function () {
        $response = makeStatusRequest('postJson', '/status/update', []);
        $response->assertStatus(422)->assertJsonValidationErrors('title');
    });

    it('validates required title on create', function () {
        $response = makeStatusRequest('postJson', '/status/store', [
            'color' => '#ff0000'
        ]);
        $response->assertStatus(422)->assertJsonValidationErrors('title');
    });

    it('validates required color on create', function () {
        $response = makeStatusRequest('postJson', '/status/store', [
            'title' => 'Missing Color'
        ]);
        $response->assertStatus(422)->assertJsonValidationErrors('color');
    });

    it('validates required fields on update', function () {
        $status = Status::create(statusPayload());
        $response = makeStatusRequest('postJson', '/status/update', [
            'id' => $status->id,
        ]);
        $response->assertStatus(422)->assertJsonValidationErrors(['title', 'color']);
    });
});

// ------------------------
// Create Tests
// ------------------------
describe('Create Tests', function () {
    it('can create status', function () {
        $response = makeStatusRequest('postJson', '/status/store', statusPayload());
        $response->assertStatus(200)->assertJson(['error' => false]);
        $this->assertDatabaseHas('statuses', ['title' => 'Test Status']);
    });
});

// ------------------------
// Update Tests
// ------------------------
describe('Update Tests', function () {
    it('can update status', function () {
        $status = Status::create(statusPayload());
        $response = makeStatusRequest('postJson', '/status/update', array_merge(['id' => $status->id], statusPayload(['title' => 'Updated Status'])));
        $response->assertStatus(200)->assertJson(['error' => false]);
        $this->assertDatabaseHas('statuses', ['title' => 'Updated Status']);
    });

    it('throws error when updating non-existent status', function () {
        $response = makeStatusRequest('postJson', '/status/update', [
            'id' => 999999,
            'title' => 'Does Not Exist',
            'color' => '#123456'
        ]);
        $response->assertStatus(422)->assertJson(['error' => true]);
    });
});

// ------------------------
// Get Tests
// ------------------------
describe('Get Tests', function () {
    it('can retrieve a status by id', function () {
        $status = Status::create(statusPayload());
        $response = makeStatusRequest('getJson', "/status/get/{$status->id}");
        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'status' => [
                    'id' => $status->id,
                    'title' => $status->title,
                ]
            ]);
    });

    it('returns 404 when retrieving non-existent status', function () {
        $response = makeStatusRequest('getJson', '/status/get/999999');
        $response->assertStatus(404)->assertJson([
            'error' => true,
            'message' => 'Status not found.'
        ]);
    });
});

// ------------------------
// Delete Tests
// ------------------------
describe('Delete Tests', function () {
    it('can delete status', function () {
        $status = Status::create(statusPayload());
        $response = makeStatusRequest('deleteJson', "/status/destroy/{$status->id}");
        $response->assertStatus(200)->assertJson(['error' => false]);
        $this->assertDatabaseMissing('statuses', ['id' => $status->id]);
    });

    it('can delete multiple statuses', function () {
        $status1 = Status::create(statusPayload());
        $status2 = Status::create(statusPayload(['title' => 'Test Status2']));
        $response = makeStatusRequest('postJson', "/status/destroy_multiple", ['ids' => [$status1->id, $status2->id]]);
        $response->assertStatus(200)->assertJson(['error' => false]);
        $this->assertDatabaseMissing('statuses', ['id' => $status1->id]);
        $this->assertDatabaseMissing('statuses', ['id' => $status2->id]);
    });

    it('returns 404 when deleting non-existent status', function () {
        $response = makeStatusRequest('deleteJson', '/status/destroy/999999');
        $response->assertStatus(404)->assertJson([
            'error' => true,
            'message' => 'Status not found.'
        ]);
    });
});

// ------------------------
// List / API List Tests
// ------------------------
describe('List / API List Tests', function () {
    it('can list statuses with pagination', function () {
        Status::create(statusPayload(['title' => 'Open']));
        Status::create(statusPayload(['title' => 'In Progress']));
        Status::create(statusPayload(['title' => 'Closed']));
        $response = makeStatusRequest('getJson', '/status/list?limit=2');
        $response->assertStatus(200)->assertJsonStructure([
            'rows',
            'total'
        ]);
    });

    it('can search statuses by title', function () {
        Status::create(statusPayload(['title' => 'Open']));
        Status::create(statusPayload(['title' => 'Closed']));
        $response = makeStatusRequest('getJson', '/status/list?search=Open');
        $response->assertStatus(200);
        expect(collect($response->json('rows'))->pluck('title'))->toContain('Open');
    });
});

