@extends('layout')
@section('title')
    <?= get_label('dashboard', 'Dashboard') ?>
@endsection
@section('content')
    @authBoth
    <div class="container-fluid">
        {{-- Welcome card (Taskify v2 — Graphite hero) --}}
        @php
            $tkUser = getAuthenticatedUser();
            $tkWs = \App\Models\Workspace::find(getWorkspaceId());
            $tkAllData = isAdminOrHasAllDataAccess();
            // Task counts mirror HomeController scoping: workspace tasks for admins,
            // assigned tasks otherwise. Display-only, no logic changed.
            $tkTotalTasks = $tkAllData ? ($tkWs ? $tkWs->tasks()->count() : 0) : $tkUser->tasks()->count();
            $tkDeadlines = $tkAllData
                ? ($tkWs
                    ? $tkWs->tasks()->whereNotNull('tasks.due_date')
                        ->whereBetween('tasks.due_date', [now()->startOfDay(), now()->endOfWeek()])->count()
                    : 0)
                : $tkUser->tasks()->whereNotNull('tasks.due_date')
                    ->whereBetween('tasks.due_date', [now()->startOfDay(), now()->endOfWeek()])->count();
        @endphp
        <div class="tk-welcome d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="tk-welcome-main">
                    @php
                    $dashboardNow = now()->tz(config('app.timezone'));
                @endphp
                <div class="tk-welcome-eyebrow">{{ $dashboardNow->format('H:i') }} · {{ $dashboardNow->translatedFormat('D d M') }} · {{ get_label('wk', 'WK') }} {{ $dashboardNow->weekOfYear }}</div>
                <h1 class="tk-welcome-title">{{ get_label('welcome_back', 'Welcome back') }}, {{ $tkUser->first_name }}.</h1>
                <p class="tk-welcome-sub">
                    <span class="tk-welcome-stat {{ $tkDeadlines > 0 ? 'tk-stat-warn' : '' }}">{{ $tkDeadlines }}</span>
                    {{ $tkDeadlines == 1 ? get_label('deadline_this_week', 'deadline this week') : get_label('deadlines_this_week', 'deadlines this week') }}
                    <span class="tk-welcome-dot">·</span>
                    <span class="tk-welcome-stat">{{ $tkTotalTasks }}</span>
                    {{ $tkTotalTasks == 1 ? get_label('task', 'task') : get_label('tasks', 'tasks') }}
                </p>
            </div>
            <div class="d-flex gap-2 align-items-center">
                @if ($auth_user->can('manage_tasks'))
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#create_task_modal">
                    <i class='bx bx-plus'></i> {{ get_label('create_task', 'Create Task') }}
                </button>
                @endif
                @if ($auth_user->can('manage_projects'))
                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#create_project_modal">
                    <i class='bx bx-plus'></i> {{ get_label('create_project', 'Create Project') }}
                </button>
                @endif
            </div>
        </div>


        <!-- Alert for Reset Warning -->
        @if (config('constants.ALLOW_MODIFICATION') === 0)
            <x-dashboard.alert type="warning" classes="container mb-0 mt-4" icon="bx bx-timer"
                message="{{ get_label('important_data_automatically_resets_every_24_hours', 'Important: Data automatically resets every 24 hours!') }}"
                dismissible="true" />
        @endif
        @php
            $tiles = [
                'manage_projects' => [
                    'id' => 'projects-tile',
                    'permission' => 'manage_projects',
                    'icon' => 'bx bx-briefcase-alt-2',
                    'icon-bg' => 'bg-label-success',
                    'label' => get_label('total_projects', 'Total projects'),
                    'count' => 0,
                    'url' => url(getUserPreferences('projects', 'default_view')),
                    'link_color' => 'text-success',
                    'custom-card-class' => 'custom-card-success',
                ],
                'manage_tasks' => [
                    'id' => 'tasks-tile',
                    'permission' => 'manage_tasks',
                    'icon' => 'bx bx-task text-primary',
                    'icon-bg' => 'bg-label-primary',
                    'label' => get_label('total_tasks', 'Total tasks'),
                    'count' => 0,
                    'url' => url(getUserPreferences('tasks', 'default_view')),
                    'link_color' => 'text-primary',
                    'custom-card-class' => 'custom-card-primary',
                ],
                'manage_users' => [
                    'id' => 'users-tile',
                    'permission' => 'manage_users',
                    'icon' => 'bx bxs-user-detail text-warning',
                    'icon-bg' => 'bg-label-warning',
                    'label' => get_label('total_users', 'Total users'),
                    'count' => 0,
                    'url' => url('users'),
                    'link_color' => 'text-warning',
                    'custom-card-class' => 'custom-card-warning',
                ],
                'manage_clients' => [
                    'id' => 'clients-tile',
                    'permission' => 'manage_clients',
                    'icon' => 'bx bxs-user-detail text-info',
                    'icon-bg' => 'bg-label-info',
                    'label' => get_label('total_clients', 'Total clients'),
                    'count' => 0,
                    'url' => url('clients'),
                    'link_color' => 'text-info',
                    'custom-card-class' => 'custom-card-info',
                ],
                'manage_meetings' => [
                    'id' => 'meetings-tile',
                    'permission' => 'manage_meetings',
                    'icon' => 'bx bx-shape-polygon text-warning',
                    'icon-bg' => 'bg-label-warning',
                    'label' => get_label('total_meetings', 'Total meetings'),
                    'count' => 0,
                    'url' => url('meetings'),
                    'link_color' => 'text-warning',
                    'custom-card-class' => 'custom-card-warning',
                ],
                'total_todos' => [
                    'id' => 'todos-tile',
                    'permission' => null,
                    'icon' => 'bx bx-list-check text-info',
                    'icon-bg' => 'bg-label-info',
                    'label' => get_label('total_todos', 'Total todos'),
                    'count' => 0,
                    'url' => url('todos'),
                    'link_color' => 'text-info',
                    'custom-card-class' => 'custom-card-info',
                ],
            ];
            $filteredTiles = array_filter($tiles, function ($tile) use ($auth_user) {
                return !$tile['permission'] || $auth_user->can($tile['permission']);
            });
        @endphp
        <div class="tk-metric-strip">
            @foreach ($filteredTiles as $tile)
                <x-dashboard.tile id="{{ $tile['id'] }}" label="{{ $tile['label'] }}" count="{{ $tile['count'] }}"
                    url="{{ $tile['url'] }}" linkColor="{{ $tile['link_color'] }}" icon="{{ $tile['icon'] }}"
                    iconBg="{{ $tile['icon-bg'] }}" customCardClass="{{ $tile['custom-card-class'] }}"
                    extraAttributes="data-id='{{ $tile['id'] }}' class='draggable-item'" />
            @endforeach
        </div>
        {{-- Taskify v2 main grid (kit d-grid).
             LEFT (8): Recent Activity (top) + Income vs Expense area chart.
             RIGHT (4): Project chart, then Task chart, then Todo chart.
             All are kit SVG charts fed by the existing dashboard AJAX (read via
             ajaxSuccess); dashboard.js, routes and logic are untouched. The
             equivalent cards in the old statistics grid are hidden via CSS. --}}
        @php $tkHasHero = $auth_user->hasRole('admin'); @endphp
        <div class="row g-4 tk-dash-grid mb-4">
            {{-- LEFT COLUMN --}}
            <div class="col-lg-8 d-flex flex-column gap-4">
                @if ($auth_user->can('manage_projects') || $auth_user->can('manage_tasks'))
                    <div class="tk-card flex-grow-0" data-id="tk-combined-chart">
                        <div class="tk-card-head">
                            <div class="tk-card-head-main">
                                <div class="tk-card-eyebrow">{{ get_label('overview', 'Overview') }}</div>
                                <h3 class="tk-card-title">{{ get_label('projects_and_tasks', 'Projects & Tasks') }}</h3>
                            </div>
                        </div>
                        <div class="tk-card-body p-2">
                            <div id="tk-combined-bar-chart"></div>
                        </div>
                    </div>
                @endif
                <div class="tk-card {{ $tkHasHero ? '' : 'flex-grow-1' }}" data-id="tk-activity-card">
                    <div class="tk-card-head">
                        <div class="tk-card-head-main">
                            <div class="tk-card-eyebrow">{{ get_label('activity_feed', 'Activity feed') }}</div>
                            <h3 class="tk-card-title">{{ get_label('recent_activities', 'Recent Activities') }}</h3>
                        </div>
                        <a href="{{ url('activity-log') }}" class="tk-card-link">{{ get_label('view_more', 'View more') }}</a>
                    </div>
                    <div class="tk-card-body">
                        <div id="tk-activity-list" class="tk-act-list"
                            data-empty-label="{{ get_label('no_activities', 'No recent activities') }}"></div>
                    </div>
                </div>
                @if ($tkHasHero)
                    <div class="tk-card tk-hero-card flex-grow-1" data-id="income-vs-expense-hero">
                        <div class="tk-card-head">
                            <div class="tk-card-head-main">
                                <div class="tk-card-eyebrow">{{ get_label('cash_flow', 'Cash flow') }}</div>
                                <h3 class="tk-card-title">{{ get_label('income_vs_expense', 'Income vs Expense') }}</h3>
                            </div>
                            <div class="tk-seg" data-chart="hero" role="radiogroup">
                                <button type="button" class="tk-seg-btn on" role="radio" aria-checked="true"
                                    data-value="both">{{ get_label('both', 'Both') }}</button>
                                <button type="button" class="tk-seg-btn" role="radio" aria-checked="false"
                                    data-value="income"><span class="tk-seg-dot" style="background: var(--signal)"></span>{{ get_label('income', 'Income') }}</button>
                                <button type="button" class="tk-seg-btn" role="radio" aria-checked="false"
                                    data-value="expense"><span class="tk-seg-dot" style="background: var(--fg-2)"></span>{{ get_label('expenses', 'Expenses') }}</button>
                            </div>
                        </div>
                        <div class="tk-card-body">
                            <div id="tk-hero-chart" class="tk-area-chart"
                                data-label-income="{{ get_label('income', 'Income') }}"
                                data-label-expense="{{ get_label('expenses', 'Expenses') }}"
                                data-empty-label="{{ get_label('no_data_available', 'No data available') }}"></div>
                        </div>
                    </div>
                @endif
            </div>
            {{-- RIGHT COLUMN: project → task → meetings --}}
            <div class="col-lg-4 d-flex flex-column gap-4">
                @php
                    $tkUpcomingMeetings = $tkAllData 
                        ? ($tkWs ? \App\Models\Meeting::where('workspace_id', $tkWs->id)->where('start_date_time', '>=', now(config('app.timezone')))->orderBy('start_date_time', 'asc')->limit(5)->get() : collect())
                        : $tkUser->meetings()->where('start_date_time', '>=', now(config('app.timezone')))->orderBy('start_date_time', 'asc')->limit(5)->get();
                @endphp
                
                <div class="tk-card flex-grow-0" data-id="tk-upcoming-meetings">
                    <div class="tk-card-head">
                        <div class="tk-card-head-main">
                            <div class="tk-card-eyebrow text-info">{{ get_label('upcoming', 'Upcoming') }}</div>
                            <h3 class="tk-card-title">{{ get_label('meetings', 'Meetings') }}</h3>
                        </div>
                        <a href="{{ url('meetings') }}" class="tk-card-link">{{ get_label('view_all', 'View all') }}</a>
                    </div>
                    <div class="tk-card-body">
                        @if($tkUpcomingMeetings->count() > 0)
                            <ul class="list-group list-group-flush mb-0">
                                @foreach($tkUpcomingMeetings as $meeting)
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <div class="d-flex flex-column">
                                            <a href="{{ url('meetings') }}" class="text-body fw-bold text-decoration-none">{{ $meeting->title }}</a>
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($meeting->start_date_time)->format('M d, H:i') }}</small>
                                        </div>
                                        <span class="badge bg-label-info rounded-pill"><i class="bx bx-calendar"></i></span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="text-center py-3">
                                <p class="text-muted mb-0">{{ get_label('no_upcoming_meetings', 'No upcoming meetings') }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                @php
                    $tkOverdueTasks = $tkAllData 
                        ? ($tkWs ? $tkWs->tasks()->whereNotNull('tasks.due_date')->where('tasks.due_date', '<', now()->startOfDay())->limit(5)->get() : collect())
                        : $tkUser->tasks()->whereNotNull('tasks.due_date')->where('tasks.due_date', '<', now()->startOfDay())->limit(5)->get();
                @endphp
                @if($tkOverdueTasks->count() > 0)
                <div class="tk-card flex-grow-0" data-id="tk-overdue-tasks">
                    <div class="tk-card-head">
                        <div class="tk-card-head-main">
                            <div class="tk-card-eyebrow text-danger">{{ get_label('attention', 'Attention') }}</div>
                            <h3 class="tk-card-title text-danger">{{ get_label('overdue_tasks', 'Overdue Tasks') }}</h3>
                        </div>
                        <a href="{{ url(getUserPreferences('tasks', 'default_view')) }}" class="tk-card-link">{{ get_label('view_all', 'View all') }}</a>
                    </div>
                    <div class="tk-card-body">
                        <ul class="list-group list-group-flush mb-0">
                            @foreach($tkOverdueTasks as $task)
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <a href="{{ url('tasks/information/'.$task->id) }}" class="text-body fw-bold text-decoration-none">{{ $task->title }}</a>
                                    <span class="badge bg-label-danger rounded-pill">{{ \Carbon\Carbon::parse($task->due_date)->format('M d') }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif
            </div>
        </div>


        <x-dashboard.tabs />
        <!-- Dependencies -->
        <script src="{{ asset('assets/js/apexcharts.js') }}"></script>
        <script src="{{ asset('assets/js/Sortable.min.js') }}"></script>
        <script src="{{ asset('assets/js/pages/dashboard.js') }}"></script>
    @else
        <div class="w-100 h-100 d-flex align-items-center justify-content-center">
            <span>{{ get_label('you_must_log_in_or_register', 'You must') }} <a href="{{ url('login') }}">{{ get_label('log_in', 'Log in') }}</a> {{ get_label('or', 'or') }} <a href="{{ url('register') }}">{{ get_label('register', 'Register') }}</a> {{ get_label('to_access', 'to access') }} {{ $general_settings['company_title'] }}!</span>
        </div>
    @endauth
@endsection
