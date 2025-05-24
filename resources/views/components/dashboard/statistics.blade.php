@php
    $auth_user = auth()->user();
    function getStatusCounts($statuses, $auth_user, $type = 'projects')
    {
        $statusCounts = [];
        $totalCount = 0;
        foreach ($statuses as $status) {
            $count = isAdminOrHasAllDataAccess()
                ? count($status->$type)
                : $auth_user->{"status_{$type}"}($status->id)->count();
            $statusCounts[$status->id] = $count;
            $totalCount += $count;
        }
        arsort($statusCounts); // Sort by count descending
        return [$statusCounts, $totalCount];
    }
@endphp
<div id="dashboard-items" class="row">
    @if ($auth_user->can('manage_projects'))
        <div class="col-md-4 col-sm-12 draggable-item" data-id="project-statistics">
            <x-dashboard.card :title="get_label('project_statistics', 'Project statistics')" chart-id="projectStatisticsChart">
                @php [$projectStatusCounts, $totalProjects] = getStatusCounts($statuses, $auth_user, 'projects') @endphp
                <x-dashboard.status-list :statusCounts="$projectStatusCounts" :statuses="$statuses" :totalCount="$totalProjects" type="projects" />
            </x-dashboard.card>
        </div>
    @endif
    @if ($auth_user->can('manage_tasks'))
        <div class="col-md-4 col-sm-12 draggable-item" data-id="task-statistics">
            <x-dashboard.card :title="get_label('task_statistics', 'Task statistics')" chart-id="taskStatisticsChart">
                @php [$taskStatusCounts, $totalTasks] = getStatusCounts($statuses, $auth_user, 'tasks') @endphp
                <x-dashboard.status-list :statusCounts="$taskStatusCounts" :statuses="$statuses" :totalCount="$totalTasks" type="tasks" />
            </x-dashboard.card>
        </div>
    @endif
    <div class="col-md-4 col-sm-12 draggable-item" data-id="todos-overview">
        <x-dashboard.card :title="get_label('todos_overview', 'Todos overview')" chart-id="todoStatisticsChart">
            <div class="d-flex justify-content-between mb-3">
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#create_todo_modal">
                    <i class='bx bx-plus'></i> {{ get_label('create_todo', 'Create Todo') }}
                </button>
                <a href="{{ url('todos') }}" class="btn btn-sm btn-primary">
                    <i class="bx bx-list-ul"></i> {{ get_label('view_more', 'View more') }}
                </a>
            </div>
            <x-dashboard.todo-list :todos="$todos" />
        </x-dashboard.card>
    </div>
    @if ($auth_user->hasRole('admin'))
        <div class="col-md-6 draggable-item" data-id="income-vs-expense">
            <x-dashboard.card :title="get_label('income_vs_expense', 'Income vs Expense')">
                <input type="text" id="filter_date_range_income_expense" class="form-control mb-3"
                    placeholder="{{ get_label('date_between', 'Date Between') }}" autocomplete="off">
                <div id="income-expense-chart"></div>
            </x-dashboard.card>
        </div>
        <div class="col-md-6 draggable-item" data-id="recent-activities">
            @php
                $cardId = 'recent-activity';
            @endphp
            <x-dashboard.card :title="get_label('recent_activities', 'Recent Activities')" icon="bx bx-bar-chart-alt-2" :cardId="$cardId">
                <x-dashboard.timeline :activities="$activities" />
            </x-dashboard.card>
        </div>
    @endif
</div>
