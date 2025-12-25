
'use strict';
function queryParamsExpenseTypes(p) {
    return {
        page: p.offset / p.limit + 1,
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search
    };
}

function queryParams(p) {
    return {
        "user_ids": $('#user_filter').val(),
        "type_ids": $('#type_filter').val(),
<<<<<<< HEAD
        "date_from": $('#expense_date_from').val(),
        "date_to": $('#expense_date_to').val(),
=======
        "date_from": $('#expense_date_between_from').val(),
        "date_to": $('#expense_date_between_to').val(),
>>>>>>> 144e56db9f7d21936e8433596f818ef2d9bfc72e
        page: p.offset / p.limit + 1,
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search
    };
}

window.icons = {
    refresh: 'bx-refresh',
    toggleOn: 'bx-toggle-right',
    toggleOff: 'bx-toggle-left'
}

function loadingTemplate(message) {
    return '<i class="bx bx-loader-alt bx-spin bx-flip-vertical" ></i>'
}

<<<<<<< HEAD
$('#expense_from_date_between').on('apply.daterangepicker', function (ev, picker) {
    var fromDate = picker.startDate.format('YYYY-MM-DD');
    var toDate = picker.endDate.format('YYYY-MM-DD');

    $('#expense_date_from').val(fromDate);
    $('#expense_date_to').val(toDate);

    $('#table').bootstrapTable('refresh');
});

$('#expense_from_date_between').on('cancel.daterangepicker', function (ev, picker) {
    $('#expense_date_from').val('');
    $('#expense_date_to').val('');
    $('#expense_from_date_between').val('');
    picker.setStartDate(moment());
    picker.setEndDate(moment());
    picker.updateElement();
    $('#table').bootstrapTable('refresh');
});

=======
>>>>>>> 144e56db9f7d21936e8433596f818ef2d9bfc72e
addDebouncedEventListener('#user_filter, #type_filter', 'change', function (e, refreshTable) {
    e.preventDefault();
    if (typeof refreshTable === 'undefined' || refreshTable) {
        $('#table').bootstrapTable('refresh');
    }
});

$(document).on('click', '.clear-expenses-filters', function (e) {
    e.preventDefault();
<<<<<<< HEAD
    $('#expense_from_date_between').val('');
    $('#expense_date_from').val('');
    $('#expense_date_to').val('');
=======
    $('#expense_date_between').val('');
    $('#expense_date_between_from').val('');
    $('#expense_date_between_to').val('');
>>>>>>> 144e56db9f7d21936e8433596f818ef2d9bfc72e
    $('#user_filter').val('').trigger('change', [0]);
    $('#type_filter').val('').trigger('change', [0]);
    $('#table').bootstrapTable('refresh');
})

$(document).ready(function () {
    // Initialize TableFilterSync for users
    const expenseFilterSync = new TableFilterSync({
        tableId: 'table',
        dataType: 'expenses',
        filters: [
            {
<<<<<<< HEAD
                selector: '#expense_from_date_between',
                type: 'daterangepicker',
                name: 'expense_from_date_between',
                hiddenFrom: '#expense_date_from',
                hiddenTo: '#expense_date_to'
=======
                selector: '#expense_date_between',
                type: 'daterangepicker',
                name: 'expense_date_between',
                hiddenFrom: '#expense_date_between_from',
                hiddenTo: '#expense_date_between_to'
>>>>>>> 144e56db9f7d21936e8433596f818ef2d9bfc72e
            },
            {
                selector: '#user_filter',
                type: 'select2',
                name: 'user_ids',
                ajaxType: 'users'
            },

            {
                selector: '#type_filter',
                type: 'select2',
                name: 'type_ids',
                ajaxType: 'expense_types'
            }
        ],
        preserveParams: [''],
        queryParamsFn: queryParams // Reuse existing function
    });
});
