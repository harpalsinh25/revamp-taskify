"use strict";
class DashboardManager {
    constructor() {
        this.charts = {};
        this.colors = {
            success: "#22C55E",
            danger: "#EF4444",
            warning: "#F59E0B",
            info: "#3B82F6",
            primary: "#8B5CF6",
            secondary: "#6B7280",
            pastelSuccess: "#63ED7A",
            pastelDanger: "#FC544B",
            pastelWarning: "#FCD34D",
            pastelInfo: "#93C5FD",
            pastelPrimary: "#C4B5FD",
            grid: "#E2E8F0",
            text: "#64748B"
        };
        this.chartPalette = [
            "#22C55E", "#EF4444", "#F59E0B", "#3B82F6", "#8B5CF6",
            "#EC4899", "#14B8A6", "#F97316", "#84CC16", "#6366F1"
        ];
        this.dashboardDataEndpoint = "/dashboard/data";
        this.invoicesReportEndpoint = "/reports/income-vs-expense-report-data";
    }
    formatDataForChart(chartType, data, labels = [], categories = [], seriesName = "Values") {
        switch (chartType) {
            case 'polarArea':
            case 'pie':
            case 'donut':
            case 'radialBar':
                return this.formatCircularChartData(data, labels);
            case 'bar':
            case 'column':
                return this.formatBarChartData(data, categories, seriesName);
            case 'line':
            case 'area':
            case 'mixed':
                return this.formatLineChartData(data, categories, seriesName);
            default:
                console.warn(`Unsupported chart type: ${chartType}`);
                return { series: [], labels: [], categories: [] };
        }
    }
    formatCircularChartData(data, labels) {
        const formattedData = Array.isArray(data)
            ? data.map(val => Math.max(0, Number(val) || 0))
            : [];
        const formattedLabels = Array.isArray(labels) && labels.length === data.length
            ? labels
            : formattedData.map((_, i) => `Item ${i + 1}`);
        return {
            series: formattedData,
            labels: formattedLabels,
            categories: []
        };
    }
    formatBarChartData(data, categories, seriesName) {
        const formattedData = Array.isArray(data)
            ? data.map(val => Math.max(0, Number(val) || 0))
            : [];
        const formattedCategories = Array.isArray(categories) && categories.length === data.length
            ? categories
            : formattedData.map((_, i) => `Category ${i + 1}`);
        return {
            series: [{ name: seriesName, data: formattedData }],
            labels: [],
            categories: formattedCategories
        };
    }
    formatLineChartData(data, categories = [], seriesName = "Values") {
        let formattedSeries = [];
        if (!Array.isArray(data)) {
            return { series: [], labels: [], categories: [] };
        }
        const first = data[0];
        const isSeriesObject = first && typeof first === 'object' && ('data' in first || 'name' in first);
        const isSimpleNumericArray = typeof first === 'number' || typeof first === 'string' || Array.isArray(first) && typeof first[0] === 'number';
        if (isSeriesObject) {
            // data is [{name, data}, {name, data}]
            formattedSeries = data.map((series, i) => ({
                name: series.name || `${seriesName} ${i + 1}`,
                data: (Array.isArray(series.data) ? series.data : []).map(val => Math.max(0, Number(val) || 0))
            }));
        } else if (isSimpleNumericArray) {
            // data is [10, 20, 30]
            formattedSeries = [{
                name: seriesName,
                data: data.map(val => Math.max(0, Number(val) || 0))
            }];
        } else {
            // fallback: try to coerce to numeric array
            formattedSeries = [{
                name: seriesName,
                data: data.map(val => Math.max(0, Number(val) || 0))
            }];
        }
        const maxLength = Math.max(...formattedSeries.map(s => s.data.length), 0);
        // Use passed categories if useful; otherwise build sensible default labels.
        let formattedCategories = Array.isArray(categories) ? [...categories] : [];
        if (formattedCategories.length > maxLength) {
            formattedCategories = formattedCategories.slice(0, maxLength);
        } else if (formattedCategories.length < maxLength) {
            // pad with readable placeholders
            for (let i = formattedCategories.length; i < maxLength; i++) {
                formattedCategories.push(`Point ${i + 1}`);
            }
        }
        return {
            series: formattedSeries,
            labels: [],
            categories: formattedCategories
        };
    }
    init() {
        this.initFilters();
        // this.initSortable();
        this.initTooltips();
        this.updateDashboard();
    }
    renderPolarAreaChart(selector, data, colors, labels, label = "") {
        if (!document.querySelector(selector)) return;
        const { series, labels: formattedLabels } = this.formatDataForChart('polarArea', data, labels);
        const options = {
            series,
            labels: formattedLabels,
            colors: colors || this.chartPalette,
            chart: { type: 'polarArea', toolbar: { show: false } },
            stroke: { colors: ['#fff'] },
            fill: { opacity: 0.9 },
            dataLabels: { enabled: true, formatter: (val, opts) => opts.w.globals.series[opts.seriesIndex] },
            legend: { position: 'right', labels: { colors: this.colors.text } },
            yaxis: { show: false },
            responsive: [{ breakpoint: 480, options: { chart: { width: 350 }, legend: { position: 'bottom' } } }]
        };
        this.renderChartInstance(selector, options);
    }
    renderPieChart(selector, data, labels, colors = null) {
        if (!document.querySelector(selector)) return;
        const { series, labels: formattedLabels } = this.formatDataForChart('pie', data, labels);
        const options = {
            series,
            chart: { type: 'pie', height: 350, toolbar: { show: false } },
            labels: formattedLabels,
            colors: colors || this.chartPalette,
            dataLabels: { enabled: true, formatter: (val, opts) => opts.w.globals.series[opts.seriesIndex] },
            tooltip: { y: { formatter: (val, { seriesIndex }) => `${val} (${this.calculatePercentage(series)[seriesIndex]})` } },
            legend: { position: 'right', fontSize: '14px' },
            responsive: [{ breakpoint: 480, options: { chart: { width: 300 }, legend: { position: 'bottom' } } }]
        };
        this.renderChartInstance(selector, options);
    }
    renderDonutChart(selector, data, colors, labels, label = "") {
        if (!document.querySelector(selector)) return;
        const { series, labels: formattedLabels } = this.formatDataForChart('donut', data, labels);
        const options = {
            series,
            colors: colors || this.chartPalette,
            labels: formattedLabels,
            chart: { type: "donut", height: 250 },
            plotOptions: {
                pie: {
                    donut: {
                        size: "80%",
                        labels: {
                            show: true,
                            total: { show: true, label, fontSize: "16px", fontWeight: 500, formatter: () => series.reduce((a, b) => a + b, 0) }
                        }
                    }
                }
            },
            dataLabels: { enabled: false },
            tooltip: { y: { formatter: (val, { seriesIndex }) => `${val} (${this.calculatePercentage(series)[seriesIndex]})` } },
            legend: { position: "right", fontSize: "14px", markers: { radius: 12 } },
            responsive: [{ breakpoint: 480, options: { chart: { width: 180 } } }]
        };
        this.renderChartInstance(selector, options);
    }
    renderChartInstance(selector, options) {
        if (this.charts[selector]) this.charts[selector].destroy();
        this.charts[selector] = new ApexCharts(document.querySelector(selector), options);
        this.charts[selector].render();
    }
    updateDashboard() {
        const filters = this.getFilters();
        $.ajax({
            type: "POST",
            url: this.dashboardDataEndpoint,
            data: filters,
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
            dataType: "JSON",
            success: (response) => {
                // Update tiles
                $('#projects-tile .count').text(response.projects_count || 0).show();
                $('#tasks-tile .count').text(response.tasks_count || 0).show();
                $('#users-tile .count').text(response.users_count || 0).show();
                $('#clients-tile .count').text(response.clients_count || 0).show();
                $('#meetings-tile .count').text(response.meetings_count || 0).show();
                $('#todos-tile .count').text(response.todos_count || 0).show();
                // Update charts
                this.renderPolarAreaChart("#projectStatisticsChart", response.project_data || [], response.bg_colors || [], response.labels || [], label_total_projects);
                this.renderPieChart("#taskStatisticsChart", response.task_data || [], response.labels || [], response.bg_colors || []);
                this.renderDonutChart("#todoStatisticsChart", response.todo_data || [], [this.colors.pastelSuccess, this.colors.pastelDanger], ['Done', 'Pending'], label_total_todos);
                // Update status lists
                this.updateStatusList('#project-statistics .status-list', response.project_status_counts || {}, response.statuses || [], response.total_projects || 0, 'projects');
                this.updateStatusList('#task-statistics .status-list', response.task_status_counts || {}, response.statuses || [], response.total_tasks || 0, 'tasks');
                // Update todo list
                this.updateTodoList('#todos-overview .todo-list', response.todos || []);
                // Update timeline
                this.updateTimeline('#recent-activities .timeline', response.activities || []);
                // Update selected users count
                const userIds = $('#userFilter').val() || [];
                $('#selectedUsersCount').text(userIds.length > 0
                    ? `${userIds.length} ${userIds.length === 1 ? 'user' : 'users'} ${label_selected}`
                    : label_all_team_members_selected).show();
                // Update URL with filter parameters
                this.updateIncomeExpenseChart(filters);
                const urlParams = new URLSearchParams();
                if (filters.start_date) urlParams.set('start_date', filters.start_date);
                if (filters.end_date) urlParams.set('end_date', filters.end_date);
                if (userIds.length > 0) urlParams.set('user_ids', userIds.join(','));
                window.history.pushState({}, '', `${window.location.pathname}?${urlParams.toString()}`);
                // Fetch and update income vs. expense chart
            },
            error: (xhr, status, error) => console.error("Dashboard Update Error:", error)
        });
    }
    updateStatusList(selector, statusCounts, statuses, totalCount, type) {
        const container = $(selector);
        container.html(''); // Clear existing content
        if (container.length) {
            // Add table-responsive and table structure
            container.append(`
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <tbody>
        `);
            const tbody = container.find('tbody'); // Reference to the tbody for appending rows
            if (Object.keys(statusCounts).length && statuses.length) {
                statuses.forEach(status => {
                    const count = statusCounts[status.id] || 0;
                    const percentage = totalCount > 0 ? ((count / totalCount) * 100).toFixed(1) : 0;
                    tbody.append(`
                    <tr>
                        <td class="border-0 py-2">
                            <div class="d-flex align-items-center">
                                <div class="legend-dot bg-${status.color} me-2" style="width: 12px; height: 12px; border-radius: 50%;"></div>
                                <a href="${type === 'projects' ? '/projects/list' : '/tasks'}?status=${status.id}&status_ids[]=${status.id}"
   class="text-decoration-none text-dark fw-medium">
   ${status.title}
</a>
                            </div>
                        </td>
                        <td class="border-0 py-2 text-end">
                            <span class="fw-bold text-${status.color}">${count}</span>
                        </td>
                        <td class="border-0 py-2 text-end text-muted">
                            <small>${percentage}%</small>
                        </td>
                    </tr>
                `);
                });
                tbody.append(`
                <tr class="border-top">
                    <td class="pt-2 fw-bold">
                        <i class="bx bx-menu me-2"></i>${label_total}
                    </td>
                    <td class="pt-2 text-end fw-bold text-primary">${totalCount}</td>
                    <td class="pt-2 text-end text-muted">
                        <small>100%</small>
                    </td>
                </tr>
            `);
            } else {
                tbody.append(`
                <tr>
                    <td colspan="3" class="text-muted text-center">
                        ${label_no_data_available}
                    </td>
                </tr>
            `);
            }
            // Close table and tbody
            container.append(`
                    </tbody>
                </table>
            </div>
        `);
        } else {
            console.warn(`Container ${selector} not found`);
        }
    }
    updateTodoList(selector, todos) {
        const container = $(selector);
        container.html(''); // Clear existing content
        let html = '';
        if (container.length) {
            if (Array.isArray(todos) && todos.length > 0) {
                todos.forEach(todo => {
                    html += `
                    <li class="list-group-item d-flex align-items-center py-2">
                        <div class="me-3">
                            <input type="checkbox"
                                   id="${todo.id}"
                                   onclick="update_status(this)"
                                   name="${todo.id}"
                                   class="form-check-input"
                                   ${todo.is_completed ? 'checked' : ''}>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0 ${todo.is_completed ? 'text-decoration-line-through text-muted' : ''}" id="${todo.id}_title">
                                        ${todo.title}
                                    </h6>
                                    <small class="text-muted">${todo.created_at}</small>
                                </div>
                                <div class="user-progress d-flex align-items-center gap-2">
                                    <a href="javascript:void(0);"
                                       class="edit-todo text-primary"
                                       data-bs-toggle="modal"
                                       data-bs-target="#edit_todo_modal"
                                       data-id="${todo.id}"
                                       title="${label_update}">
                                        <i class="bx bx-edit"></i>
                                    </a>
                                    <a href="javascript:void(0);"
                                       class="delete text-danger"
                                       data-id="${todo.id}"
                                       data-type="todos"
                                       title="${label_delete}">
                                        <i class="bx bx-trash"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </li>`;
                });

            } else {
                html += `
                <div class="d-flex justify-content-center align-items-center text-muted p-3">
                    <span>${label_no_todos_found}</span>
                </div>`;
            }
            container.append(html);
        } else {
            console.warn(`Container ${selector} not found`);
        }
    }
    updateTimeline(selector, activities) {
        const container = $(selector);
        container.html('');
        if (activities.length) {
            activities.forEach(activity => {
                // Determine timeline point class based on activity type
                let timelinePointClass = 'timeline-point-primary';
                switch (activity.activity) {
                    case 'created':
                        timelinePointClass = 'timeline-point-success';
                        break;
                    case 'updated':
                        timelinePointClass = 'timeline-point-info';
                        break;
                    case 'deleted':
                        timelinePointClass = 'timeline-point-danger';
                        break;
                    case 'updated status':
                        timelinePointClass = 'timeline-point-warning';
                        break;
                }
                container.append(`
                <li class="timeline-item timeline-item-transparent">
                    <span class="timeline-point ${timelinePointClass}"></span>
                    <div class="timeline-event">
                        <div class="timeline-header d-flex justify-content-between align-items-center">
                            <h6 class="fw-semibold mb-1">${activity.message}</h6>
                            <small class="text-muted">${activity.created_at_diff}</small>
                        </div>
                        <div class="timeline-body">
                            <p class="text-muted">${activity.created_at_formatted}</p>
                        </div>
                    </div>
                </li>
            `);
            });
        } else {
            container.append(`
            <li class="timeline-item timeline-item-transparent text-center">
                <span class="timeline-point timeline-point-primary"></span>
                <div class="timeline-event">
                    <div class="timeline-header">
                        <h6 class="text-muted mb-0">${window.label_no_activities || 'No recent activities'}</h6>
                    </div>
                </div>
            </li>
        `);
        }
    }
    updateIncomeExpenseChart(filters) {

        $.ajax({
            type: "GET",
            url: this.invoicesReportEndpoint,
            data: filters,
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
            dataType: "JSON",
            success: (response) => {
                const { invoices = [], expenses = [] } = response;
                const groupByDate = (data, dateField, amountField) => {
                    return data.reduce((acc, item) => {
                        const date = (item[dateField] || '').split(' ')[0] || '';
                        const amount = parseFloat((item[amountField] || '').toString().replace(/[^0-9.-]+/g, "")) || 0;
                        if (!date) return acc;
                        acc[date] = (acc[date] || 0) + amount;
                        return acc;
                    }, {});
                };
                const invoicesByDate = groupByDate(invoices, 'from_date', 'amount');
                const expensesByDate = groupByDate(expenses, 'expense_date', 'amount');
                // robust parse for dd-mm-yyyy strings
                const parseDMY = (d) => {
                    if (!d) return new Date(0);
                    const parts = d.split('-');
                    if (parts.length !== 3) return new Date(d); // fallback
                    const [dd, mm, yyyy] = parts.map(p => Number(p));
                    return new Date(yyyy, mm - 1, dd);
                };
                const allDates = [...new Set([...Object.keys(invoicesByDate), ...Object.keys(expensesByDate)])];
                allDates.sort((a, b) => parseDMY(a) - parseDMY(b));
                const chartData = {
                    categories: allDates,
                    incomeData: allDates.map(d => invoicesByDate[d] || 0),
                    expenseData: allDates.map(d => expensesByDate[d] || 0)
                };
                // NOTE: pass categories as 4th argument (third is labels in your current signature)
                const { series, categories } = this.formatDataForChart('area', [
                    { name: label_income, data: chartData.incomeData },
                    { name: label_expenses, data: chartData.expenseData }
                ], [], chartData.categories); // <-- important: [] is labels, chartData.categories is 4th param
                const options = {
                    series,
                    chart: { height: 380, type: "area", stacked: false, toolbar: { show: false } },
                    stroke: { curve: "smooth", width: 2 },
                    fill: { type: "gradient", gradient: { opacityFrom: 0.6, opacityTo: 0.1 } },
                    colors: [this.colors.success, this.colors.danger],
                    xaxis: { categories, labels: { rotate: -45, style: { colors: this.colors.text, fontSize: "12px" } }, axisBorder: { show: false }, axisTicks: { show: false } },
                    yaxis: { labels: { formatter: (val) => "₹" + Math.abs(val).toLocaleString(), style: { colors: this.colors.text, fontSize: "12px" } } },
                    grid: { borderColor: this.colors.grid, strokeDashArray: 4, xaxis: { lines: { show: true } }, yaxis: { lines: { show: true } } },
                    tooltip: { shared: true, y: { formatter: (v) => "₹" + Math.abs(v).toLocaleString() } },
                    legend: { position: "top", horizontalAlign: "right", fontSize: "14px", markers: { radius: 12 } }
                };
                this.renderChartInstance("#income-expense-chart", options);
            },
            error: (xhr, status, error) => console.error("Income vs Expense Chart Update Error:", error)
        });
    }
    getFilters() {
        const filters = {
            start_date: $("#filter_date_range_from").val() || moment().subtract(6, "days").format("YYYY-MM-DD"),
            end_date: $("#filter_date_range_to").val() || moment().format("YYYY-MM-DD"),
            user_ids: $('#userFilter').val() || []
        };

        return filters;
    }
    initFilters() {
        if (typeof moment === "undefined" || !$.fn.daterangepicker) {
            console.error("Moment.js or DateRangePicker is not loaded!");
            return;
        }


        $('#daterange').daterangepicker({
            startDate: moment().subtract(6, 'days'),
            endDate: moment(),
            locale: { format: "YYYY-MM-DD", cancelLabel: label_clear },
            ranges: {
                [label_today]: [moment(), moment()],
                [label_yesterday]: [moment().subtract(1, "days"), moment().subtract(1, "days")],
                [label_last_7_days]: [moment().subtract(6, "days"), moment()],
                [label_last_30_days]: [moment().subtract(29, "days"), moment()],
                [label_current_month]: [moment().startOf("month"), moment().endOf("month")]
            }
        });

        $("#daterange").on("apply.daterangepicker", (ev, picker) => {
            $("#filter_date_range_from").val(picker.startDate.format("YYYY-MM-DD"));
            $("#filter_date_range_to").val(picker.endDate.format("YYYY-MM-DD"));

            this.updateDashboard();
        });

        $("#daterange").on("cancel.daterangepicker", (ev, picker) => {
            $("#filter_date_range_from").val(moment().subtract(6, "days").format("YYYY-MM-DD"));
            $("#filter_date_range_to").val(moment().format("YYYY-MM-DD"));
            picker.setStartDate(moment().subtract(6, "days"));
            picker.setEndDate(moment());
            picker.updateElement();

            this.updateDashboard();
        });

        $('.quick-date-btn').on('click', function (e) {
            const range = $(e.currentTarget).data('range');
            let startDate, endDate;
            switch (range) {
                case 'today':
                    startDate = endDate = moment();
                    break;
                case 'yesterday':
                    startDate = endDate = moment().subtract(1, 'days');
                    break;
                case 'last7days':
                    startDate = moment().subtract(6, 'days');
                    endDate = moment();
                    break;
                case 'last30days':
                    startDate = moment().subtract(29, 'days');
                    endDate = moment();
                    break;
                case 'thismonth':
                    startDate = moment().startOf('month');
                    endDate = moment().endOf('month');
                    break;
            }
            $('#daterange').data('daterangepicker').setStartDate(startDate);
            $('#daterange').data('daterangepicker').setEndDate(endDate);
            $("#filter_date_range_from").val(startDate.format("YYYY-MM-DD"));
            $("#filter_date_range_to").val(endDate.format("YYYY-MM-DD"));
            this.updateDashboard();
        }.bind(this));

        $('#userFilter').on('change', () => {

            this.updateDashboard();
        });

        $('.select-all-users-btn, .clear-user-selection-btn').on('click', () => {
            $('#userFilter').val([]).trigger('change');
            this.updateDashboard();
        });

        const urlParams = new URLSearchParams(window.location.search);
        const startDate = urlParams.get('start_date');
        const endDate = urlParams.get('end_date');
        const userIds = urlParams.get('user_ids') ? urlParams.get('user_ids').split(',') : [];


        if (startDate && endDate) {
            $('#daterange').data('daterangepicker').setStartDate(moment(startDate));
            $('#daterange').data('daterangepicker').setEndDate(moment(endDate));
            $("#filter_date_range_from").val(startDate);
            $("#filter_date_range_to").val(endDate);
        }

        if (userIds.length > 0) {
            $.ajax({
                url: '/users/list',
                dataType: 'json',
                data: { ids: userIds },
                success: (data) => {
                    data.forEach(user => {
                        const option = new Option(user.name, user.id, true, true);
                        $('#userFilter').append(option);
                    });
                    $('#userFilter').trigger('change');

                },
                error: (xhr, status, error) => {
                    console.error("Failed to load user data:", error);
                }
            });
        }


        setTimeout(() => this.updateDashboard(), 100);
    }
    initSortable() {
        const $container = $("#dashboard-items");
        if (!$container.length) return;
        Sortable.create($container[0], {
            animation: 150,
            ghostClass: "sortable-ghost",
            handle: ".draggable-item",
            onEnd: () => this.saveDashboardOrder()
        });
        this.loadDashboardOrder();
    }
    saveDashboardOrder() {
        const order = [];
        $("#dashboard-items .draggable-item").each(function (index) {
            order.push({ id: $(this).data("id"), height: $(this).outerHeight(), width: $(this).outerWidth(), position: index + 1 });
        });
        localStorage.setItem("dashboardOrder", JSON.stringify(order));
    }
    loadDashboardOrder() {
        const savedOrder = localStorage.getItem("dashboardOrder");
        if (!savedOrder) return;
        const order = JSON.parse(savedOrder);
        order.forEach(item => {
            const $item = $(`#dashboard-items .draggable-item[data-id="${item.id}"]`);
            $("#dashboard-items").append($item);
        });
    }
    initTooltips() {
        $(".draggable-item").each(function () {
            $(this).addClass("position-relative").append(`
                <span class="drag-tooltip-icon end-0 fs-4 me-4 mt-2 position-absolute top-0"
                      data-bs-toggle="tooltip" title="${label_drag_to_reorder}">
                    <i class="bx bx-move text-muted small"></i>
                </span>
            `);
        });
        $("[data-bs-toggle='tooltip']").tooltip();
    }
    calculatePercentage(data) {
        const total = data.reduce((a, b) => a + b, 0);
        return data.map(value => ((value / total) * 100).toFixed(2) + "%");
    }
}
$(document).ready(() => {
    const dashboard = new DashboardManager();
    dashboard.init();
});
