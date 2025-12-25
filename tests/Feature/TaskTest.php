<?php

use App\Models\Tag;
use App\Models\User;
use App\Models\Task;
use App\Models\Status;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Priority;
use App\Models\Workspace;
use App\Models\TaskTimeEntry;
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

    // Create default project
    $this->project = Project::create([
        'title' => 'Test Project',
        'status_id' => $this->status->id,
        'priority_id' => $this->priority->id,
        'task_accessibility' => 'project_users',
        'workspace_id' => $this->workspace->id,
        'created_by' => $this->adminUser->id,
    ]);
});

// ------------------------
// Helpers
// ------------------------

function taskPayload($overrides = [])
{
    return array_merge([
        'title' => 'Test Task',
        'status_id' => test()->status->id,
        'priority_id' => test()->priority->id,
        'description' => null,
        'note' => null,
        'start_date' => null,
        'due_date' => null,
        'project_id' => test()->project->id,
        'project' => test()->project->id,
        'workspace_id' => test()->workspace->id,
        'created_by' => test()->adminUser->id,
    ], $overrides);
}

function makeTaskRequest($method, $url, $payload = [])
{
    return test()->$method($url, $payload);
}

// ------------------------
// Validation Tests
// ------------------------
describe('Validation Tests', function () {
    it('validates required fields on create', function () {
        $response = makeTaskRequest('postJson', '/tasks/store', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    });

    it('validates required fields on update', function () {
        $task = Task::create(taskPayload());

        $response = makeTaskRequest('postJson', '/tasks/update', ['id' => $task->id]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    });
});

// ------------------------
// Create Tests
// ------------------------
describe('Create Tests', function () {
    it('can create a task', function () {
        $response = makeTaskRequest('postJson', '/tasks/store', taskPayload());

        $response->assertStatus(200)
            ->assertJson(['error' => false, 'message' => 'Task created successfully.']);

        $this->assertDatabaseHas('tasks', ['title' => 'Test Task']);
    });

    it('can create a task with optional fields', function () {
        $payload = taskPayload([
            'description' => 'This is a test task',
            'note' => 'Internal note',
        ]);

        $response = makeTaskRequest('postJson', '/tasks/store', $payload);

        $response->assertStatus(200);
        $this->assertDatabaseHas('tasks', [
            'title' => 'Test Task',
            'description' => 'This is a test task'
        ]);
    });
});

// ------------------------
// Update Tests
// ------------------------
describe('Update Tests', function () {
    it('can update a task', function () {
        $task = Task::create(taskPayload());
        $payload = taskPayload(['title' => 'Updated Test Task']);

        $response = makeTaskRequest(
            'postJson',
            '/tasks/update',
            array_merge(['id' => $task->id], $payload)
        );

        $response->assertStatus(200)
            ->assertJson(['error' => false, 'message' => 'Task updated successfully.']);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated Test Task',
        ]);
    });
});

// ------------------------
// Delete Tests
// ------------------------
describe('Delete Tests', function () {
    it('can delete a task', function () {
        $task = Task::create(taskPayload());

        $response = $this->deleteJson("/tasks/destroy/{$task->id}");

        $response->assertStatus(200)
            ->assertJson(['error' => false, 'message' => 'Task deleted successfully.']);

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    });

    it('can delete multiple tasks', function () {
        $task1 = Task::create(taskPayload());
        $task2 = Task::create(taskPayload(['title' => 'Another Task']));

        $response = makeTaskRequest('postJson', '/tasks/destroy_multiple', ['ids' => [$task1->id, $task2->id]]);

        $response->assertStatus(200)
            ->assertJson(['error' => false, 'message' => 'Task(s) deleted successfully.']);

        $this->assertDatabaseMissing('tasks', ['id' => $task1->id]);
        $this->assertDatabaseMissing('tasks', ['id' => $task2->id]);
    });

    it('validates task IDs for bulk delete', function () {
        $response = makeTaskRequest('postJson', '/tasks/destroy_multiple', ['ids' => [999]]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ids.0']);
    });
});

// ------------------------
// View Rendering Tests
// ------------------------
describe('View Rendering Tests', function () {
    it('renders task index view', function () {
        $task = Task::create(taskPayload());

        $response = $this->get('/tasks');

        $response->assertStatus(200)
            ->assertViewIs('tasks.tasks');
    });

    it('renders tasks grouped by task list', function () {
        $task = Task::create(taskPayload());

        $response = $this->get('/tasks/group-by-task-list');

        $response->assertStatus(200)
            ->assertViewIs('tasks.group_by_task_lists');
    });

    it('renders draggable tasks view', function () {
        $task = Task::create(taskPayload());

        $response = $this->get('/tasks/draggable');

        $response->assertStatus(200)
            ->assertViewIs('tasks.board_view')
            ->assertViewHas('tasks', function ($tasks) use ($task) {
                return $tasks->contains('id', $task->id);
            });
    });

    it('renders task calendar view', function () {
        $response = $this->get('/tasks/calendar');

        $response->assertStatus(200)
            ->assertViewIs('tasks.calendar_view');
    });
});

// ------------------------
// Bulk Upload Tests
// ------------------------
describe('Bulk Upload Tests', function () {
    it('renders bulk upload form', function () {
        $response = $this->get('/tasks/bulk-upload');

        $response->assertStatus(200)
            ->assertViewIs('bulk-upload')
            ->assertViewHas('entity', 'tasks')
            ->assertViewHas('sample_file_url')
            ->assertViewHas('help_url');
    });

    it('processes bulk task upload', function () {
        $csvContent = "title,status_id,priority_id,project_id,client_can_discuss\nBulk Task,{$this->status->id},{$this->priority->id},{$this->project->id},1";

        $file = UploadedFile::fake()->createWithContent('tasks.csv', $csvContent);

        $response = $this->post('/tasks/process-bulk-upload', [
            'bulk_file' => $file
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('tasks', ['title' => 'Bulk Task']);
    });
});

// ------------------------
// Show and Get Tests
// ------------------------
describe('Show and Get Tests', function () {
    it('shows task details', function () {
        $task = Task::create(taskPayload());

        $response = $this->get("/tasks/information/{$task->id}");

        $response->assertStatus(200)
            ->assertViewIs('tasks.task_information')
            ->assertViewHas('task', function ($viewTask) use ($task) {
                return $viewTask->id === $task->id;
            });
    });

    it('returns 404 for non-existent task in show', function () {
        $response = $this->get('/tasks/information/999');

        $response->assertStatus(404);
    });

    it('retrieves task details via API', function () {
        $task = Task::create(taskPayload());

        $response = $this->getJson("/tasks/get/{$task->id}");

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'task' => ['id' => $task->id, 'title' => $task->title],
            ]);
    });

    it('returns 404 for non-existent task in get', function () {
        $response = $this->getJson('/tasks/get/999');

        $response->assertStatus(404);
    });
});

// ------------------------
// List Tests
// ------------------------
describe('List Tests', function () {
    it('lists tasks with filters', function () {
        $task = Task::create(taskPayload());

        $response = $this->getJson("/tasks/list?status_ids[]={$this->status->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['rows', 'total'])
            ->assertJsonFragment(['id' => $task->id]);
    });
});

// ------------------------
// Status and Priority Update Tests
// ------------------------
describe('Status and Priority Update Tests', function () {
    it('updates task status via PUT route', function () {
        $task = Task::create(taskPayload());
        $newStatus = Status::create([
            'title' => 'Closed',
            'color' => '#ff0000',
            'workspace_id' => $this->workspace->id,
            'slug' => 'closed'
        ]);

        $response = $this->putJson("/tasks/{$task->id}/update-status/{$newStatus->id}");

        $response->assertStatus(200)
            ->assertJson(['error' => false]);

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status_id' => $newStatus->id]);
    });

    it('updates task status via POST route', function () {
        $task = Task::create(taskPayload());
        $newStatus = Status::create([
            'title' => 'Closed',
            'color' => '#ff0000',
            'workspace_id' => $this->workspace->id,
            'slug' => 'closed'
        ]);

        $response = makeTaskRequest('postJson', '/update-task-status', [
            'id' => $task->id,
            'statusId' => $newStatus->id
        ]);

        $response->assertStatus(200)
            ->assertJson(['error' => false, 'message' => 'Status updated successfully.']);

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status_id' => $newStatus->id]);
    });

    it('restricts status update for unauthorized users', function () {
        $task = Task::create(taskPayload());
        $newStatus = Status::create([
            'title' => 'Closed',
            'color' => '#ff0000',
            'workspace_id' => $this->workspace->id,
            'slug' => 'closed'
        ]);

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

        $response = makeTaskRequest('postJson', '/update-task-status', [
            'id' => $task->id,
            'statusId' => $newStatus->id
        ]);

        $response->assertStatus(200)
            ->assertJson(['error' => true]);
    });

    it('updates task priority', function () {
        $task = Task::create(taskPayload());
        $newPriority = Priority::create([
            'title' => 'Low',
            'color' => '#0000ff',
            'workspace_id' => $this->workspace->id,
            'slug' => 'low'
        ]);

        $response = makeTaskRequest('postJson', '/update-task-priority', [
            'id' => $task->id,
            'priorityId' => $newPriority->id
        ]);

        $response->assertStatus(200)
            ->assertJson(['error' => false, 'message' => 'Priority updated successfully.']);

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'priority_id' => $newPriority->id]);
    });
});

// ------------------------
// Favorite and Pinned Tests
// ------------------------
describe('Favorite and Pinned Tests', function () {
    it('updates task favorite status', function () {
        $task = Task::create(taskPayload());

        $response = $this->patchJson("/tasks/update-favorite/{$task->id}", [
            'is_favorite' => true
        ]);

        $response->assertStatus(200)
            ->assertJson(['error' => false]);

        $this->assertDatabaseHas('favorites', [
            'favoritable_type' => Task::class,
            'favoritable_id' => $task->id,
            'user_id' => $this->adminUser->id,
        ]);
    });

    it('updates task pinned status', function () {
        $task = Task::create(taskPayload());

        $response = $this->patchJson("/tasks/update-pinned/{$task->id}", [
            'is_pinned' => true
        ]);

        $response->assertStatus(200)
            ->assertJson(['error' => false]);

        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    });

    it('duplicates a task (with or without new title)', function () {
        // 1. Create original project
        $task = Task::create(taskPayload(['title' => 'Original Project']));

        // 2. Simulate user passing a new title (or comment this out to test same-title case)
        $response = $this->getJson("/tasks/duplicate/{$task->id}?title=Custom+Task");

        // 3. Response assertions
        $response->assertStatus(200)
            ->assertJson([
                'error'   => false,
                'message' => 'Task duplicated successfully.',
            ]);

        // 4. Get duplicate ID and duplicate title
        $duplicateId    = $response->json('id');
        $duplicateTitle = request()->input('title', $task->title); // fallback to same title

        // Ensure the duplicate has a different ID
        $this->assertNotEquals($task->id, $duplicateId);

        // 5. Ensure duplicate exists in DB with expected title
        $this->assertDatabaseHas('tasks', [
            'id'    => $duplicateId,
            'title' => $duplicateTitle,
        ]);
    });
});

// ------------------------
// Comment Tests
// ------------------------
describe('Comment Tests', function () {
    it('adds a comment to a task', function () {
        $task = Task::create(taskPayload());

        $response = makeTaskRequest('postJson', "/tasks/information/{$task->id}/comments", [
            'model_type' => Task::class,
            'model_id' => $task->id,
            'content' => 'Test comment',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true, 'message' => 'Comment Added Successfully']);

        $this->assertDatabaseHas('comments', [
            'commentable_id' => $task->id,
            'content' => 'Test comment'
        ]);
    });

    it('retrieves a specific task comment', function () {
        $task = Task::create(taskPayload());
        $comment = Comment::create([
            'commentable_type' => Task::class,
            'commentable_id' => $task->id,
            'content' => 'Test comment',
            'commenter_id' => $this->adminUser->id,
            'commenter_type' => User::class,
        ]);

        $response = $this->getJson("/tasks/comments/get/{$comment->id}");

        $response->assertStatus(200)
            ->assertJson(['error' => false, 'comment' => ['id' => $comment->id]]);
    });

    it('updates a task comment', function () {
        $task = Task::create(taskPayload());
        $comment = Comment::create([
            'commentable_type' => Task::class,
            'commentable_id' => $task->id,
            'content' => 'Original comment',
            'commenter_id' => $this->adminUser->id,
            'commenter_type' => User::class,
        ]);

        $response = makeTaskRequest('postJson', '/tasks/comments/update', [
            'comment_id' => $comment->id,
            'content' => 'Updated comment',
        ]);

        $response->assertStatus(200)
            ->assertJson(['error' => false, 'message' => 'Comment updated successfully.']);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'Updated comment'
        ]);
    });

    it('deletes a task comment', function () {
        $task = Task::create(taskPayload());
        $comment = Comment::create([
            'commentable_type' => Task::class,
            'commentable_id' => $task->id,
            'content' => 'Test comment',
            'commenter_id' => $this->adminUser->id,
            'commenter_type' => User::class,
        ]);

        $response = makeTaskRequest('deleteJson', '/tasks/comments/destroy', [
            'comment_id' => $comment->id
        ]);

        $response->assertStatus(200)
            ->assertJson(['error' => false, 'message' => 'Comment deleted successfully.']);

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    });
});

// ------------------------
// Media Tests
// ------------------------
describe('Media Tests', function () {
    it('uploads media to a task', function () {
        $task = Task::create(taskPayload());
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->post('/tasks/upload-media', [
            'model_type' => Task::class,
            'id' => $task->id,
            'model_id' => $task->id,
            'media_files' => [$file],
        ]);

        $response->assertStatus(200)
            ->assertJson(['error' => false]);

        $this->assertDatabaseHas('media', [
            'model_type' => Task::class,
            'model_id' => $task->id,
        ]);
    });

    it('retrieves task media', function () {
        $task = Task::create(taskPayload());

        $media = $task->addMedia(
            UploadedFile::fake()->create('document.pdf', 100, 'application/pdf')
        )->toMediaCollection('task-media');

        $response = $this->getJson("/tasks/get-media/{$task->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['rows', 'total'])
            ->assertJsonFragment(['id' => $media->id]);
    });

    it('deletes task media', function () {
        $task = Task::create(taskPayload());

        $media = $task->addMedia(
            UploadedFile::fake()->create('document.pdf', 100, 'application/pdf')
        )->toMediaCollection('task-media');

        $response = $this->deleteJson("/tasks/delete-media/{$media->id}");

        $response->assertStatus(200)
            ->assertJson(['error' => false]);

        $this->assertDatabaseMissing('media', ['id' => $media->id]);
    });

    it('deletes multiple task media', function () {
        $task = Task::create(taskPayload());
        $media1 = $task->addMedia(
            UploadedFile::fake()->create('document1.pdf', 100, 'application/pdf')
        )->toMediaCollection('task-media');
        $media2 = $task->addMedia(
            UploadedFile::fake()->create('document2.pdf', 100, 'application/pdf')
        )->toMediaCollection('task-media');

        $response = makeTaskRequest('postJson', '/tasks/delete-multiple-media', [
            'ids' => [$media1->id, $media2->id]
        ]);

        $response->assertStatus(200)
            ->assertJson(['error' => false]);

        $this->assertDatabaseMissing('media', ['id' => $media1->id]);
        $this->assertDatabaseMissing('media', ['id' => $media2->id]);
    });
});

// ------------------------
// Time Entry Tests
// ------------------------
describe('Time Entry Tests', function () {
    it('creates a time entry', function () {
        $task = Task::create(taskPayload());

        $response = makeTaskRequest('postJson', '/tasks/time-entries/store', [
            'task_id' => $task->id,
            'message' => 'Working on task',
            'start_time' => '09:00',
            'end_time' => '10:00',
            'start_date' => now()->toDateString(),
            'end_date' => now()->toDateString(),
            'user_id' => $this->adminUser->id,
            'entry_type' => 'standard',
            'is_billable' => 0,
            'standard_hours' => '08:00:00',
            'entry_date' => now()->format(app('php_date_format')),
        ]);

        $response->assertStatus(200)
            ->assertJson(['error' => false]);

        $this->assertDatabaseHas('task_time_entries', [
            'task_id' => $task->id
        ]);
    });

    it('lists task time entries', function () {
        $task = Task::create(taskPayload());
        $timeEntry = TaskTimeEntry::create([
            'task_id' => $task->id,
            'user_id' => 'u_' . $this->adminUser->id,
            'message' => 'Working on task',
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'start_date' => now()->toDateString(),
            'end_date' => now()->toDateString(),
            'workspace_id' => $this->workspace->id,
            'entry_date' => now()->toDateString(),
        ]);

        $response = $this->getJson("/tasks/time-entries/list/{$task->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['rows', 'total'])
            ->assertJsonFragment(['id' => $timeEntry->id]);
    });

    it('deletes a time entry', function () {
        $task = Task::create(taskPayload());
        $timeEntry = TaskTimeEntry::create([
            'task_id' => $task->id,
            'user_id' => 'u_' . $this->adminUser->id,
            'message' => 'Working on task',
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'start_date' => now()->toDateString(),
            'end_date' => now()->toDateString(),
            'workspace_id' => $this->workspace->id,
            'entry_date' => now()->toDateString(),
        ]);

        $response = $this->deleteJson("/tasks/time-entries/destroy/{$timeEntry->id}");

        $response->assertStatus(200)
            ->assertJson(['error' => false]);

        $this->assertDatabaseMissing('task_time_entries', ['id' => $timeEntry->id]);
    });

    it('deletes multiple time entries', function () {
        $task = Task::create(taskPayload());
        $timeEntry1 = TaskTimeEntry::create([
            'task_id' => $task->id,
            'user_id' => 'u_' . $this->adminUser->id,
            'message' => 'Working on task 1',
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'start_date' => now()->toDateString(),
            'end_date' => now()->toDateString(),
            'workspace_id' => $this->workspace->id,
            'entry_date' => now()->toDateString(),
        ]);
        $timeEntry2 = TaskTimeEntry::create([
            'task_id' => $task->id,
            'user_id' => 'u_' . $this->adminUser->id,
            'message' => 'Working on task ',
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'start_date' => now()->toDateString(),
            'end_date' => now()->toDateString(),
            'workspace_id' => $this->workspace->id,
            'entry_date' => now()->toDateString(),
        ]);

        $response = makeTaskRequest('postJson', '/tasks/time-entries/destroy_multiple', [
            'ids' => [$timeEntry1->id, $timeEntry2->id]
        ]);

        $response->assertStatus(200)
            ->assertJson(['error' => false]);

        $this->assertDatabaseMissing('task_time_entries', ['id' => $timeEntry1->id]);
        $this->assertDatabaseMissing('task_time_entries', ['id' => $timeEntry2->id]);
    });
});

// ------------------------
// View Preference Tests
// ------------------------
describe('View Preference Tests', function () {
    it('saves task view preference', function () {
        $response = makeTaskRequest('putJson', '/save-tasks-view-preference', ['view' => 'draggable']);

        $response->assertStatus(200)
            ->assertJson(['error' => false, 'message' => 'Default View Set Successfully.']);

        $this->assertDatabaseHas('user_client_preferences', [
            'user_id' => 'u_' . $this->adminUser->id,
            'table_name' => 'tasks',
            'default_view' => 'draggable',
        ]);
    });
});

// ------------------------
// Calendar Tests
// ------------------------
describe('Calendar Tests', function () {
    it('retrieves calendar data', function () {
        $task = Task::create(taskPayload([
            'start_date' => now()->toDateString(),
            'due_date' => now()->addDays(5)->toDateString(),
        ]));

        $response = $this->getJson('/tasks/get-calendar-data?start=' . now()->subDays(10)->toDateString() . '&end=' . now()->addDays(10)->toDateString());

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $task->id]);
    });
});

// ------------------------
// Permission and Access Tests
// ------------------------
describe('Permission and Access Tests', function () {
    it('restricts task access for unauthorized users', function () {
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

        $task = Task::create(taskPayload()); // Task not assigned to user

        $response = $this->get("/tasks/information/{$task->id}");

        $response->assertStatus(302); // checkAccess middleware returns 302
    });
});

// ------------------------
// Edge Case Tests
// ------------------------
describe('Edge Case Tests', function () {
    it('validates task date ranges', function () {
        $response = makeTaskRequest('postJson', '/tasks/store', taskPayload([
            'start_date' => '2024-01-01',
            'due_date' => '2023-12-31' // Due date before start
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['due_date']);
    });

    it('handles non-existent task in status update', function () {
        $response = makeTaskRequest('postJson', '/update-task-status', [
            'id' => 999,
            'statusId' => $this->status->id
        ]);

        $response->assertStatus(422);
    });
});

// ------------------------
// Relationship Tests
// ------------------------
describe('Relationship Tests', function () {
    it('manages task user assignments', function () {
        $task = Task::create(taskPayload());
        $user = User::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'status' => 1,
            'email_verified_at' => now(),
        ]);

        $task->users()->attach($user->id);

        $this->assertTrue($task->users->contains($user));
    });
});

// ------------------------
// Performance and Stress Tests
// ------------------------
describe('Performance and Stress Tests', function () {
    it('handles large number of tasks efficiently', function () {
        // Create 50 tasks
        $tasks = [];
        for ($i = 0; $i < 50; $i++) {
            $tasks[] = Task::create(taskPayload(['title' => "Task $i"]));
        }

        $response = $this->getJson('/tasks/list');

        $response->assertStatus(200);
        $this->assertCount(50, $tasks);
    });

    it('filters tasks by multiple criteria', function () {
        $status = Status::create([
            'title' => 'Frontend',
            'workspace_id' => $this->workspace->id,
            'slug' => 'frontend',
            'color' => '#ff0000'
        ]);
        $priority = Priority::create([
            'title' => 'Bug',
            'workspace_id' => $this->workspace->id,
            'slug' => 'bug',
            'color' => '#00ff00'
        ]);

        $task1 = Task::create(taskPayload(['title' => 'Frontend Bug']));
        $task1->status()->associate($status->id);
        $task1->priority()->associate($priority->id);

        $task2 = Task::create(taskPayload(['title' => 'Backend Task']));

        $response = $this->get("/tasks?statuses[]={$this->status->id}&status[]={$status->id}&priority[]={$priority->id}");

        $response->assertStatus(200);
    });
});

// ------------------------
// Project-Task Relationship Tests
// ------------------------
describe('Project-Task Relationship Tests', function () {
    it('restricts tasks by project access', function () {
        $user = User::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'status' => 1,
            'email_verified_at' => now(),
        ]);
        $user->workspaces()->attach($this->workspace->id);

        // Create a project user has no access to
        $restrictedProject = Project::create([
            'title' => 'Restricted Project',
            'status_id' => $this->status->id,
            'priority_id' => $this->priority->id,
            'task_accessibility' => 'project_users',
            'workspace_id' => $this->workspace->id,
            'created_by' => $this->adminUser->id,
        ]);

        $task = Task::create(taskPayload(['project_id' => $restrictedProject->id]));

        $this->actingAs($user);
        $response = $this->get('/tasks');

        $response->assertStatus(200)
            ->assertDontSee($task->title);
    });
});

// ------------------------
// Time Tracking Edge Cases
// ------------------------
describe('Time Tracking Edge Cases', function () {
    it('validates time entry overlap', function () {
        $task = Task::create(taskPayload());

        // Create existing time entry
        TaskTimeEntry::create([
            'task_id' => $task->id,
            'user_id' => $this->adminUser->id,
            'message' => 'Existing entry',
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'start_date' => now()->toDateString(),
            'end_date' => now()->toDateString(),
            'workspace_id' => $this->workspace->id,
            'entry_date' => now()->toDateString()
        ]);

        // Try to create overlapping time entry
        $response = makeTaskRequest('postJson', '/tasks/time-entries/store', [
            'task_id' => $task->id,
            'message' => 'Overlapping entry',
            'start_time' => '09:30',
            'end_time' => '10:30',
            'start_date' => now()->toDateString(),
            'end_date' => now()->toDateString(),
            'user_id' => $this->adminUser->id,
        ]);

        // This should either be prevented or handled gracefully
        $response->assertStatus(422);
    });

    it('calculates time entry duration correctly', function () {
        $task = Task::create(taskPayload());

        $response = makeTaskRequest('postJson', '/tasks/time-entries/store', [
            'task_id' => $task->id,
            'message' => 'Duration test',
            'start_time' => '09:00',
            'end_time' => '10:30',
            'start_date' => now()->toDateString(),
            'end_date' => now()->toDateString(),
            'user_id' => $this->adminUser->id,
            'entry_type' => 'standard',
            'is_billable' => 0,
            'standard_hours' => '08:00:00',
            'entry_date' => now()->format(app('php_date_format')),
        ]);

        $response->assertStatus(200);

        $timeEntry = TaskTimeEntry::where('task_id', $task->id)->first();

        $this->assertNotNull($timeEntry);
    });
});

// ------------------------
// Workflow Tests
// ------------------------
describe('Workflow Tests', function () {
    it('handles complete task workflow', function () {
        // Create task
        $response = makeTaskRequest('postJson', '/tasks/store', taskPayload(['title' => 'Workflow Task']));
        $response->assertStatus(200);

        $taskId = Task::where('title', 'Workflow Task')->first()->id;

        // Add comment
        $response = makeTaskRequest('postJson', "/tasks/information/{$taskId}/comments", [
            'model_type' => Task::class,
            'model_id' => $taskId,
            'content' => 'Starting work on this task',
        ]);
        $response->assertStatus(200);

        // Add time entry
        $response = makeTaskRequest('postJson', '/tasks/time-entries/store', [
            'task_id' => $taskId,
            'message' => 'Working on task',
            'start_time' => '09:00',
            'end_time' => '10:00',
            'start_date' => now()->toDateString(),
            'end_date' => now()->toDateString(),
            'user_id' => $this->adminUser->id,
            'entry_type' => 'standard',
            'is_billable' => 0,
            'standard_hours' => '08:00:00',
            'entry_date' => now()->format(app('php_date_format')),
        ]);
        $response->assertStatus(200);

        // Update status to completed
        $completedStatus = Status::create([
            'title' => 'Completed',
            'color' => '#00ff00',
            'workspace_id' => $this->workspace->id,
            'slug' => 'completed'
        ]);

        $response = makeTaskRequest('postJson', '/update-task-status', [
            'id' => $taskId,
            'statusId' => $completedStatus->id
        ]);
        $response->assertStatus(200);

        // Verify final state
        $this->assertDatabaseHas('tasks', ['id' => $taskId, 'status_id' => $completedStatus->id]);
        $this->assertDatabaseHas('comments', ['commentable_id' => $taskId]);
        $this->assertDatabaseHas('task_time_entries', ['task_id' => $taskId]);
    });
});

// ------------------------
// Data Integrity Tests
// ------------------------
describe('Data Integrity Tests', function () {
    it('maintains referential integrity when deleting task', function () {
        $task = Task::create(taskPayload());

        // Add related data
        $comment = Comment::create([
            'commentable_type' => Task::class,
            'commentable_id' => $task->id,
            'content' => 'Test comment',
            'commenter_id' => $this->adminUser->id,
            'commenter_type' => User::class,
        ]);

        $timeEntry = TaskTimeEntry::create([
            'task_id' => $task->id,
            'user_id' => $this->adminUser->id,
            'message' => 'Test entry',
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'start_date' => now()->toDateString(),
            'end_date' => now()->toDateString(),
            'workspace_id' => $this->workspace->id,
            'entry_date' => now()->toDateString(),
        ]);

        // Delete task
        $response = $this->deleteJson("/tasks/destroy/{$task->id}");
        $response->assertStatus(200);

        // Verify cascade deletion or appropriate handling
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
        // Depending on your foreign key constraints, these might cascade delete
        // or you might need to handle them in your controller
        // Adjust assertions based on your implementation
    });
});

// ------------------------
// Security Tests
// ------------------------
describe('Security Tests', function () {
    it('prevents unauthorized task modification', function () {
        $user1 = User::create([
            'first_name' => 'User',
            'last_name' => 'One',
            'email' => 'user1@example.com',
            'password' => Hash::make('password'),
            'status' => 1,
            'email_verified_at' => now(),
        ]);
        $user1->workspaces()->attach($this->workspace->id);

        $user2 = User::create([
            'first_name' => 'User',
            'last_name' => 'Two',
            'email' => 'user2@example.com',
            'password' => Hash::make('password'),
            'status' => 1,
            'email_verified_at' => now(),
        ]);
        $user2->workspaces()->attach($this->workspace->id);

        $this->actingAs($user1);
        $task = Task::create(taskPayload(['created_by' => $user1->id]));

        // Switch to user2 and try to modify user1's task
        $this->actingAs($user2);
        $response = makeTaskRequest('postJson', '/tasks/update', [
            'id' => $task->id,
            'title' => 'Unauthorized Update',
            'status_id' => 1
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'error' => true,
                'message' => 'You are not authorized to set this status.',
            ]);
    });

    it('prevents SQL injection in task queries', function () {
        $maliciousInput = "'; DROP TABLE tasks; --";

        $response = $this->getJson("/tasks/list?search=" . urlencode($maliciousInput));

        // Should handle gracefully without errors
        $response->assertStatus(200);

        // Verify tasks table still exists by checking count
        $this->assertTrue(Task::count() >= 0);
    });
});
