<!-- projects card -->
<div class="align-items-baseline d-flex gap-1 tk-filter-bar">
    <div class="col-md-3 mb-4">
        <select class="form-select users_select" id="wa_user_filter" aria-label="Default select example" data-placeholder="<?= get_label('select_members', 'Select Members') ?>" multiple>
        </select>
    </div>
    <div class="col-md-3 mb-4">
        <select class="form-select clients_select" id="wa_client_filter" aria-label="Default select example" data-placeholder="<?= get_label('select_clients', 'Select Clients') ?>" multiple>
        </select>
    </div>
    <div class="col-md-4">
        <div class="input-group input-group-merge">
            <input type="number" id="upcoming_days_wa" name="upcoming_days" class="form-control" min="0" placeholder="<?= get_label('till_upcoming_days_def_30', 'Till upcoming days : default 30') ?>" autocomplete="off">
        </div>
    </div>
    <div class="col-md-2">
        <div>
            <button type="button" id="upcoming_days_wa_filter" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-original-title="<?= get_label('filter', 'Filter') ?>"><i class='bx bx-filter-alt'></i></button>
        </div>
    </div>
</div>
<x-tk-table id="wa_table" :url="url('/home/upcoming-work-anniversaries')"
    data-sort-name="doj" data-sort-order="asc" data-query-params="queryParamsUpcomingWa"
    :columns="[
        ['field' => 'id', 'label' => get_label('id', 'ID')],
        ['field' => 'member', 'label' => get_label('whose', 'Whose')],
        ['field' => 'type', 'label' => get_label('type', 'Type')],
        ['field' => 'wa_date', 'label' => get_label('work_anniversary_date', 'Work anniversary date'), 'sortable' => true],
        ['field' => 'days_left', 'label' => get_label('days_left', 'Days left')],
    ]">
    <x-slot:before>
        <input type="hidden" id="data_type" value="users">
        <input type="hidden" id="data_table" value="wa_table">
        <input type="hidden" id="data_reload" value="1">
        <input type="hidden" id="multi_select" value="upcoming-wa">
    </x-slot:before>
</x-tk-table>