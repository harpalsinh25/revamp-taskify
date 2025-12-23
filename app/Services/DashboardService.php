<?php

namespace App\Services;

use App\Models\Status;
use App\Models\User;
use App\Models\Workspace;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DashboardService
{
    /**
     * Build dashboard data for the given workspace, user and filters.
     *
     * This method does not perform any HTTP or logging side effects – it only
     * computes the data structure returned by the original controller method.
     *
     * @param  Workspace|null  $workspace
     * @param  User|null  $user
     * @param  array{start_date?:string|null,end_date?:string|null,user_ids?:array<int,int>}  $filters
     * @return array<string,mixed>
     */
    public function getDashboardData(?Workspace $workspace, ?User $user, array $filters = []): array
    {
        $startDateInput = $filters['start_date'] ?? null;
        $endDateInput = $filters['end_date'] ?? null;
        $userIds = $filters['user_ids'] ?? [];

        // Use Carbon to set defaults if inputs are null or invalid
        $startDate = $startDateInput && Carbon::hasFormat($startDateInput, 'Y-m-d')
            ? $startDateInput
            : Carbon::now()->subDays(6)->format('Y-m-d');

        $endDate = $endDateInput && Carbon::hasFormat($endDateInput, 'Y-m-d')
            ? $endDateInput
            : Carbon::now()->format('Y-m-d');

        // Validate date range (preserve original behaviour of throwing on invalid range)
        if (Carbon::parse($startDate)->gt(Carbon::parse($endDate))) {
            throw new \InvalidArgumentException('start_date cannot be after end_date.');
        }

        $dateRangeWithTime = [$startDate . ' 00:00:00', $endDate . ' 23:59:59'];

        [
            $projectsCount,
            $tasksCount,
            $usersCount,
            $clientsCount,
            $meetingsCount
        ] = $this->buildTileCounts($workspace, $user, $dateRangeWithTime, $userIds, $startDate, $endDate);

        $todosCount = $this->countTodos($workspace, $dateRangeWithTime, $userIds);
        $todos = $this->buildTodos($user, $dateRangeWithTime, $userIds);
        $activities = $this->buildActivities($workspace, $dateRangeWithTime, $userIds);

        [
            $projectData,
            $taskData,
            $projectStatusCounts,
            $taskStatusCounts,
            $labels,
            $bgColors,
            $statuses
        ] = $this->buildStatusChartData($workspace, $user, $userIds, $startDate, $endDate);

        $todoData = $this->buildTodoStatusData($workspace, $dateRangeWithTime, $userIds);

        return [
            'projects_count' => $projectsCount,
            'tasks_count' => $tasksCount,
            'users_count' => $usersCount,
            'clients_count' => $clientsCount,
            'meetings_count' => $meetingsCount,
            'todos_count' => $todosCount,
            'project_data' => $projectData,
            'task_data' => $taskData,
            'todo_data' => $todoData,
            'labels' => $labels,
            'bg_colors' => $bgColors,
            'todos' => $todos,
            'activities' => $activities,
            'statuses' => $statuses->map(
                static fn ($status) => [
                    'id' => $status->id,
                    'title' => $status->title,
                    'color' => $status->color,
                ]
            ),
            'project_status_counts' => $projectStatusCounts,
            'task_status_counts' => $taskStatusCounts,
            'total_projects' => array_sum($projectData),
            'total_tasks' => array_sum($taskData),
        ];
    }

    /**
     * Build count tiles (projects, tasks, users, clients, meetings).
     *
     * @param  Workspace|null  $workspace
     * @param  User|null  $user
     * @param  array{0:string,1:string}  $dateRangeWithTime
     * @param  array<int,int>  $userIds
     * @param  string  $startDate
     * @param  string  $endDate
     * @return array<int,int>
     */
    protected function buildTileCounts(
        ?Workspace $workspace,
        ?User $user,
        array $dateRangeWithTime,
        array $userIds,
        string $startDate,
        string $endDate
    ): array {
        $projectsCount = 0;
        $tasksCount = 0;
        $usersCount = 0;
        $clientsCount = 0;
        $meetingsCount = 0;

        if ($workspace) {
            // Define overlap queries for projects and tasks
            $projectOverlapQuery = static function ($query) use ($startDate, $endDate) {
                $query->where(function ($q) use ($endDate) {
                    $q->whereNull('start_date')
                        ->orWhere('start_date', '<=', $endDate . ' 23:59:59');
                })->where(function ($q) use ($startDate) {
                    $q->whereNull('end_date')
                        ->orWhere('end_date', '>=', $startDate . ' 00:00:00');
                });
            };

            $taskOverlapQuery = static function ($query) use ($startDate, $endDate) {
                $query->where(function ($q) use ($endDate) {
                    $q->whereNull('start_date')
                        ->orWhere('start_date', '<=', $endDate . ' 23:59:59');
                })->where(function ($q) use ($startDate) {
                    $q->whereNull('due_date')
                        ->orWhere('due_date', '>=', $startDate . ' 00:00:00');
                });
            };

            $projectsQuery = $workspace->projects()->where($projectOverlapQuery);
            $tasksQuery = $workspace->tasks()->where($taskOverlapQuery);
            $meetingsQuery = $workspace->meetings()->whereBetween('created_at', $dateRangeWithTime);

            if (!isAdminOrHasAllDataAccess()) {
                // For non-admins, filter by user-specific relationships
                $projectsQuery = $user && method_exists($user, 'projects')
                    ? $user->projects()->where($projectOverlapQuery)
                    : $workspace->projects()->whereRaw('1=0');

                $tasksQuery = $user && method_exists($user, 'tasks')
                    ? $user->tasks()->where($taskOverlapQuery)
                    : $workspace->tasks()->whereRaw('1=0');

                $meetingsQuery = $user && method_exists($user, 'meetings')
                    ? $user->meetings()->whereBetween('created_at', $dateRangeWithTime)
                    : $workspace->meetings()->whereRaw('1=0');
            }

            if (!empty($userIds)) {
                $projectsQuery->whereHas('users', static fn ($q) => $q->whereIn('users.id', $userIds));
                $tasksQuery->whereHas('users', static fn ($q) => $q->whereIn('users.id', $userIds));
                $meetingsQuery->whereHas('users', static fn ($q) => $q->whereIn('users.id', $userIds));
            }

            $projectsCount = $projectsQuery->count();
            $tasksCount = $tasksQuery->count();
            $usersCount = $workspace->users()
                ->when($userIds, static fn ($query) => $query->whereIn('users.id', $userIds))
                ->count();
            $clientsCount = $workspace->clients()
                ->whereBetween('created_at', $dateRangeWithTime)
                ->count();
            $meetingsCount = $meetingsQuery->count();
        }

        return [
            $projectsCount,
            $tasksCount,
            $usersCount,
            $clientsCount,
            $meetingsCount,
        ];
    }

    /**
     * Count todos for the given workspace and filters.
     *
     * @param  Workspace|null  $workspace
     * @param  array{0:string,1:string}  $dateRangeWithTime
     * @param  array<int,int>  $userIds
     * @return int
     */
    protected function countTodos(?Workspace $workspace, array $dateRangeWithTime, array $userIds): int
    {
        if (!$workspace || !method_exists($workspace, 'todos')) {
            return 0;
        }

        return $workspace->todos()
            ->whereBetween('created_at', $dateRangeWithTime)
            ->when($userIds, static fn ($query) => $query->whereIn('creator_id', $userIds))
            ->count();
    }

    /**
     * Build todos list collection for the authenticated user.
     *
     * @param  User|null  $user
     * @param  array{0:string,1:string}  $dateRangeWithTime
     * @param  array<int,int>  $userIds
     * @return \Illuminate\Support\Collection<int,array<string,mixed>>
     */
    protected function buildTodos(?User $user, array $dateRangeWithTime, array $userIds): Collection
    {
        if (!$user || !method_exists($user, 'todos')) {
            return collect();
        }

        return $user->todos()
            ->whereBetween('created_at', $dateRangeWithTime)
            ->when($userIds, static fn ($query) => $query->whereIn('creator_id', $userIds))
            ->orderBy('is_completed', 'asc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(static function ($todo) {
                return [
                    'id' => $todo->id,
                    'title' => ucfirst($todo->title),
                    'is_completed' => $todo->is_completed,
                    'created_at' => format_date($todo->created_at, true),
                ];
            });
    }

    /**
     * Build recent activities collection for the workspace.
     *
     * @param  Workspace|null  $workspace
     * @param  array{0:string,1:string}  $dateRangeWithTime
     * @param  array<int,int>  $userIds
     * @return \Illuminate\Support\Collection<int,array<string,mixed>>
     */
    protected function buildActivities(?Workspace $workspace, array $dateRangeWithTime, array $userIds): Collection
    {
        if (!$workspace || !method_exists($workspace, 'activity_logs')) {
            return collect();
        }

        return $workspace->activity_logs()
            ->whereBetween('created_at', $dateRangeWithTime)
            ->when($userIds, static fn ($query) => $query->whereIn('actor_id', $userIds))
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get()
            ->map(static function ($activity) {
                return [
                    'id' => $activity->id,
                    'message' => $activity->message,
                    'activity' => $activity->activity,
                    'created_at' => $activity->created_at->toIso8601String(),
                    'created_at_diff' => $activity->created_at->diffForHumans(),
                    'created_at_formatted' => format_date($activity->created_at, true),
                ];
            });
    }

    /**
     * Build status-wise chart data for projects and tasks.
     *
     * @param  Workspace|null  $workspace
     * @param  User|null  $user
     * @param  array<int,int>  $userIds
     * @param  string  $startDate
     * @param  string  $endDate
     * @return array<int,mixed>
     */
    protected function buildStatusChartData(
        ?Workspace $workspace,
        ?User $user,
        array $userIds,
        string $startDate,
        string $endDate
    ): array {
        $projectData = [];
        $taskData = [];
        $projectStatusCounts = [];
        $taskStatusCounts = [];
        $labels = [];
        $bgColors = [];

        $colorMap = [
            'primary' => '#6777ef',
            'secondary' => '#6c757d',
            'success' => '#63ed7a',
            'danger' => '#fc544b',
            'warning' => '#ffa426',
            'info' => '#00c4b4',
        ];

        $statuses = Status::all();

        if (!$workspace) {
            // Keep behaviour identical: if workspace is null, everything stays empty.
            return [
                $projectData,
                $taskData,
                $projectStatusCounts,
                $taskStatusCounts,
                $labels,
                $bgColors,
                $statuses,
            ];
        }

        $projectOverlapQuery = static function ($query) use ($startDate, $endDate) {
            $query->where(function ($q) use ($endDate) {
                $q->whereNull('start_date')
                    ->orWhere('start_date', '<=', $endDate . ' 23:59:59');
            })->where(function ($q) use ($startDate) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $startDate . ' 00:00:00');
            });
        };

        $taskOverlapQuery = static function ($query) use ($startDate, $endDate) {
            $query->where(function ($q) use ($endDate) {
                $q->whereNull('start_date')
                    ->orWhere('start_date', '<=', $endDate . ' 23:59:59');
            })->where(function ($q) use ($startDate) {
                $q->whereNull('due_date')
                    ->orWhere('due_date', '>=', $startDate . ' 00:00:00');
            });
        };

        foreach ($statuses as $status) {
            $projectStatusQuery = $workspace->projects()
                ->where('status_id', $status->id)
                ->where($projectOverlapQuery);

            $taskStatusQuery = $workspace->tasks()
                ->where('status_id', $status->id)
                ->where($taskOverlapQuery);

            if (!isAdminOrHasAllDataAccess()) {
                $projectStatusQuery = $user && method_exists($user, 'projects')
                    ? $user->projects()->where('status_id', $status->id)->where($projectOverlapQuery)
                    : $workspace->projects()->whereRaw('1=0');

                $taskStatusQuery = $user && method_exists($user, 'tasks')
                    ? $user->tasks()->where('status_id', $status->id)->where($taskOverlapQuery)
                    : $workspace->tasks()->whereRaw('1=0');
            }

            if (!empty($userIds)) {
                $projectStatusQuery->whereHas('users', static fn ($q) => $q->whereIn('users.id', $userIds));
                $taskStatusQuery->whereHas('users', static fn ($q) => $q->whereIn('users.id', $userIds));
            }

            $projectCount = $projectStatusQuery->count();
            $taskCount = $taskStatusQuery->count();

            $projectData[] = $projectCount;
            $taskData[] = $taskCount;
            $projectStatusCounts[$status->id] = $projectCount;
            $taskStatusCounts[$status->id] = $taskCount;
            $labels[] = $status->title;
            $bgColors[] = $colorMap[$status->color] ?? '#64748B';
        }

        return [
            $projectData,
            $taskData,
            $projectStatusCounts,
            $taskStatusCounts,
            $labels,
            $bgColors,
            $statuses,
        ];
    }

    /**
     * Build completed vs pending todo counts for charts.
     *
     * @param  Workspace|null  $workspace
     * @param  array{0:string,1:string}  $dateRangeWithTime
     * @param  array<int,int>  $userIds
     * @return array<int,int>
     */
    protected function buildTodoStatusData(?Workspace $workspace, array $dateRangeWithTime, array $userIds): array
    {
        if (!$workspace || !method_exists($workspace, 'todos')) {
            return [0, 0];
        }

        $completed = $workspace->todos()
            ->whereBetween('created_at', $dateRangeWithTime)
            ->when($userIds, static fn ($query) => $query->whereIn('creator_id', $userIds))
            ->where('is_completed', true)
            ->count();

        $pending = $workspace->todos()
            ->whereBetween('created_at', $dateRangeWithTime)
            ->when($userIds, static fn ($query) => $query->whereIn('creator_id', $userIds))
            ->where('is_completed', false)
            ->count();

        return [$completed, $pending];
    }
}


