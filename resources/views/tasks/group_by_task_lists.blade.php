@extends('layout')
@section('title')
    {{ get_label('tasks', 'Tasks') }} - {{ get_label('group_by_task_lists', 'Group by task lists') }}
@endsection
@section('content')
    <div class="container-fluid">
        <!-- Breadcrumb Navigation -->
        <div class="d-flex justify-content-between align-items-center mb-4 mt-4">
            <!-- Left Side: Breadcrumbs and Badge -->
            <div class="d-flex align-items-center gap-3">
                <nav class="breadcrumb" aria-label="breadcrumb">
                    <a class="breadcrumb-item" href="{{ route('home.index') }}"><?= get_label('home', 'Home') ?></a>
                    @isset($project->id)
                        <span class="breadcrumb-sep">/</span>
                        <a class="breadcrumb-item" href="{{ url(getUserPreferences('projects', 'default_view')) }}"><?= get_label('projects', 'Projects') ?></a>
                        <span class="breadcrumb-sep">/</span>
                        <a class="breadcrumb-item" href="{{ url('projects/information/' . $project->id) }}">{{ $project->title }}</a>
                    @endisset
                    <span class="breadcrumb-sep">/</span>
                    <a class="breadcrumb-item" href="{{ route('tasks.index') }}"><?= get_label('tasks', 'Tasks') ?></a>
                    @if ($is_favorites == 1)
                        <span class="breadcrumb-sep">/</span>
                        <span class="breadcrumb-item"><?= get_label('favorite', 'Favorite') ?></span>
                    @endif
                    <span class="breadcrumb-sep">/</span>
                    <span class="breadcrumb-current"><?= get_label('group_by_task_list', 'Group By Task List') ?></span>
                </nav>

                @php
                    $taskDefaultView = getUserPreferences('tasks', 'default_view');
                @endphp
                @if ($taskDefaultView === 'tasks/group-by-task-list')
                    <span class="badge bg-primary"><?= get_label('default_view', 'Default View') ?></span>
                @else
                    <a href="javascript:void(0);" id="set-default-view" data-type="tasks" data-view="group-by-task-list">
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

                    // Draggable View Url
                    $draggableUrl = isset($project->id) ? url('/projects/tasks/draggable/' . $projectId) : url('/tasks/draggable');
                    if (request()->has('status')) {
                        $draggableUrl .= (strpos($draggableUrl, '?') === false ? '?' : '&') . 'status=' . request('status');
                    }
                    if ($is_favorites) {
                        $draggableUrl .= (strpos($draggableUrl, '?') === false ? '?' : '&') . 'favorite=1';
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
                    <a href="{{ $draggableUrl }}" class="seg-btn" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="<?= get_label('draggable_view', 'Draggable View') ?>">
                        <i class='bx bxs-dashboard'></i>
                    </a>
                    <a href="javascript:void(0);" class="seg-btn on" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="<?= get_label('group_by_task_list', 'Group By Task List') ?>">
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
        <!-- Task Table -->
        <div class="card">
            <div class="card-header border-bottom py-3">
                <div class="d-flex justify-content-between align-items-center row">
                    <div class="col-md-6">
                        <h5 class="mb-0">{{ get_label('grouped_by_task_lists', 'Grouped by Task lists') }}</h5>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="task-lists" id="taskListsContainer">
                    <x-group-task-list :taskLists="$taskLists" />
                </div>
                <!-- Loading Indicator -->
                <div id="loadingIndicator" class="d-none">
                    <div class="d-flex justify-content-center py-3">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('assets/js/pages/group-by-task-lists.js') }}"></script>

@endsection
