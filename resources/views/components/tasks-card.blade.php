<!-- tasks -->
@php
    $flag =
        Request::segment(1) == 'home' ||
        Request::segment(1) == 'users' ||
        Request::segment(1) == 'clients' ||
        (isset($viewAssigned) && $viewAssigned == 1) ||
        (Request::segment(1) == 'projects' && Request::segment(2) == 'information' && Request::segment(3) != null)
            ? 0
            : 1;
    $visibleColumns = getUserPreferences('tasks');
    $auth_user = getAuthenticatedUser();
@endphp
@if ((isset($tasks) && $tasks > 0) || (isset($emptyState) && $emptyState == 0))
    <div class="mt-2">
@endif
{{ $slot }}
@if ((isset($tasks) && $tasks > 0) || (isset($emptyState) && $emptyState == 0))
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end tk-filter-row">
                    <x-advanced-date-filters prefix="task" />
                    @if (getAuthenticatedUser()->can('manage_projects'))
                        <div class="col-md-4">
                            <label class="form-label">{{ get_label('projects', 'Projects') }}</label>
                            <select class="form-control tom_projects_select" id="task_project_filter" multiple="multiple"
                                data-placeholder="<?= get_label('select_projects', 'Select Projects') ?>">
                            </select>
                        </div>
                    @endif
                    @if (isAdminOrHasAllDataAccess() && !isset($viewAssigned))
                        @if (explode('_', $id)[0] != 'client' && explode('_', $id)[0] != 'user')
                            <div class="col-md-4">
                                <label class="form-label">{{ get_label('users', 'Users') }}</label>
                                <select class="form-control tom_users_select" id="task_user_filter" name="user_ids[]"
                                    multiple="multiple" data-placeholder="<?= get_label('select_users', 'Select Users') ?>">
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ get_label('clients', 'Clients') }}</label>
                                <select class="form-control tom_clients_select" id="task_client_filter" name="client_ids[]"
                                    multiple="multiple" data-placeholder="<?= get_label('select_clients', 'Select Clients') ?>">
                                </select>
                            </div>
                        @endif
                    @endif
                    <div class="col-md-4">
                        <label class="form-label">{{ get_label('statuses', 'Statuses') }}</label>
                        <select class="form-control tom_statuses_filter" id="task_status_filter" name="status_ids[]" multiple="multiple"
                            data-placeholder="<?= get_label('select_statuses', 'Select Statuses') ?>">
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ get_label('priorities', 'Priorities') }}</label>
                        <select class="form-control tom_priorities_filter" id="task_priority_filter" name="priority_ids[]"
                            multiple="multiple" data-placeholder="<?= get_label('select_priorities', 'Select Priorities') ?>">
                        </select>
                    </div>
                </div>
            </div>
        </div>
    <input type="hidden" id="is_favorites" value="{{ $favorites ?? '' }}">
    @php
        $columns = [
            ['checkbox' => true],
            ['field' => 'id', 'label' => get_label('id', 'ID'), 'sortable' => true, 'visible' => (in_array('id', $visibleColumns) || empty($visibleColumns))],
            ['field' => 'title', 'label' => get_label('task', 'Task'), 'sortable' => true, 'visible' => (in_array('title', $visibleColumns) || empty($visibleColumns))],
            ['field' => 'project_id', 'label' => get_label('project', 'Project'), 'sortable' => true, 'visible' => (in_array('project_id', $visibleColumns) || empty($visibleColumns))],
            ['field' => 'users', 'label' => get_label('users', 'Users'), 'visible' => (in_array('users', $visibleColumns) || empty($visibleColumns))],
            ['field' => 'clients', 'label' => get_label('clients', 'Clients'), 'visible' => (in_array('clients', $visibleColumns) || empty($visibleColumns))],
            ['field' => 'status_id', 'label' => get_label('status', 'Status'), 'sortable' => true, 'visible' => (in_array('status_id', $visibleColumns) || empty($visibleColumns)), 'class' => 'status-column'],
            ['field' => 'priority_id', 'label' => get_label('priority', 'Priority'), 'sortable' => true, 'visible' => (in_array('priority_id', $visibleColumns) || empty($visibleColumns)), 'class' => 'priority-column'],
            ['field' => 'start_date', 'label' => get_label('starts_at', 'Starts at'), 'sortable' => true, 'visible' => (in_array('start_date', $visibleColumns) || empty($visibleColumns))],
            ['field' => 'due_date', 'label' => get_label('ends_at', 'Ends at'), 'sortable' => true, 'visible' => (in_array('due_date', $visibleColumns) || empty($visibleColumns))],
            ['field' => 'created_at', 'label' => get_label('created_at', 'Created at'), 'sortable' => true, 'visible' => in_array('created_at', $visibleColumns)],
            ['field' => 'updated_at', 'label' => get_label('updated_at', 'Updated at'), 'sortable' => true, 'visible' => in_array('updated_at', $visibleColumns)],
        ];

        // Add custom fields dynamically
        if (isset($customFields) && $customFields->isNotEmpty()) {
            foreach ($customFields as $customField) {
                if ($customField->visibility !== null) {
                    $col = [
                        'field' => 'custom_field_' . $customField->id,
                        'label' => $customField->field_label,
                        'visible' => (in_array('custom_field_' . $customField->id, $visibleColumns) || empty($visibleColumns)),
                    ];
                    if ($customField->field_type === 'checkbox') {
                        $col['formatter'] = 'customFieldFormatter';
                    }
                    $columns[] = $col;
                }
            }
        }

        $columns[] = ['field' => 'actions', 'label' => get_label('actions', 'Actions'), 'visible' => (in_array('actions', $visibleColumns) || empty($visibleColumns))];

        $taskUrl = isset($viewAssigned) && $viewAssigned == 1
            ? ''
            : (!empty($id)
                ? url('/tasks/list/' . $id . '?from_home=' . (request()->is('home') || request()->is('projects/information/*') ? '1' : '0'))
                : url('/tasks/list?from_home=' . (request()->is('home') || request()->is('projects/information/*') ? '1' : '0')));
    @endphp
    <div class="card border shadow-none">
        <div class="card-body p-0">
            <x-tk-table
                id="task_table"
                :url="$taskUrl"
                :columns="$columns"
                data-sort-name="id"
                data-sort-order="desc"
                data-query-params="queryParamsTasks"
            >
                <x-slot:before>
                    <input type="hidden" id="data_type" value="tasks">
                    <input type="hidden" id="data_table" value="task_table">
                    <input type="hidden" id="data_reload"
                        value="{{ request()->is('home') || request()->is('projects/information/*') ? '1' : '0' }}">
                    <input type="hidden" id="save_column_visibility">
                    <input type="hidden" id="multi_select">
                </x-slot:before>
            </x-tk-table>
        </div>
    </div>
@else
    @if (!isset($emptyState) || $emptyState != 0)
        <?php
        $type = 'Tasks';
        ?>
        <x-empty-state-card :type="$type" />
    @endif
@endif
@if ((isset($tasks) && $tasks > 0) || (isset($emptyState) && $emptyState == 0))
    </div>
@endif
@section('page_scripts')
<script>
    var label_update = '<?= get_label('update', 'Update') ?>';
    var label_delete = '<?= get_label('delete', 'Delete') ?>';
    var label_duplicate = '<?= get_label('duplicate', 'Duplicate') ?>';
    var label_not_assigned = '<?= get_label('not_assigned', 'Not assigned') ?>';
    var add_favorite = '<?= get_label('add_favorite', 'Click to mark as favorite') ?>';
    var remove_favorite = '<?= get_label('remove_favorite', 'Click to remove from favorite') ?>';
    var id = '<?= $id ?? '' ?>';
</script>
<script src="{{ asset('assets/js/pages/tasks.js') }}"></script>
@endsection
