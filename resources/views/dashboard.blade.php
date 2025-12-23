@extends('layout')
@section('title')
    <?= get_label('dashboard', 'Dashboard') ?>
@endsection
@section('content')
    @authBoth
    <div class="container-fluid">
        <!-- Filter Card -->
        <div class="card mb-4 mt-4 shadow-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-9">
                        <h3 class="fw-bold mb-1">
                            <i class="bx bx-bar-chart-alt-2 me-2"></i>
                            {{ get_label('analytics_dashboard', 'Analytics Dashboard') }}
                        </h3>
                        <p class="text-muted small mb-3">
                            {{ get_label('monitor_projects_and_tasks_insights', 'Monitor Projects ,Tasks and more insights.') }}
                            <i class="bx bx-help-circle ms-1" data-bs-toggle="tooltip" data-bs-placement="right"
                                title="{{ get_label('dashboard_insights', 'This dashboard displays counts and trends for projects, tasks, todos, and activities, filtered by date range and team members (for admins). Data reflects active and ongoing records, including those without specific start or end dates.') }}"></i>
                        </p>
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
            $filteredTiles = array_slice($filteredTiles, 0, 4);
        @endphp
        <div class="col-lg-12 col-md-12 order-1">
            <div class="row mt-4">
                @foreach ($filteredTiles as $tile)
                    <x-dashboard.tile id="{{ $tile['id'] }}" label="{{ $tile['label'] }}" count="{{ $tile['count'] }}"
                        url="{{ $tile['url'] }}" linkColor="{{ $tile['link_color'] }}" icon="{{ $tile['icon'] }}"
                        iconBg="{{ $tile['icon-bg'] }}" customCardClass="{{ $tile['custom-card-class'] }}"
                        extraAttributes="data-id='{{ $tile['id'] }}' class='draggable-item'" />
                @endforeach
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
