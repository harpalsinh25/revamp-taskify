@extends('layout')
@section('title')
@section('title')
@if($is_favorites == 1)
    {{ get_label('favorite', 'Favorite') }}
@endif
<?= get_label('tasks', 'Tasks') ?> - <?= get_label('draggable', 'Draggable') ?>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-4">
        <!-- Left Side: Breadcrumbs and Badge -->
        <div class="d-flex align-items-center gap-3">
            <nav class="breadcrumb" aria-label="breadcrumb">
                <a class="breadcrumb-item" href="{{ url('home') }}"><?= get_label('home', 'Home') ?></a>
                @if (isset($project->id))
                    <span class="breadcrumb-sep">/</span>
                    <a class="breadcrumb-item" href="{{ url(getUserPreferences('projects', 'default_view')) }}"><?= get_label('projects', 'Projects') ?></a>
                    <span class="breadcrumb-sep">/</span>
                    <a class="breadcrumb-item" href="{{ url('projects/information/' . $project->id) }}">{{ $project->title }}</a>
                @endif
                <span class="breadcrumb-sep">/</span>
                <a class="breadcrumb-item" href="{{ route('tasks.index') }}"><?= get_label('tasks', 'Tasks') ?></a>
                @if ($is_favorites == 1)
                    <span class="breadcrumb-sep">/</span>
                    <span class="breadcrumb-item"><?= get_label('favorite', 'Favorite') ?></span>
                @endif
                <span class="breadcrumb-sep">/</span>
                <span class="breadcrumb-current"><?= get_label('draggable', 'Draggable') ?></span>
            </nav>

            @php
                $taskDefaultView = getUserPreferences('tasks', 'default_view');
            @endphp
            @if ($taskDefaultView === 'tasks/draggable')
                <span class="badge bg-primary"><?= get_label('default_view', 'Default View') ?></span>
            @else
                <a href="javascript:void(0);" id="set-default-view" data-type="tasks" data-view="draggable">
                    <span class="badge bg-secondary"><?= get_label('set_as_default_view', 'Set as Default View') ?></span>
                </a>
            @endif
        </div>

        <!-- Right Side: View modes and Actions -->
        <div class="d-flex align-items-center gap-3">
            @php
                $projectId = isset($project->id) ? $project->id : (request()->has('project') ? request('project') : '');
                
                // List View Url
                $listUrl = isset($project->id) ? url('/projects/tasks/list/' . $project->id) : url('/tasks');
                if (request()->has('status')) {
                    $listUrl .= (strpos($listUrl, '?') === false ? '?' : '&') . 'status=' . request('status');
                }
                if ($is_favorites) {
                    $listUrl .= (strpos($listUrl, '?') === false ? '?' : '&') . 'favorite=1';
                }

                // Calendar View Url
                if ($is_favorites) {
                    $calendarUrl = isset($project->id) || request()->has('project') ? url('/projects/tasks/calendar') : url('/tasks/calendar');
                } else {
                    $calendarUrl = isset($project->id) || request()->has('project') ? url('/projects/tasks/calendar/' . $projectId) : url('/tasks/calendar');
                }
                if (request()->has('status')) {
                    $calendarUrl .= (strpos($calendarUrl, '?') === false ? '?' : '&') . 'status=' . request('status');
                }
                if ($is_favorites) {
                    $calendarUrl .= (strpos($calendarUrl, '?') === false ? '?' : '&') . 'favorite=1';
                }
            @endphp

            <div class="seg">
                <a href="{{ $listUrl }}" class="seg-btn" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="<?= get_label('list_view', 'List view') ?>">
                    <i class='bx bx-list-ul'></i>
                </a>
                <a href="javascript:void(0);" class="seg-btn on" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="<?= get_label('draggable_view', 'Draggable View') ?>">
                    <i class='bx bxs-dashboard'></i>
                </a>
                <a href="{{ route('tasks.groupByTaskList') }}" class="seg-btn" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="<?= get_label('group_by_task_list', 'Group By Task List') ?>">
                    <i class='bx bx-align-middle'></i>
                </a>
                <a href="{{ $calendarUrl }}" class="seg-btn" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="<?= get_label('calendar_view', 'Calendar View') ?>">
                    <i class='bx bx-calendar'></i>
                </a>
            </div>

            @if (getAuthenticatedUser() && getAuthenticatedUser()->can('create_tasks'))
            <a href="javascript:void(0);" data-bs-toggle="offcanvas" data-bs-target="#create_task_offcanvas">
                <button type="button" class="btn btn-sm btn-primary action_create_tasks" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-original-title="<?= get_label('create_task', 'Create task') ?>">
                    <i class='bx bx-plus'></i>
                </button>
            </a>
            @endif
        </div>
    </div>
    @if ($total_tasks > 0)
    <div class="alert alert-primary alert-dismissible" role="alert">
        <?= get_label('drag_drop_update_task_status', 'Drag and drop to update task status') . ' !' ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    {{-- Taskify v2 — Kanban board (design-system .kanban / .kcol / .tcard).
         Drag-and-drop hooks preserved: column id={slug} + data-status are the
         dragula containers, the task cards (data-task-id) are the draggables. --}}
    <div class="kanban-board tk-kanban">
        @foreach ($statuses as $status)
        @php $statusTaskCount = collect($tasks)->where('status_id', $status->id)->count(); @endphp
        <div class="kcol kanban-column">
            <div class="kcol-head">
                <span class="kcol-dot kcol-dot-{{ $status->color }}"></span>
                <span class="kcol-name">{{ $status->title }}</span>
                <span class="kcol-count">{{ $statusTaskCount }}</span>
            </div>
            <div class="kanban-tasks kcol-body" id="{{ $status->slug }}" data-status="{{ $status->id }}">
                @foreach ($tasks as $task)
                @if($task->status_id==$status->id)
                <x-kanban :task="$task" />
                @endif
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
    @else
    <?php
    $type = 'Tasks';
    ?>
    <x-empty-state-card :type="$type" />
    @endif
</div>
@endsection

@section('page_scripts')
<script>
    var statusArray = <?php echo json_encode($statuses); ?>;
</script>
<script src="{{asset('assets/js/pages/task-board.js')}}"></script>
@endsection
