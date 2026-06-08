@extends('layout')
@section('title')
    @if ($is_favorites == 1)
        {{ get_label('favorite', 'Favorite') }}
    @endif
    <?= get_label('tasks', 'Tasks') ?> - <?= get_label('list_view', 'List view') ?>
@endsection
@section('content')
    <div class="container-fluid">
    @isset($project->id)
        {{-- ===== Project list view: same kit header + toolbar as the board, with a docked task inspector ===== --}}
        <header class="tk-proj-head mt-4 mb-3">
            <div class="tk-proj-headmain">
                <div class="tk-proj-eyebrow mono">
                    <span class="kcol-dot kcol-dot-{{ $project->status->color }}"></span>
                    {{ strtoupper(str_replace(' ', '-', $project->title)) }}@if($project->clients->isNotEmpty()) · {{ strtoupper($project->clients->first()->first_name.' '.$project->clients->first()->last_name) }}@endif
                </div>
                <h1 class="tk-proj-title">
                    {{ $project->title }}
                    <a href="javascript:void(0);" class="tk-proj-ic">
                        <i class='bx {{getFavoriteStatus($project->id) ? "bxs" : "bx"}}-star favorite-icon text-warning' data-id="{{$project->id}}" data-favorite="{{getFavoriteStatus($project->id) ? 1 : 0}}"></i>
                    </a>
                    <a href="javascript:void(0);" class="tk-proj-ic">
                        <i class='bx {{getPinnedStatus($project->id) ? "bxs" : "bx"}}-pin pinned-icon text-success' data-id="{{$project->id}}" data-pinned="{{getPinnedStatus($project->id)}}" data-require_reload="0"></i>
                    </a>
                </h1>
                <div class="tk-proj-meta">
                    <span><x-tk-icon name="check" size="13" /> {{ $project->tasks->count() }} {{ get_label('tasks', 'tasks') }}</span>
                    <span><x-tk-icon name="users" size="13" /> {{ $project->users->count() }} {{ get_label('members', 'members') }}</span>
                    @if($project->end_date)<span><x-tk-icon name="calendar" size="13" /> {{ get_label('due', 'Due') }} {{ format_date($project->end_date) }}</span>@endif
                </div>
            </div>
            <div class="tk-proj-headside">
                @if (getAuthenticatedUser() && getAuthenticatedUser()->can('manage_tasks'))
                <a href="javascript:void(0);" class="btn btn-sm btn-primary" data-bs-toggle="offcanvas" data-bs-target="#create_task_offcanvas" aria-controls="create_task_offcanvas"><x-tk-icon name="plus" class="me-1" />{{ get_label('new_task', 'New task') }}</a>
                @endif
            </div>
        </header>
        <div class="tk-board-toolbar mb-3">
            <div class="tk-seg">
                <a href="{{ url('projects/information/'.$project->id) }}" class="tk-seg-item"><x-tk-icon name="kanban" size="14" /> {{ get_label('board', 'Board') }}</a>
                <a href="javascript:void(0);" class="tk-seg-item active"><x-tk-icon name="list" size="14" /> {{ get_label('list', 'List') }}</a>
                <a href="{{ url('projects/tasks/calendar/'.$project->id) }}" class="tk-seg-item"><x-tk-icon name="calendar" size="14" /> {{ get_label('timeline', 'Timeline') }}</a>
            </div>
            <span class="tk-toolbar-spacer"></span>
        </div>
        <?php $id = 'project_' . $project->id; ?>
        <x-tasks-card :tasks="$tasks" :id="$id" :project="$project" :favorites="$is_favorites" :customFields="$taskCustomFields" />
    @else
        <div class="d-flex justify-content-between mb-2 mt-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ url('home') }}"><?= get_label('home', 'Home') ?></a>
                        </li>
                        @isset($project->id)
                            <li class="breadcrumb-item">
                                <a
                                    href="{{ url(getUserPreferences('projects', 'default_view')) }}"><?= get_label('projects', 'Projects') ?></a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ url('projects/information/' . $project->id) }}">{{ $project->title }}</a>
                            </li>
                        @endisset
                        <li class="breadcrumb-item"><?= get_label('tasks', 'Tasks') ?></li>
                        @if ($is_favorites == 1)
                            <li class="breadcrumb-item"><?= get_label('favorite', 'Favorite') ?></li>
                        @endif
                        <li class="breadcrumb-item active">
                            <?= get_label('list', 'List') ?>
                        </li>
                    </ol>
                </nav>
            </div>
            <div>
                @php
                    $taskDefaultView = getUserPreferences('tasks', 'default_view');
                @endphp
                @if (!$taskDefaultView || $taskDefaultView === 'tasks')
                    <span class="badge bg-primary"><?= get_label('default_view', 'Default View') ?></span>
                @else
                    <a href="javascript:void(0);"><span class="badge bg-secondary" id="set-default-view" data-type="tasks"
                            data-view="list"><?= get_label('set_as_default_view', 'Set as Default View') ?></span></a>
                @endif
            </div>
            <div>
                @php
                    $projectId = isset($project->id)
                        ? $project->id
                        : (request()->has('project')
                            ? request('project')
                            : '');

                    // Base URL logic
                    if ($is_favorites) {
                        $url =
                            isset($project->id) || request()->has('project')
                                ? url('/projects/tasks/draggable')
                                : url('/tasks/draggable');
                    } else {
                        $url =
                            isset($project->id) || request()->has('project')
                                ? url('/projects/tasks/draggable/' . $projectId)
                                : url('/tasks/draggable');
                    }

                    // Collect query parameters
                    $queryParams = [];
                    if (request()->has('status')) {
                        $queryParams['status'] = request('status');
                    }
                    if ($is_favorites) {
                        $queryParams['favorite'] = 1;
                    }

                    // Append query parameters to the URL
                    if (!empty($queryParams)) {
                        $url .= '?' . http_build_query($queryParams);
                    }
                @endphp


                <a href="javascript:void(0);" data-bs-toggle="offcanvas" data-bs-target="#create_task_offcanvas"><button
                        type="button" class="btn btn-sm btn-primary action_create_tasks" data-bs-toggle="tooltip"
                        data-bs-placement="right"
                        data-bs-original-title=" <?= get_label('create_task', 'Create task') ?>"><i
                            class="bx bx-plus"></i></button></a>
                <a href="{{ $url }}"><button type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                        data-bs-placement="left"
                        data-bs-original-title="<?= get_label('draggable_view', 'Draggable View') ?>"><i
                            class="bx bxs-dashboard"></i></button></a>
                <a href="{{route('tasks.groupByTaskList') }}"><button type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                    data-bs-placement="left"
                    data-bs-original-title="<?= get_label('group_by_task_list', 'Group By Task List') ?>"><i
                        class="bx bx-align-middle"></i></button></a>
                @php
                    $projectId = isset($project->id)
                        ? $project->id
                        : (request()->has('project')
                            ? request('project')
                            : '');

                    // Base URL logic
                    if ($is_favorites) {
                        $url =
                            isset($project->id) || request()->has('project')
                                ? url('/projects/tasks/calendar')
                                : url('/tasks/calendar');
                    } else {
                        $url =
                            isset($project->id) || request()->has('project')
                                ? url('/projects/tasks/calendar/' . $projectId)
                                : url('/tasks/calendar');
                    }

                    // Collect query parameters
                    $queryParams = [];
                    if (request()->has('status')) {
                        $queryParams['status'] = request('status');
                    }
                    if ($is_favorites) {
                        $queryParams['favorite'] = 1; // Add favorite=1 when $is_favorites is true
                    }

                    // Append the query parameters to the URL
                    if (!empty($queryParams)) {
                        $url .= '?' . http_build_query($queryParams);
                    }
                @endphp


                <a href="{{ $url }}"><button type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                        data-bs-placement="left"
                        data-bs-original-title="<?= get_label('calendar_view', 'Calendar View') ?>"><i
                            class="bx bx-calendar"></i></button></a>
            </div>
        </div>
        <?php
        $id = isset($project->id) ? 'project_' . $project->id : '';
        ?>
        <x-tasks-card :tasks="$tasks" :id="$id" :project="$project" :favorites="$is_favorites" :customFields="$taskCustomFields" />
    @endisset
    </div>
@endsection
