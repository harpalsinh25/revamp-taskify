
$(function () {
    $('#projects_report_table').on('load-success.bs.table', function (e, data) {
        $('#total-projects').text(data.summary.total_projects);
        $('#on-time-projects').text(data.summary.on_time_projects);
        $('#projects-with-due-tasks').text(data.summary.projects_with_due_tasks);
        $('#average-days-remaining').text((data.summary.average_days_remaining || 0).toFixed(2));
        $('#average-task-progress').text((data.summary.average_task_progress || 0).toFixed(2) + '%');
        $('#average-overdue-days-per-project').text((data.summary.average_overdue_days_per_project || 0).toFixed(2));
        $('#total-tasks').text(data.summary.total_tasks);
        $('#average-task-duration').text((data.summary.average_task_duration || 0).toFixed(2) + ' days');
        $('#total-overdue-days').text(data.summary.total_overdue_days);
        $('#overdue-projects-percentage').text(
            `${data.summary.overdue_projects || 0} (${(data.summary.overdue_projects_percentage || 0).toFixed(2)}%)`
        );
        $('#due-projects-percentage').text(
            `${data.summary.due_projects || 0} (${(data.summary.due_projects_percentage || 0).toFixed(2)}%)`
        );
        $('#average-budget-utilization').text((data.summary.average_budget_utilization || 0).toFixed(2) + '%');
        $('#total-team-members').text(data.summary.total_team_members);
    });


});
// Initialize advanced date range filters with preset ranges - FIRST before anything else
$(document).ready(function () {
<<<<<<< HEAD
    // Initialize advanced date range filters FIRST
    initAdvancedDateRangePicker({
        selector: '#filter_date_range',
        hiddenFrom: '#filter_date_range_from',
        hiddenTo: '#filter_date_range_to',
        tableId: 'projects_report_table'
    });

    initAdvancedDateRangePicker({
        selector: '#report_start_date_between',
        hiddenFrom: '#filter_start_date_from',
        hiddenTo: '#filter_start_date_to',
        tableId: 'projects_report_table'
    });

    initAdvancedDateRangePicker({
        selector: '#report_end_date_between',
        hiddenFrom: '#filter_end_date_from',
        hiddenTo: '#filter_end_date_to',
        tableId: 'projects_report_table'
    });

=======
>>>>>>> 144e56db9f7d21936e8433596f818ef2d9bfc72e
    // Export button
    $('#export_button').click(function () {
        var $exportButton = $(this);
        $exportButton.attr('disabled', true);
        // Prepare query parameters
        const queryParams = project_report_query_params({ offset: 0, limit: 1000, sort: 'id', order: 'desc', search: '' });
        // Construct the export URL
        const exportUrl = projects_report_export_url + '?' + $.param(queryParams);
        // Open the export URL in a new tab or window
        $exportButton.attr('disabled', false);
        window.open(exportUrl, '_blank');
    });
});
function project_report_query_params(p) {
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
        $('#projects_report_table').bootstrapTable('refresh');
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
    $('#projects_report_table').bootstrapTable('refresh');
})
// Initialize TableFilterSync AFTER daterangepickers are set up
$(document).ready(function () {
    // Initialize TableFilterSync - this will use already initialized daterangepickers
    const projectReportFilterSync = new TableFilterSync({
        tableId: 'projects_report_table',
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
        queryParamsFn: project_report_query_params // Reuse existing function
    });
});
