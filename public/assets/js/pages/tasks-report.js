
$(function () {
    $('#tasks_report_table').on('load-success.bs.table', function (e, data) {
        $('#total-tasks').text(data.summary.total_tasks);
        $('#due-tasks').text(
            `${data.summary.due_tasks || 0} (${(data.summary.due_tasks_percentage || 0).toFixed(2)}%)`
        );
        $('#overdue-tasks').text(
            `${data.summary.overdue_tasks || 0} (${(data.summary.overdue_tasks_percentage || 0).toFixed(2)}%)`
        );
        $('#average-task-completion-time').text(data.summary.average_task_duration);
        $('#urgent-tasks').text(
            `${data.summary.urgent_tasks || 0} (${(data.summary.urgent_tasks_percentage || 0).toFixed(2)}%)`
        );
        $('#total-tasks').text(data.summary.total_tasks);
    });


});
$(document).ready(function () {
    $('#export_button').click(function () {
        var $exportButton = $(this);
        $exportButton.attr('disabled', true);
        // Prepare query parameters
        const queryParams = tasks_report_query_params({ offset: 0, limit: 1000, sort: 'id', order: 'desc', search: '' });
        // Construct the export URL
        const exportUrl = tasks_report_export_url + '?' + $.param(queryParams);
        // Open the export URL in a new tab or window
        $exportButton.attr('disabled', false);
        window.open(exportUrl, '_blank');
    });
<<<<<<< HEAD
    // Initialize advanced date range filters with preset ranges
    initAdvancedDateRangePicker({
        selector: '#filter_date_range',
        hiddenFrom: '#filter_date_range_from',
        hiddenTo: '#filter_date_range_to',
        tableId: 'tasks_report_table'
    });

    initAdvancedDateRangePicker({
        selector: '#report_start_date_between',
        hiddenFrom: '#filter_start_date_from',
        hiddenTo: '#filter_start_date_to',
        tableId: 'tasks_report_table'
    });

    initAdvancedDateRangePicker({
        selector: '#report_end_date_between',
        hiddenFrom: '#filter_end_date_from',
        hiddenTo: '#filter_end_date_to',
        tableId: 'tasks_report_table'
    });
=======
>>>>>>> 144e56db9f7d21936e8433596f818ef2d9bfc72e
});
function tasks_report_query_params(p) {
    return {
        project_ids: $('#project_filter').val(),
        user_ids: $('#user_filter').val(),
        client_ids: $('#client_filter').val(),
        status_ids: $('#status_filter').val(),
        priority_ids: $('#priority_filter').val(),
<<<<<<< HEAD
        date_between_from: $('#filter_date_range_from').val(),
        date_between_to: $('#filter_date_range_to').val(),
        start_date_from: $('#filter_start_date_from').val(),
        start_date_to: $('#filter_start_date_to').val(),
        end_date_from: $('#filter_end_date_from').val(),
        end_date_to: $('#filter_end_date_to').val(),
=======
        date_between_from: $('#report_date_between_from').val(),
        date_between_to: $('#report_date_between_to').val(),
        start_date_from: $('#report_start_date_from').val(),
        start_date_to: $('#report_start_date_to').val(),
        end_date_from: $('#report_end_date_from').val(),
        end_date_to: $('#report_end_date_to').val(),
>>>>>>> 144e56db9f7d21936e8433596f818ef2d9bfc72e
        page: p.offset / p.limit + 1,
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search
    };
}
addDebouncedEventListener('#project_filter,#user_filter,#client_filter,#status_filter,#priority_filter', 'change', function (e, refreshTable) {
    e.preventDefault();
    if (typeof refreshTable === 'undefined' || refreshTable) {
        $('#tasks_report_table').bootstrapTable('refresh');
    }
});

$(document).on('click', '.clear-report-filters', function (e) {
    e.preventDefault();
<<<<<<< HEAD
    $('#filter_date_range').val('');
    $('#filter_date_range_from').val('');
    $('#filter_date_range_to').val('');
    $('#report_start_date_between').val('');
    $('#filter_start_date_from').val('');
    $('#filter_start_date_to').val('');
    $('#report_end_date_between').val('');
    $('#filter_end_date_from').val('');
    $('#filter_end_date_to').val('');
=======
    $('#report_date_between').val('');
    $('#report_date_between_from').val('');
    $('#report_date_between_to').val('');
    $('#report_start_date_between').val('');
    $('#report_start_date_from').val('');
    $('#report_start_date_to').val('');
    $('#report_end_date_between').val('');
    $('#report_end_date_from').val('');
    $('#report_end_date_to').val('');
>>>>>>> 144e56db9f7d21936e8433596f818ef2d9bfc72e
    $('#project_filter').val('').trigger('change', [0]);
    $('#user_filter').val('').trigger('change', [0]);
    $('#client_filter').val('').trigger('change', [0]);
    $('#status_filter').val('').trigger('change', [0]);
    $('#priority_filter').val('').trigger('change', [0]);
    $('#tasks_report_table').bootstrapTable('refresh');
})

$(document).ready(function () {
    // Initialize TableFilterSync for users
    const taskReportFilterSync = new TableFilterSync({
        tableId: 'tasks_report_table',
        dataType: 'report',
        filters: [
            {
<<<<<<< HEAD
                selector: '#filter_date_range',
                type: 'daterangepicker',
                name: 'filter_date_range',
                hiddenFrom: '#filter_date_range_from',
                hiddenTo: '#filter_date_range_to'
=======
                selector: '#report_date_between',
                type: 'daterangepicker',
                name: 'report_date_between',
                hiddenFrom: '#report_date_between_from',
                hiddenTo: '#report_date_between_to'
>>>>>>> 144e56db9f7d21936e8433596f818ef2d9bfc72e
            },
            {
                selector: '#report_start_date_between',
                type: 'daterangepicker',
                name: 'report_start_date_between',
<<<<<<< HEAD
                hiddenFrom: '#filter_start_date_from',
                hiddenTo: '#filter_start_date_to'
=======
                hiddenFrom: '#report_start_date_from',
                hiddenTo: '#report_start_date_to'
>>>>>>> 144e56db9f7d21936e8433596f818ef2d9bfc72e
            },
            {
                selector: '#report_end_date_between',
                type: 'daterangepicker',
                name: 'report_end_date_between',
<<<<<<< HEAD
                hiddenFrom: '#filter_end_date_from',
                hiddenTo: '#filter_end_date_to'
=======
                hiddenFrom: '#report_end_date_from',
                hiddenTo: '#report_end_date_to'
>>>>>>> 144e56db9f7d21936e8433596f818ef2d9bfc72e
            },
            {
                selector: '#project_filter',
                type: 'select2',
                name: 'project_ids',
                ajaxType: 'projects'
            },
            {
                selector: '#user_filter',
                type: 'select2',
                name: 'user_ids',
                ajaxType: 'users'
            },
            {
                selector: '#client_filter',
                type: 'select2',
                name: 'client_ids',
                ajaxType: 'clients'
            },
            {
                selector: '#status_filter',
                type: 'select2',
                name: 'statuses',
                ajaxType: 'statuses'
            },
            {
                selector: '#priority_filter',
                type: 'select2',
                name: 'priority_ids',
                ajaxType: 'priorities'
            }

        ],
        preserveParams: [''],
        queryParamsFn: tasks_report_query_params // Reuse existing function
    });
});
