$(document).ready(function () {
    $("#sort").on("change", function () {
<<<<<<< HEAD
        $('#table').bootstrapTable('refresh');
    });
    $("#selected_sources").on("change", function () {
        $('#table').bootstrapTable('refresh');
    });
    $("#selected_stages").on("change", function () {
        $('#table').bootstrapTable('refresh');
    });
    $("#lead_date_range").on(
        "apply.daterangepicker",
        function (ev, picker) {
            var startDate = picker.startDate.format("YYYY-MM-DD");
            var endDate = picker.endDate.format("YYYY-MM-DD");
            $('#lead_end_date').val(endDate);
            $('#lead_start_date').val(startDate);
            $("#table").bootstrapTable('refresh');
        }
    );
    $("#lead_date_range").on(
        "cancel.daterangepicker",
        function (ev, picker) {
            $('#lead_end_date').val('');
            $('#lead_start_date').val('');
            $('#lead_date_range').val('');
            picker.setStartDate(moment());
            picker.setEndDate(moment());
            picker.updateElement();
            $("#table").bootstrapTable('refresh');
        }
    );

    $(document).on('click', '.clear-leads-filters', function (e) {
=======
        $('#leads_table').bootstrapTable('refresh');
    });
    $("#selected_sources").on("change", function () {
        $('#leads_table').bootstrapTable('refresh');
    });
    $("#selected_stages").on("change", function () {
        $('#leads_table').bootstrapTable('refresh');
    });

    $(document).on('click', '.clear-leads-filters', function (e) {
        e.preventDefault();
>>>>>>> 144e56db9f7d21936e8433596f818ef2d9bfc72e
        $('#sort').val('').trigger('change', [0]);
        $('#selected_sources').val('').trigger('change', [0]);
        $('#selected_stages').val('').trigger('change', [0]);
        $('#lead_date_range').val('');
<<<<<<< HEAD
        $('#lead_start_date').val('');
        $('#lead_end_date').val('');
=======
        $('#lead_date_range_from').val('');
        $('#lead_date_range_to').val('');
>>>>>>> 144e56db9f7d21936e8433596f818ef2d9bfc72e
        $('#leads_table').bootstrapTable('refresh');
    })

});
function queryParamsLead(p) {
    return {
        page: p.offset / p.limit + 1,
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search,
        sort: $('#sort').val(),
        source_ids: $('#selected_sources').val(),
<<<<<<< HEAD
        start_date: $('#lead_start_date').val(),
        end_date: $('#lead_end_date').val(),
=======
        start_date: $('#lead_date_range_from').val(),
        end_date: $('#lead_date_range_to').val(),
>>>>>>> 144e56db9f7d21936e8433596f818ef2d9bfc72e
        stage_ids: $('#selected_stages').val(),
    };
}

$(document).ready(function () {
    // Initialize TableFilterSync for users
    const leadFilterSync = new TableFilterSync({
        tableId: 'leads_table',
        dataType: 'leads',
        filters: [
            {
                selector: '#sort',
                type: 'select',
                name: 'sort',
                ajaxType: null
            },
            {
                selector: '#selected_sources',
                type: 'select2',
                name: 'source_ids',
                ajaxType: 'lead_sources'
            },
            {
                selector: '#selected_stages',
                type: 'select2',
                name: 'stage_ids',
                ajaxType: 'lead_stages'
            },
            {
                selector: '#lead_date_range',
                type: 'daterangepicker',
                name: 'lead_date_range',
<<<<<<< HEAD
                hiddenFrom: '#lead_start_date',
                hiddenTo: '#lead_end_date'
=======
                hiddenFrom: '#lead_date_range_from',
                hiddenTo: '#lead_date_range_to'
>>>>>>> 144e56db9f7d21936e8433596f818ef2d9bfc72e
            }
        ],
        preserveParams: [''],
        queryParamsFn: queryParamsLead // Reuse existing function
    });
});


