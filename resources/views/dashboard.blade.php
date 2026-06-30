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
        <div class="tk-card mt-4">
            <div class="p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    @php
                        $dashboardNow = now()->tz(config('app.timezone'));
                    @endphp
                    <div class="tk-eyebrow mb-1">{{ $dashboardNow->format('H:i') }} · {{ $dashboardNow->translatedFormat('D d M') }} · {{ get_label('wk', 'WK') }} {{ $dashboardNow->weekOfYear }}</div>
                    <h3 class="tk-fg-0 fw-bold mb-2">{{ get_label('welcome_back', 'Welcome back') }}, {{ $tkUser->first_name }}.</h3>
                    <p class="tk-muted mb-0">
                        <span class="fw-bold {{ $tkDeadlines > 0 ? 'text-danger' : 'tk-fg-0' }}">{{ $tkDeadlines }}</span>
                        {{ $tkDeadlines == 1 ? get_label('deadline_this_week', 'deadline this week') : get_label('deadlines_this_week', 'deadlines this week') }}
                        <span class="mx-2">·</span>
                        <span class="fw-bold tk-fg-0">{{ $tkTotalTasks }}</span>
                        {{ $tkTotalTasks == 1 ? get_label('task', 'task') : get_label('tasks', 'tasks') }}
                    </p>
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
            <div class="col-12 col-lg-8 d-flex flex-column gap-4">
                @if ($auth_user->can('manage_projects') || $auth_user->can('manage_tasks'))
                    <div class="tk-card flex-grow-0" data-id="tk-combined-chart">
                        <div class="tk-card-head">
                            <div class="tk-card-head-main">
                                <div class="tk-card-eyebrow">{{ get_label('overview', 'Overview') }}</div>
                                <h3 class="tk-card-title">{{ get_label('projects_and_tasks', 'Projects & Tasks') }}</h3>
                            </div>
                        </div>
                        <div class="tk-card-body p-2">
                            <div id="tk-combined-bar-chart">
                                <div class="skel-chart-container tk-loading-skeleton">
                                    <div class="skel skel-bar-1"></div>
                                    <div class="skel skel-bar-2"></div>
                                    <div class="skel skel-bar-3"></div>
                                    <div class="skel skel-bar-4"></div>
                                    <div class="skel skel-bar-5"></div>
                                    <div class="skel skel-bar-6"></div>
                                    <div class="skel skel-bar-7"></div>
                                    <div class="skel skel-bar-8"></div>
                                </div>
                            </div>
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
                            data-empty-label="{{ get_label('no_activities', 'No recent activities') }}">
                            <div class="tk-loading-skeleton">
                                <div class="skel-row">
                                    <span class="skel skel-circle skel-avatar"></span>
                                    <div class="skel-text-wrap">
                                        <span class="skel skel-title skel-w-50"></span>
                                        <span class="skel skel-line skel-w-30"></span>
                                    </div>
                                </div>
                                <div class="skel-row">
                                    <span class="skel skel-circle skel-avatar"></span>
                                    <div class="skel-text-wrap">
                                        <span class="skel skel-title skel-w-65"></span>
                                        <span class="skel skel-line skel-w-40"></span>
                                    </div>
                                </div>
                                <div class="skel-row">
                                    <span class="skel skel-circle skel-avatar"></span>
                                    <div class="skel-text-wrap">
                                        <span class="skel skel-title skel-w-45"></span>
                                        <span class="skel skel-line skel-w-25"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
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
                                data-empty-label="{{ get_label('no_data_available', 'No data available') }}">
                                <div class="skel-chart-container tk-loading-skeleton">
                                    <div class="skel skel-bar-1"></div>
                                    <div class="skel skel-bar-2"></div>
                                    <div class="skel skel-bar-3"></div>
                                    <div class="skel skel-bar-4"></div>
                                    <div class="skel skel-bar-5"></div>
                                    <div class="skel skel-bar-6"></div>
                                    <div class="skel skel-bar-7"></div>
                                    <div class="skel skel-bar-8"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            {{-- RIGHT COLUMN: project → task → meetings --}}
            <div class="col-12 col-lg-4 d-flex flex-column gap-4">
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
                    <div class="tk-card-body p-2 d-flex flex-column gap-2">
                        @if($tkUpcomingMeetings->count() > 0)
                            @foreach($tkUpcomingMeetings as $meeting)
                                @php
                                    $startTime = \Carbon\Carbon::parse($meeting->start_date_time)->tz(config('app.timezone'));
                                    $endTime = \Carbon\Carbon::parse($meeting->end_date_time)->tz(config('app.timezone'));
                                    $durationMins = (int) abs($startTime->diffInMinutes($endTime, false));
                                    $durationStr = $durationMins >= 60 ? floor($durationMins / 60) . 'h' . ($durationMins % 60 > 0 ? ' ' . ($durationMins % 60) . 'm' : '') : $durationMins . 'm';
                                @endphp
                                <div class="d-flex align-items-center justify-content-between p-2 border rounded" style="transition: box-shadow 0.2s; box-shadow: 0 2px 6px 0 rgba(67, 89, 113, 0.08);">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar avatar-sm flex-shrink-0">
                                            <span class="avatar-initial rounded bg-label-info"><i class="bx bx-shape-polygon fs-6"></i></span>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <a href="{{ url('meetings') }}" class="text-body fw-bold" style="font-size: 0.85rem; line-height: 1.2;">{{ $meeting->title }}</a>
                                            <div class="text-muted mt-1" style="font-size: 0.75rem;">
                                                <span><i class='bx bx-time-five'></i> {{ $startTime->format('H:i') }} ({{ $durationStr }})</span>
                                                <span class="ms-2"><i class='bx bx-calendar'></i> {{ $startTime->format('M d, Y') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column align-items-end ms-2">
                                        <span class="badge bg-label-info" style="font-size: 0.7rem;">{{ $startTime->diffForHumans() }}</span>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="empty">
                                <div class="empty-icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 9h18M8 3v4M16 3v4"/></svg>
                                </div>
                                <div class="empty-title">{{ get_label('no_upcoming_meetings', 'No upcoming meetings') }}</div>
                                <div class="empty-sub">{{ get_label('no_meetings_scheduled_yet', 'No meetings scheduled yet') }}.</div>
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
                    <div class="tk-card-body p-2 d-flex flex-column gap-2">
                        @foreach($tkOverdueTasks as $task)
                            @php
                                $dueDate = \Carbon\Carbon::parse($task->due_date)->tz(config('app.timezone'));
                                $overdueStr = $dueDate->diffForHumans();
                            @endphp
                            <div class="d-flex align-items-center justify-content-between p-2 border rounded" style="transition: box-shadow 0.2s; box-shadow: 0 2px 6px 0 rgba(67, 89, 113, 0.08);">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm flex-shrink-0">
                                        <span class="avatar-initial rounded bg-label-danger"><i class="bx bx-task fs-6"></i></span>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <a href="{{ url('tasks/information/'.$task->id) }}" class="text-body fw-bold" style="font-size: 0.85rem; line-height: 1.2;">{{ $task->title }}</a>
                                        <div class="text-muted mt-1" style="font-size: 0.75rem;">
                                            @if($task->project)
                                                <span><i class='bx bx-briefcase-alt-2'></i> {{ $task->project->title }}</span>
                                                <span class="mx-1">|</span>
                                            @endif
                                            <span><i class='bx bx-calendar'></i> {{ $dueDate->format('M d, Y') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex flex-column align-items-end ms-2">
                                    <span class="badge bg-label-danger" style="font-size: 0.7rem;">{{ $overdueStr }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                @php
                    $tkDueInvoices = $tkAllData 
                        ? ($tkWs ? \App\Models\EstimatesInvoice::where('workspace_id', $tkWs->id)->where('type', 'invoice')->whereIn('status', ['due', 'partially_paid', 'draft'])->whereNotNull('to_date')->orderBy('to_date', 'asc')->limit(5)->get() : collect())
                        : $tkUser->estimates_invoices()->where('type', 'invoice')->whereIn('status', ['due', 'partially_paid', 'draft'])->whereNotNull('to_date')->orderBy('to_date', 'asc')->limit(5)->get();
                @endphp
                @if($tkDueInvoices->count() > 0)
                <div class="tk-card flex-grow-0" data-id="tk-due-invoices">
                    <div class="tk-card-head">
                        <div class="tk-card-head-main">
                            <div class="tk-card-eyebrow text-warning">{{ get_label('action_needed', 'Action Needed') }}</div>
                            <h3 class="tk-card-title">{{ get_label('due_invoices', 'Due Invoices') }}</h3>
                        </div>
                        <a href="{{ url('estimates-invoices') }}" class="tk-card-link">{{ get_label('view_all', 'View all') }}</a>
                    </div>
                    <div class="tk-card-body p-2 d-flex flex-column gap-2">
                        @foreach($tkDueInvoices as $invoice)
                            @php
                                $dueDate = \Carbon\Carbon::parse($invoice->to_date)->tz(config('app.timezone'));
                                $overdueStr = $dueDate->diffForHumans();
                                $isOverdue = $dueDate->isPast();
                            @endphp
                            <div class="d-flex align-items-center justify-content-between p-2 border rounded" style="transition: box-shadow 0.2s; box-shadow: 0 2px 6px 0 rgba(67, 89, 113, 0.08);">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm flex-shrink-0">
                                        <span class="avatar-initial rounded bg-label-{{ $isOverdue ? 'danger' : 'warning' }}"><i class="bx bx-receipt fs-6"></i></span>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <a href="{{ url('estimates-invoices/view/'.$invoice->id) }}" class="text-body fw-bold" style="font-size: 0.85rem; line-height: 1.2;">{{ $invoice->name ?: get_label('invoice', 'Invoice') . ' #' . $invoice->id }}</a>
                                        <div class="text-muted mt-1" style="font-size: 0.75rem;">
                                            @if($invoice->client)
                                                <span><i class='bx bx-user'></i> {{ $invoice->client->first_name }} {{ $invoice->client->last_name }}</span>
                                                <span class="mx-1">|</span>
                                            @endif
                                            <span><i class='bx bx-calendar'></i> {{ $dueDate->format('M d, Y') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex flex-column align-items-end gap-1 ms-2">
                                    <span class="fw-bold text-dark" style="font-size: 0.95rem;">{{ format_currency($invoice->final_total) }}</span>
                                    <span class="badge bg-label-{{ $isOverdue ? 'danger' : 'warning' }}" style="font-size: 0.7rem;">{{ $overdueStr }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="tk-card flex-grow-1" id="todos-overview" data-id="todos-overview">
                    <div class="tk-card-head">
                        <div class="tk-card-head-main">
                            <div class="tk-card-eyebrow text-info">{{ get_label('todos', 'Todos') }}</div>
                            <h3 class="tk-card-title">{{ get_label('todos_overview', 'Todos overview') }}</h3>
                        </div>
                        <div class="d-flex gap-3 align-items-center mt-1">
                            <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#create_todo_modal" class="tk-card-link text-primary d-flex align-items-center" style="line-height: 1;" title="{{ get_label('create_todo', 'Create Todo') }}">
                                <i class='bx bx-plus' style="font-size: 1.1rem; margin-right: 2px;"></i>{{ get_label('add', 'Add') }}
                            </a>
                            <a href="{{ url('todos') }}" class="tk-card-link d-flex align-items-center" style="line-height: 1;">{{ get_label('view_more', 'View more') }}</a>
                        </div>
                    </div>
                    <div class="px-3 pt-2 border-bottom">
                        <ul class="nav nav-tabs nav-tabs-custom tk-custom-tabs" role="tablist" style="margin-bottom: -1px;">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#todos-pending" type="button" role="tab" aria-selected="true" style="font-size: 0.85rem; font-weight: 500; padding: 0.5rem 1rem;">
                                    {{ get_label('pending', 'Pending') }}
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#todos-completed" type="button" role="tab" aria-selected="false" style="font-size: 0.85rem; font-weight: 500; padding: 0.5rem 1rem;">
                                    {{ get_label('completed', 'Completed') }}
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="tk-card-body p-0 d-flex flex-column" style="height: 300px;">
                        <div style="flex: 1 1 auto; overflow-y: auto; min-height: 0;">
                            <div class="tab-content p-0 m-0">
                                <div class="tab-pane fade show active" id="todos-pending" role="tabpanel">
                                    <ul class="p-0 m-0 todo-list todo-list-pending list-group list-group-flush">
                                <div class="tk-loading-skeleton">
                                    <div class="skel-row">
                                        <span class="skel skel-check-icon"></span>
                                        <div class="skel-text-wrap">
                                            <span class="skel skel-line skel-w-70"></span>
                                            <span class="skel skel-line skel-w-40"></span>
                                        </div>
                                    </div>
                                    <div class="skel-row">
                                        <span class="skel skel-check-icon"></span>
                                        <div class="skel-text-wrap">
                                            <span class="skel skel-line skel-w-60"></span>
                                            <span class="skel skel-line skel-w-30"></span>
                                        </div>
                                    </div>
                                    <div class="skel-row">
                                        <span class="skel skel-check-icon"></span>
                                        <div class="skel-text-wrap">
                                            <span class="skel skel-line skel-w-80"></span>
                                            <span class="skel skel-line skel-w-50"></span>
                                        </div>
                                    </div>
                                </div>
                                    </ul>
                                </div>
                                <div class="tab-pane fade" id="todos-completed" role="tabpanel">
                                    <ul class="p-0 m-0 todo-list todo-list-completed list-group list-group-flush">
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <x-dashboard.tabs />
        <!-- Dependencies -->
        <script src="{{ asset('assets/js/apexcharts.js') }}"></script>
        <script src="{{ asset('assets/js/Sortable.min.js') }}"></script>
        <script src="{{ asset('assets/js/pages/dashboard.js') }}?v={{ time() }}"></script>
    @else
        <div class="w-100 h-100 d-flex align-items-center justify-content-center">
            <span>{{ get_label('you_must_log_in_or_register', 'You must') }} <a href="{{ url('login') }}">{{ get_label('log_in', 'Log in') }}</a> {{ get_label('or', 'or') }} <a href="{{ url('register') }}">{{ get_label('register', 'Register') }}</a> {{ get_label('to_access', 'to access') }} {{ $general_settings['company_title'] }}!</span>
        </div>
    @endauth
@endsection
