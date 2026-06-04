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
        <div class="tk-welcome">
            <div class="tk-welcome-main">
                <div class="tk-welcome-eyebrow">{{ now()->format('H:i') }} · {{ now()->translatedFormat('D d M') }} · {{ get_label('wk', 'WK') }} {{ now()->weekOfYear }}</div>
                <h1 class="tk-welcome-title">{{ get_label('welcome_back', 'Welcome back') }}, {{ $tkUser->first_name }}.</h1>
                <p class="tk-welcome-sub">
                    <span class="tk-welcome-stat {{ $tkDeadlines > 0 ? 'tk-stat-warn' : '' }}">{{ $tkDeadlines }}</span>
                    {{ $tkDeadlines == 1 ? get_label('deadline_this_week', 'deadline this week') : get_label('deadlines_this_week', 'deadlines this week') }}
                    <span class="tk-welcome-dot">·</span>
                    <span class="tk-welcome-stat">{{ $tkTotalTasks }}</span>
                    {{ $tkTotalTasks == 1 ? get_label('task', 'task') : get_label('tasks', 'tasks') }}
                </p>
            </div>
        </div>

        <!-- Filter Card -->
        <!-- <div class="card mb-4 mt-4 shadow-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-9">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="daterange" class="form-label fw-semibold">
                                    {{ get_label('select_period', 'Select Period') }}
                                    <i class="bx bx-info-circle text-muted ms-1" data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        title="{{ get_label('date_range_filter', 'Select a date range to filter data. Projects and tasks are included if their start/end dates overlap with the range or are undefined (ongoing). Other data (clients, meetings, todos, activities) is filtered by creation date. Defaults to the last 7 days.') }}"></i>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bx bx-calendar"></i>
                                    </span>
                                    <input type="text" id="daterange" name="daterange" class="form-control" readonly
                                        placeholder="{{ get_label('select_date_range', 'Select date range') }}..."
                                        data-bs-toggle="tooltip" data-bs-placement="bottom"
                                        title="{{ get_label('date_picker', 'Click to select a custom date range for filtering dashboard data.') }}">
                                    <input type="hidden" id="filter_date_range_from" name="start_date">
                                    <input type="hidden" id="filter_date_range_to" name="end_date">
                                </div>
                                <div class="form-text">
                                    <i class="bx bx-time-five me-1"></i>
                                    {{ get_label('quick_select', 'Quick select') }}:
                                    <span class="badge bg-light text-dark quick-date-btn ms-1 cursor-pointer"
                                        data-range="today" data-bs-toggle="tooltip"
                                        title="{{ get_label('today_filter', 'Show data for today only, based on creation dates or overlapping project/task dates.') }}">{{ get_label('today', 'Today') }}</span>
                                    <span class="badge bg-light text-dark quick-date-btn ms-1 cursor-pointer"
                                        data-range="yesterday" data-bs-toggle="tooltip"
                                        title="{{ get_label('yesterday_filter', 'Show data for yesterday only, based on creation dates or overlapping project/task dates.') }}">{{ get_label('yesterday', 'Yesterday') }}</span>
                                    <span class="badge bg-light text-dark quick-date-btn ms-1 cursor-pointer"
                                        data-range="last7days" data-bs-toggle="tooltip"
                                        title="{{ get_label('last_7_days_filter', 'Show data for the last 7 days, including projects/tasks active during this period or without defined dates.') }}">{{ get_label('last_7_days', 'Last 7 days') }}</span>
                                    <span class="badge bg-light text-dark quick-date-btn ms-1 cursor-pointer"
                                        data-range="last30days" data-bs-toggle="tooltip"
                                        title="{{ get_label('last_30_days_filter', 'Show data for the last 30 days, including projects/tasks active during this period or without defined dates.') }}">{{ get_label('last_30_days', 'Last 30 days') }}</span>
                                    <span class="badge bg-light text-dark quick-date-btn ms-1 cursor-pointer"
                                        data-range="thismonth" data-bs-toggle="tooltip"
                                        title="{{ get_label('current_month_filter', 'Show data for the current month, including projects/tasks active during this period or without defined dates.') }}">{{ get_label('current_month', 'Current Month') }}</span>
                                </div>
                            </div>
                            @if (isAdminOrHasAllDataAccess())
                                <div class="col-md-6">
                                    <label for="userFilter" class="form-label fw-semibold">
                                        {{ get_label('select_team_members', 'Select Team Member(s)') }}
                                        <i class="bx bx-info-circle text-muted ms-1" data-bs-toggle="tooltip"
                                            data-bs-placement="top"
                                            title="{{ get_label('user_filter', 'Filter todos and activities by specific team members (based on creator_id or actor_id). Leave empty to include all team members in the workspace.') }}"></i>
                                    </label>
                                    <select id="userFilter" name="user_ids[]"
                                        class="form-select js-example-basic-multiple users_select" multiple
                                        data-bs-toggle="tooltip" data-bs-placement="bottom"
                                        title="{{ get_label('user_select', 'Start typing to select team members for filtering dashboard data.') }}">
                                        <option value="">
                                            {{ get_label('loading_team_members', 'Loading team members...') }}</option>
                                    </select>
                                    <div class="form-text">
                                        <span class="ms-2">
                                            <a class="text-decoration-none small clear-user-selection-btn"
                                                href="javascript:void(0)">{{ get_label('clear', 'Clear') }}</a>
                                        </span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-3 d-none d-md-block text-center">
                        <img src="{{ asset('storage/Work_3.jpg') }}" alt="Dashboard illustration"
                            class="h-px-250 img-fluid">
                    </div>
                </div>
            </div>
        </div> -->
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
            $filteredTiles = array_slice($filteredTiles, 0, 4);
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
            {{-- RIGHT COLUMN: project → task → todo --}}
            <div class="col-lg-4 d-flex flex-column gap-4">
                @if ($auth_user->can('manage_projects'))
                    <div class="tk-card flex-grow-1" data-id="tk-project-chart">
                        <div class="tk-card-head">
                            <div class="tk-card-head-main">
                                <div class="tk-card-eyebrow">{{ get_label('projects', 'Projects') }}</div>
                                <h3 class="tk-card-title"><span id="tk-project-total">0</span>
                                    <span class="tk-card-title-sub">{{ get_label('total', 'total') }}</span>
                                </h3>
                            </div>
                        </div>
                        <div class="tk-card-body">
                            <div class="tk-donut-wrap">
                                <div id="tk-project-donut" class="tk-donut" data-kind="project"
                                    data-center-label="{{ get_label('projects', 'PROJECTS') }}"></div>
                                <div id="tk-project-legend" class="tk-donut-legend"></div>
                            </div>
                        </div>
                    </div>
                @endif
                @if ($auth_user->can('manage_tasks'))
                    <div class="tk-card flex-grow-1" data-id="tk-task-chart">
                        <div class="tk-card-head">
                            <div class="tk-card-head-main">
                                <div class="tk-card-eyebrow">{{ get_label('tasks', 'Tasks') }}</div>
                                <h3 class="tk-card-title"><span id="tk-task-total">0</span>
                                    <span class="tk-card-title-sub">{{ get_label('total', 'total') }}</span>
                                </h3>
                            </div>
                        </div>
                        <div class="tk-card-body">
                            <div class="tk-donut-wrap">
                                <div id="tk-task-donut" class="tk-donut" data-kind="task"
                                    data-center-label="{{ get_label('tasks', 'TASKS') }}"></div>
                                <div id="tk-task-legend" class="tk-donut-legend"></div>
                            </div>
                        </div>
                    </div>
                @endif
                <div class="tk-card flex-grow-1" data-id="tk-todo-chart">
                    <div class="tk-card-head">
                        <div class="tk-card-head-main">
                            <div class="tk-card-eyebrow">{{ get_label('todos', 'Todos') }}</div>
                            <h3 class="tk-card-title"><span id="tk-todo-total">0</span>
                                <span class="tk-card-title-sub">{{ get_label('total', 'total') }}</span>
                            </h3>
                        </div>
                    </div>
                    <div class="tk-card-body">
                        <div class="tk-donut-wrap">
                            <div id="tk-todo-donut" class="tk-donut" data-kind="todo"
                                data-center-label="{{ get_label('todos', 'TODOS') }}"
                                data-label-done="{{ get_label('completed', 'Completed') }}"
                                data-label-pending="{{ get_label('pending', 'Pending') }}"></div>
                            <div id="tk-todo-legend" class="tk-donut-legend"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <x-dashboard.statistics :statuses="[]" :todos="[]" :activities="[]" />

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
