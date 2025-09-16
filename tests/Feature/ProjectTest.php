<?php

use App\Models\Tag;
use App\Models\User;
use App\Models\Status;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Priority;
use App\Models\Milestone;
use App\Models\Workspace;
use App\Models\CommentAttachment;
use Illuminate\Http\UploadedFile;
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

    // Create default status and priority
    $this->status = Status::create([
        'title' => 'Open',
        'color' => '#00ff00',
        'workspace_id' => $this->workspace->id,
        'slug' => 'open',
    ]);
    $this->priority = Priority::create([
        'title' => 'High',
        'color' => '#00ff00',
        'workspace_id' => $this->workspace->id,
        'slug' => 'high',
    ]);
});

// ------------------------
// Helpers
// ------------------------

function projectPayload($overrides = [])
{
    return array_merge([
        'title' => 'Test Project',
        'status_id' => test()->status->id,
        'priority_id' => test()->priority->id,
        'task_accessibility' => 'project_users',
        'budget' => null,
        'description' => null,
        'note' => null,
        'start_date' => null,
        'end_date' => null,
        'workspace_id' => test()->workspace->id,
        'created_by' => test()->adminUser->id,
    ], $overrides);
}

function makeProjectRequest($method, $url, $payload = [])
{
    return test()->$method($url, $payload);
}

// ------------------------
// VALIDATION TESTS
// ------------------------

describe('Project Validation', function () {

    it('validates required fields on create', function () {
        $response = makeProjectRequest('postJson', '/projects/store', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    });

    it('validates required fields on update', function () {
        $project = Project::create(projectPayload());

        $response = makeProjectRequest('postJson', '/projects/update', ['id' => $project->id]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    });
});

// ------------------------
// CREATE TESTS
// ------------------------

describe('Project Creation', function () {

    it('can create a project', function () {
        $response = makeProjectRequest('postJson', '/projects/store', projectPayload());

        $response->assertStatus(200)
            ->assertJson(['error' => false, 'message' => 'Project created successfully.']);

        $this->assertDatabaseHas('projects', ['title' => 'Test Project']);
    });

    it('can create a project with optional fields', function () {
        $payload = projectPayload([
            'budget' => '1000',
            'description' => 'This is a test project',
            'note' => 'Internal note',
        ]);

        $response = makeProjectRequest('postJson', '/projects/store', $payload);

        $response->assertStatus(200);
        $this->assertDatabaseHas('projects', ['title' => 'Test Project', 'budget' => 1000]);
    });
});

// ------------------------
// UPDATE TESTS
// ------------------------

describe('Project Updates', function () {

    it('can update a project', function () {
        $project = Project::create(projectPayload());
        $payload = projectPayload(['title' => 'Updated Test Project']);

        $response = makeProjectRequest(
            'postJson',
            '/projects/update',
            array_merge(['id' => $project->id], $payload)
        );

        $response->assertStatus(200)
            ->assertJson(['error' => false, 'message' => 'Project updated successfully.']);

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'title' => 'Updated Test Project',
        ]);
    });
});

// ------------------------
// DELETE TESTS
// ------------------------

describe('Project Deletion', function () {

    it('can delete a project', function () {
        $project = Project::create(projectPayload());

        $response = $this->deleteJson("/projects/destroy/{$project->id}");

        $response->assertStatus(200)
            ->assertJson(['error' => false, 'message' => 'Project deleted successfully.']);

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    });

    it('can delete multiple projects', function () {
        $project1 = Project::create(projectPayload());
        $project2 = Project::create(projectPayload(['title' => 'Another Project']));

        $response = makeProjectRequest('postJson', '/projects/destroy_multiple', ['ids' => [$project1->id, $project2->id]]);

        $response->assertStatus(200)
            ->assertJson(['error' => false, 'message' => 'Project(s) deleted successfully.']);

        $this->assertDatabaseMissing('projects', ['id' => $project1->id]);
        $this->assertDatabaseMissing('projects', ['id' => $project2->id]);
    });

    it('validates project IDs for bulk delete', function () {
        $response = makeProjectRequest('postJson', '/projects/destroy_multiple', ['ids' => [999]]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ids.0']);
    });
});

// ------------------------
// VIEW RENDERING TESTS
// ------------------------

describe('Project Views', function () {

    it('renders grid view with filters', function () {
        $project = Project::create(projectPayload());
        $tag = Tag::create(['title' => 'Test Tag', 'workspace_id' => $this->workspace->id, 'slug' => 'test-tag', 'color' => '#ff0000']);
        $project->tags()->attach($tag->id);

        $response = $this->get("/projects?statuses[]={$this->status->id}&tags[]={$tag->id}");

        $response->assertStatus(200)
            ->assertViewIs('projects.grid_view')
            ->assertViewHas('projects', function ($projects) use ($project) {
                return $projects->contains('id', $project->id);
            });
    });

    it('renders kanban view for favorite projects', function () {
        $project = Project::create(projectPayload());
        $this->adminUser->favorites()->create([
            'favoritable_type' => Project::class,
            'favoritable_id' => $project->id,
        ]);

        $response = $this->get('/projects/kanban/favorite');

        $response->assertStatus(200)
            ->assertViewIs('projects.kanban')
            ->assertViewHas('projects', function ($projects) use ($project) {
                return $projects->contains('id', $project->id);
            });
    });

    it('renders list view', function () {
        $project = Project::create(projectPayload());

        $response = $this->get('/projects/list');

        $response->assertStatus(200)
            ->assertViewIs('projects.projects')
            ->assertViewHas('projects', function ($projects) use ($project) {
                return $projects->contains('id', $project->id);
            });
    });

    it('renders gantt chart view', function () {
        $response = $this->get('/projects/gantt-chart');

        $response->assertStatus(200)
            ->assertViewIs('projects.gantt_chart');
    });

    it('renders calendar view', function () {
        $response = $this->get('/projects/calendar-view');

        $response->assertStatus(200)
            ->assertViewIs('projects.calendar_view');
    });

    it('renders mind map view', function () {
        $project = Project::create(projectPayload());

        $response = $this->get("/projects/mind-map/{$project->id}");

        $response->assertStatus(200)
            ->assertViewIs('projects.mind_map')
            ->assertViewHas('project', $project);
    });

    it('restricts non-admin access to only assigned projects', function () {
        $user = User::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'status' => 1,
            'email_verified_at' => now(),
        ]);
        $user->workspaces()->attach($this->workspace->id);
        $this->actingAs($user);

        $project = Project::create(projectPayload());
        $project->users()->attach($user->id);

        $response = $this->get('/projects');

        $response->assertStatus(200)
            ->assertViewHas('projects', function ($projects) use ($project) {
                return $projects->contains('id', $project->id);
            });
    });
});

// ------------------------
// BULK UPLOAD TESTS
// ------------------------

describe('Bulk Upload', function () {

    it('renders bulk upload form', function () {
        $response = $this->get('/projects/bulk-upload');

        $response->assertStatus(200)
            ->assertViewIs('bulk-upload')
            ->assertViewHas('entity', 'projects')
            ->assertViewHas('sample_file_url')
            ->assertViewHas('help_url');
    });
});

// ------------------------
// SHOW AND GET TESTS
// ------------------------

describe('Project Details', function () {

    it('shows project details', function () {
        $project = Project::create(projectPayload());

        $response = $this->get("/projects/information/{$project->id}");

        $response->assertStatus(200)
            ->assertViewIs('projects.project_information')
            ->assertViewHas('project', function ($viewProject) use ($project) {
                return $viewProject->id === $project->id;
            });
    });

    it('returns 404 for non-existent project in show', function () {
        $response = $this->get('/projects/information/999');

        $response->assertStatus(404);
    });

    it('retrieves project details via API', function () {
        $project = Project::create(projectPayload());

        $response = $this->getJson("/projects/get/{$project->id}");

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'project' => ['id' => $project->id, 'title' => $project->title],
            ]);
    });

    it('returns 404 for non-existent project in get', function () {
        $response = $this->getJson('/projects/get/999');

        $response->assertStatus(404);
    });
});

// ------------------------
// LIST TESTS
// ------------------------

describe('Project Listing', function () {

    it('lists projects with filters', function () {
        $project = Project::create(projectPayload());

        $response = $this->getJson("/projects/listing?status_ids[]={$this->status->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['rows', 'total'])
            ->assertJsonFragment(['id' => $project->id]);
    });
});

// ------------------------
// STATUS AND PRIORITY UPDATE TESTS
// ------------------------

describe('Status and Priority Updates', function () {

    it('updates project status', function () {
        $project = Project::create(projectPayload());
        $newStatus = Status::create(['title' => 'Closed', 'color' => '#ff0000', 'workspace_id' => $this->workspace->id, 'slug' => 'closed']);

        $response = makeProjectRequest('postJson', '/update-project-status', ['id' => $project->id, 'statusId' => $newStatus->id]);

        $response->assertStatus(200)
            ->assertJson(['error' => false, 'message' => 'Status updated successfully.']);

        $this->assertDatabaseHas('projects', ['id' => $project->id, 'status_id' => $newStatus->id]);
    });

    it('restricts status update for unauthorized users', function () {
        $project = Project::create(projectPayload());
        $newStatus = Status::create(['title' => 'Closed', 'color' => '#ff0000', 'workspace_id' => $this->workspace->id, 'slug' => 'closed']);

        $user = User::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'status' => 1,
            'email_verified_at' => now(),
        ]);

        $user->workspaces()->attach($this->workspace->id);
        $this->actingAs($user);

        $response = makeProjectRequest('postJson', '/update-project-status', ['id' => $project->id, 'statusId' => $newStatus->id]);

        $response->assertStatus(200)
            ->assertJson(['error' => true]);
    });

    it('updates project priority', function () {
        $project = Project::create(projectPayload());
        $newPriority = Priority::create(['title' => 'Low', 'color' => '#0000ff', 'workspace_id' => $this->workspace->id, 'slug' => 'low']);

        $response = makeProjectRequest('postJson', '/update-project-priority', ['id' => $project->id, 'priorityId' => $newPriority->id]);

        $response->assertStatus(200)
            ->assertJson(['error' => false, 'message' => 'Priority updated successfully.']);

        $this->assertDatabaseHas('projects', ['id' => $project->id, 'priority_id' => $newPriority->id]);
    });
});

// ------------------------
// FAVORITE AND PINNED TESTS
// ------------------------

describe('Favorites and Pinned', function () {

    it('updates project favorite status', function () {
        $project = Project::create(projectPayload());

        $response = $this->patchJson("/projects/update-favorite/{$project->id}", [
            'is_favorite' => true
        ]);

        $response->assertStatus(200)
            ->assertJson(['error' => false]);

        $this->assertDatabaseHas('favorites', [
            'favoritable_type' => Project::class,
            'favoritable_id' => $project->id,
            'user_id' => $this->adminUser->id,
        ]);
    });

    it('updates project pinned status', function () {
        $project = Project::create(projectPayload());

        $response = $this->patchJson("/projects/update-pinned/{$project->id}", [
            'is_pinned' => true
        ]);

        $response->assertStatus(200)
            ->assertJson(['error' => false]);

        $this->assertDatabaseHas('projects', ['id' => $project->id]);
    });
});

// ------------------------
// DUPLICATE TEST
// ------------------------

describe('Project Duplication', function () {

    it('duplicates a project (with or without new title)', function () {
        // 1. Create original project
        $project = Project::create(projectPayload(['title' => 'Original Project']));

        // 2. Simulate user passing a new title (or comment this out to test same-title case)
        $response = $this->getJson("/projects/duplicate/{$project->id}?title=Custom+Project");

        // 3. Response assertions
        $response->assertStatus(200)
            ->assertJson([
                'error'   => false,
                'message' => 'Project duplicated successfully.',
            ]);
            // 4. Get duplicate ID and duplicate title
            $duplicateId    = $response->json('id');
            $duplicateTitle = request()->input('title', $project->title); // fallback to same title


        // Ensure the duplicate has a different ID
        $this->assertNotEquals($project->id, $duplicateId);

        // 5. Ensure duplicate exists in DB with expected title
        $this->assertDatabaseHas('projects', [
            'id'    => $duplicateId,
            'title' => $duplicateTitle,
        ]);
    });
});

// ------------------------
// COMMENT TESTS
// ------------------------

describe('Project Comments', function () {

    it('adds a comment to a project', function () {
        $project = Project::create(projectPayload());

        $response = makeProjectRequest('postJson', "/projects/information/{$project->id}/comments", [
            'model_type' => Project::class,
            'model_id' => $project->id,
            'content' => 'Test comment',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true, 'message' => 'Comment Added Successfully']);

        $this->assertDatabaseHas('comments', ['commentable_id' => $project->id, 'content' => 'Test comment']);
    });

    it('retrieves a specific comment', function () {
        $project = Project::create(projectPayload());
        $comment = Comment::create([
            'commentable_type' => Project::class,
            'commentable_id' => $project->id,
            'content' => 'Test comment',
            'commenter_id' => $this->adminUser->id,
            'commenter_type' => User::class,
        ]);

        $response = $this->getJson("/projects/comments/get/{$comment->id}");

        $response->assertStatus(200)
            ->assertJson(['error' => false, 'comment' => ['id' => $comment->id]]);
    });

    it('updates a comment', function () {
        $project = Project::create(projectPayload());
        $comment = Comment::create([
            'commentable_type' => Project::class,
            'commentable_id' => $project->id,
            'content' => 'Original comment',
            'commenter_id' => $this->adminUser->id,
            'commenter_type' => User::class,
        ]);

        $response = makeProjectRequest('postJson', '/projects/comments/update', [
            'comment_id' => $comment->id,
            'content' => 'Updated comment',
        ]);

        $response->assertStatus(200)
            ->assertJson(['error' => false, 'message' => 'Comment updated successfully.']);

        $this->assertDatabaseHas('comments', ['id' => $comment->id, 'content' => 'Updated comment']);
    });

    it('deletes a comment', function () {
        $project = Project::create(projectPayload());
        $comment = Comment::create([
            'commentable_type' => Project::class,
            'commentable_id' => $project->id,
            'content' => 'Test comment',
            'commenter_id' => $this->adminUser->id,
            'commenter_type' => User::class,
        ]);

        $response = makeProjectRequest('deleteJson', '/projects/comments/destroy', ['comment_id' => $comment->id]);

        $response->assertStatus(200)
            ->assertJson(['error' => false, 'message' => 'Comment deleted successfully.']);

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    });

    it('deletes a comment attachment', function () {
        $project = Project::create(projectPayload());
        $comment = Comment::create([
            'commentable_type' => Project::class,
            'commentable_id' => $project->id,
            'content' => 'Test comment',
            'commenter_id' => $this->adminUser->id,
            'commenter_type' => User::class,
        ]);
        $file = UploadedFile::fake()->create('attachment.pdf');
        $path = $file->store('public/comment_attachments');
        $attachment = CommentAttachment::create([
            'comment_id' => $comment->id,
            'file_name' => 'attachment.pdf',
            'file_path' => $path,
            'file_type' => 'application/pdf',
        ]);

        $response = $this->deleteJson("/projects/comments/destroy-attachment/{$attachment->id}");

        $response->assertStatus(200)
            ->assertJson(['error' => false, 'message' => 'Attachment deleted successfully.']);

        $this->assertDatabaseMissing('comment_attachments', ['id' => $attachment->id]);
    });
});

// ------------------------
// MEDIA TESTS
// ------------------------

describe('Project Media', function () {

    it('uploads media to a project', function () {
        $project = Project::create(projectPayload());
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->post('/projects/upload-media', [
            'model_type' => Project::class,
            'id' => $project->id,
            'model_id' => $project->id,
            'media_files' => [$file], // make sure your controller expects "media"
        ]);

        $response->assertStatus(200)
            ->assertJson(['error' => false]);

        $this->assertDatabaseHas('media', [
            'model_type' => Project::class,
            'model_id' => $project->id,
        ]);
    });

    it('retrieves project media', function () {
        $project = Project::create(projectPayload());

        // Upload fake file through spatie media library so it ends up in getMedia()
        $media = $project->addMedia(
            UploadedFile::fake()->create('document.pdf', 100, 'application/pdf')
        )->toMediaCollection('project-media');

        $response = $this->getJson("/projects/get-media/{$project->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['error', 'message', 'rows', 'total'])
            ->assertJsonFragment(['id' => $media->id]);
    });

    it('deletes project media', function () {
        $project = Project::create(projectPayload());

        $media = $project->addMedia(
            UploadedFile::fake()->create('document.pdf', 100, 'application')
        )->toMediaCollection('project-media');

        $response = $this->deleteJson("/projects/delete-media/{$media->id}");

        $response->assertStatus(200)
            ->assertJson(['error' => false]);

        $this->assertDatabaseMissing('media', ['id' => $media->id]);
    });

    it('deletes multiple project media', function () {
        $project = Project::create(projectPayload());
        $media1 = $project->addMedia(UploadedFile::fake()->create('document1.pdf', 100, 'application'))->toMediaCollection('project-media');

        $media2 = $project->addMedia(UploadedFile::fake()->create('document2.pdf', 100, 'application'))->toMediaCollection('project-media');

        $response = makeProjectRequest('postJson', '/projects/delete-multiple-media', ['ids' => [$media1->id, $media2->id]]);

        $response->assertStatus(200)
            ->assertJson(['error' => false]);

        $this->assertDatabaseMissing('media', ['id' => $media1->id]);
        $this->assertDatabaseMissing('media', ['id' => $media2->id]);
    });
});

// ------------------------
// MILESTONE TESTS
// ------------------------

describe('Project Milestones', function () {

    it('creates a milestone', function () {
        $project = Project::create(projectPayload());

        $response = makeProjectRequest('postJson', '/projects/store-milestone', [
            'project_id' => $project->id,
            'title' => 'Test Milestone',
            'status' => 'active', // Add missing status
            'cost' => 1000, // Add missing cost
            'workspace_id' => $this->workspace->id // Add workspace_id
        ]);

        $response->assertStatus(200)
            ->assertJson(['error' => false, 'message' => 'Milestone created successfully.']);

        $this->assertDatabaseHas('milestones', ['project_id' => $project->id, 'title' => 'Test Milestone']);
    });

    it('retrieves project milestones', function () {
        $project = Project::create(projectPayload());
        $milestone = Milestone::create([
            'project_id' => $project->id,
            'title' => 'Test Milestone',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
            'workspace_id' => $this->workspace->id,
            'status' => $this->status->id,
            'created_by' => auth()->user()->id,
            'cost' => 1234
        ]);

        $response = $this->getJson("/projects/get-milestones/{$project->id}");
        $response->assertStatus(200)
            ->assertJson(['error' => false]);
    });

    it('retrieves a specific milestone', function () {
        $project = Project::create(projectPayload());
        $milestone = Milestone::create([
            'project_id' => $project->id,
            'title' => 'Test Milestone',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
            'workspace_id' => $this->workspace->id,
            'status' => $this->status->id,
            'created_by' => auth()->user()->id,
            'cost' => 1234
        ]);

        $response = $this->getJson("/projects/get-milestone/{$milestone->id}");

        $response->assertStatus(200)
            ->assertJson(['error' => false]);
    });

    it('updates a milestone', function () {
        $project = Project::create(projectPayload());
        $milestone = Milestone::create([
            'project_id' => $project->id,
            'title' => 'Test Milestone',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
            'workspace_id' => $this->workspace->id,
            'status' => $this->status->id,
            'created_by' => auth()->user()->id,
            'cost' => 1234
        ]);

        $payload = [
            'id' => $milestone->id,
            'title' => 'Updated Milestone',
            'status' => 'In Progress', // required
            'cost' => '1500',          // required
            'progress' => 50,          // required
        ];

        $response = makeProjectRequest('postJson', '/projects/update-milestone', $payload);

        $response->assertStatus(200)
            ->assertJson(['error' => false]);

        $this->assertDatabaseHas('milestones', ['id' => $milestone->id, 'title' => 'Updated Milestone']);
    });

    it('deletes a milestone', function () {
        $project = Project::create(projectPayload());
        $milestone = Milestone::create([
            'project_id' => $project->id,
            'title' => 'Test Milestone',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
            'workspace_id' => $this->workspace->id,
            'status' => $this->status->id,
            'cost' => 12354,
            'created_by' => auth()->user()->id
        ]);

        $response = $this->deleteJson("/projects/delete-milestone/{$milestone->id}");

        $response->assertStatus(200)
            ->assertJson(['error' => false]);

        $this->assertDatabaseMissing('milestones', ['id' => $milestone->id]);
    });

    it('deletes multiple milestones', function () {
        $project = Project::create(projectPayload());
        $milestone1 = Milestone::create([
            'project_id' => $project->id,
            'title' => 'Test Milestone 1',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
            'workspace_id' => $this->workspace->id,
            'status' => $this->status->id,
            'cost' => 12354,
            'created_by' => auth()->user()->id
        ]);
        $milestone2 = Milestone::create([
            'project_id' => $project->id,
            'title' => 'Test Milestone 2',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
            'workspace_id' => $this->workspace->id,
            'status' => $this->status->id,
            'cost' => 12354,
            'created_by' => auth()->user()->id
        ]);

        $response = makeProjectRequest('postJson', '/projects/delete-multiple-milestone', ['ids' => [$milestone1->id, $milestone2->id]]);

        $response->assertStatus(200)
            ->assertJson(['error' => false]);

        $this->assertDatabaseMissing('milestones', ['id' => $milestone1->id]);
        $this->assertDatabaseMissing('milestones', ['id' => $milestone2->id]);
    });
});

// ------------------------
// VIEW PREFERENCE TEST
// ------------------------

describe('View Preferences', function () {

    it('saves view preference', function () {
        $response = makeProjectRequest('putJson', '/save-projects-view-preference', ['view' => 'kanban']);

        $response->assertStatus(200)
            ->assertJson(['error' => false, 'message' => 'Default View Set Successfully.']);

        $this->assertDatabaseHas('user_client_preferences', [
            'user_id' => 'u_' . $this->adminUser->id,
            'table_name' => 'projects',
            'default_view' => 'kanban',
        ]);
    });
});

// ------------------------
// GANTT AND CALENDAR TESTS
// ------------------------

describe('Gantt and Calendar', function () {

    it('retrieves projects for Gantt chart', function () {
        $project = Project::create(projectPayload([
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
        ]));

        $response = $this->getJson('/projects/fetch-gantt-data');

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $project->id]);
    });

    it('retrieves calendar data', function () {
        $project = Project::create(projectPayload([
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
        ]));

        $response = $this->getJson('/projects/get-calendar-data?start=' . now()->subDays(10)->toDateString() . '&end=' . now()->addDays(10)->toDateString());

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $project->id]);
    });
});

// ------------------------
// DATE UPDATE TESTS
// ------------------------

describe('Date Updates', function () {

    it('updates project dates via gantt', function () {
        $project = Project::create(projectPayload());

        $response = makeProjectRequest('postJson', '/projects/gantt-chart-view/update-module-dates', [
            'module' => ['type' => 'project', 'id' => $project->id],
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
        ]);

        $response->assertStatus(200)
            ->assertJson(['error' => false, 'message' => 'Project dates updated successfully.']);
    });
});

// ------------------------
// STATUS AND PRIORITY RETRIEVAL TESTS
// ------------------------

describe('Status and Priority Retrieval', function () {

    it('retrieves statuses', function () {
        $response = $this->getJson('/projects/get-statuses');

        $response->assertStatus(200)
            ->assertJsonStructure(['error', 'statuses', 'message'])
            ->assertJsonFragment(['id' => $this->status->id, 'title' => $this->status->title]);
    });

    it('retrieves priorities', function () {
        $response = $this->getJson('/projects/get-priorities');

        $response->assertStatus(200)
            ->assertJsonStructure(['error', 'priorities', 'message'])
            ->assertJsonFragment(['id' => $this->priority->id, 'title' => $this->priority->title]);
    });
});

// ------------------------
// PERMISSION AND ACCESS TESTS
// ------------------------

describe('Permissions and Access', function () {

    it('restricts project access for unauthorized users', function () {
        $user = User::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'status' => 1,
            'email_verified_at' => now(),
        ]);
        $user->workspaces()->attach($this->workspace->id);
        $this->actingAs($user);

        $project = Project::create(projectPayload()); // Project not assigned to user

        $response = $this->get("/projects/information/{$project->id}");

        $response->assertStatus(302); // checkAccess middleware returns 302
    });
});
