let activeIdleTrendChart, utilizationChart, trendChart, userTimeBreakdownChart;


$(document).ready(function () {
    $('#date_range').daterangepicker({
        startDate: moment().subtract(6, 'days'),
        endDate: moment(),
        opens: 'right',
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        },
        locale: {
            format: 'YYYY-MM-DD'
        }
    }, function (start, end, label) {
        console.log('Date range applied:', start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
        loadEmployeeOptions();
        loadAttendanceData();
    });

    initializeSelect2();
    loadEmployeeOptions();
    loadAttendanceData();
    setupEventListeners();
});
// Quick date range selection
function setDateRange(period) {
    let startDate, endDate;

    switch (period) {
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
        case 'lastmonth':
            startDate = moment().subtract(1, 'month').startOf('month');
            endDate = moment().subtract(1, 'month').endOf('month');
            break;
        default:
            console.warn('Unknown period:', period);
            return;
    }

    $('#date_range').data('daterangepicker').setStartDate(startDate);
    $('#date_range').data('daterangepicker').setEndDate(endDate);

    console.log('Quick date range set:', startDate.format('YYYY-MM-DD'), endDate.format('YYYY-MM-DD'));

    loadEmployeeOptions();
    loadAttendanceData();
}
$(document).on('click', '.quick-date-btn', function () {
    const period = $(this).data('range');
    setDateRange(period);
});

function setCurrentDate() {
    const now = new Date();
    $('#currentDate').text(now.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    }));
}




function initializeSelect2() {
    $('#employee_select').select2({
        placeholder: 'Select employees',
        allowClear: true
    });
}

function loadEmployeeOptions() {
    const dateRange = $('#date_range').data('daterangepicker');
    const startDate = dateRange ? dateRange.startDate.format('YYYY-MM-DD') : '2025-06-10';
    const endDate = dateRange ? dateRange.endDate.format('YYYY-MM-DD') : '2025-06-26';

    $.ajax({
        url: timeAndAttendanceDataUrl,
        type: "GET",
        data: {
            start_date: startDate,
            end_date: endDate
        },
        success: function (response) {
            if (response.data && Array.isArray(response.data)) {
                const employees = [...new Set(response.data.map(item => item.employee))];
                const user_ids = [...new Set(response.data.map(item => item.user_id))];
                console.log(user_ids);
                const $select = $('#employee_select');
                $select.html('<option value="">All Employees</option>');
                employees.forEach((emp, index) => {
                    $select.append(`<option value="${user_ids[index]}">${emp}</option>`);
                });
            }
        },
        error: function () {
            $('#employee_select').html('<option value="">No Employees Found</option>');
        }
    });
}

function setupEventListeners() {
    $('#searchInput').on('keyup', function () {
        const value = $(this).val().toLowerCase();
        $('#attendance-body tr.clickable-row').each(function () {
            const text = $(this).text().toLowerCase();
            $(this).toggle(text.includes(value));
            $(this).next('.timeline-row').hide();
        });
    });

    $(document).on('click', '.clickable-row', function () {
        const $row = $(this);
        const userId = $row.data('user-id');
        const date = $row.data('date');
        const $timelineRow = $row.next('.timeline-row');
        const $chartContainer = $timelineRow.find(`#timelineChartContainer-${userId}-${date}`);
        $timelineRow.toggleClass('d-none');

        if ($timelineRow.is(':visible') && !$chartContainer.data('loaded')) {
            $.ajax({
                url: attendanceTimelineUrl,
                type: "GET",
                data: {
                    user_id: userId,
                    date: date
                },
                success: function (response) {
                    renderTimelineChart($chartContainer[0], response.sessions || []);
                    $chartContainer.data('loaded', true);
                },
                error: function () {
                    $chartContainer.html(
                        '<p class="text-danger">Failed to load timeline data.</p>');
                }
            });
        }
    });

    $('#fetch_data, #employee_select').on('change click', loadAttendanceData);
}

function renderTimelineChart(container, sessions) {
    if (!container) return;
    if (!sessions || sessions.length === 0) {
        $(container).html('<p class="text-center">No session data available for this date.</p>');
        return;
    }

    const colorMap = {
        active: '#4CAF50',
        manual: '#2196F3',
        idle: '#FFC107',
        break: '#F44336',
        pending_manual: '#957df5',
        pending: '#FF9800',
    };

    const series = [{
        data: sessions.map(session => {
            // Parse timestamps - handles both ISO 8601 with timezone and legacy formats
            // ISO 8601: "2025-10-09T10:22:00+05:30" (with timezone)
            // Legacy: "2025-10-09 10:14:06" (convert to ISO)
            let startStr = session.start;
            let endStr = session.end;

            // Convert space to 'T' if not already ISO format
            if (startStr.includes(' ') && !startStr.includes('T')) {
                startStr = startStr.replace(' ', 'T');
            }
            if (endStr.includes(' ') && !endStr.includes('T')) {
                endStr = endStr.replace(' ', 'T');
            }

            const startTime = new Date(startStr).getTime();
            const endTime = new Date(endStr).getTime();

            // Validate parsed times
            if (isNaN(startTime) || isNaN(endTime)) {
                console.error('Failed to parse session times:', session);
                console.error('Start string:', startStr, 'End string:', endStr);
            }

            return {
                x: 'Workday',
                y: [startTime, endTime],
                fillColor: colorMap[session.type] || '#dfe6e9',
                sessionType: session.type
            };
        })
    }];

    const options = {
        chart: {
            type: 'rangeBar',
            height: 150,
            toolbar: {
                show: true
            }
        },
        plotOptions: {
            bar: {
                horizontal: true,
                barHeight: '60%'
            }
        },
        series: series,
        xaxis: {
            type: 'datetime',
            labels: {
                datetimeUTC: false,
                format: 'hh:mm TT'
            }
        },
        yaxis: {
            labels: {
                style: {
                    fontSize: '14px'
                }
            }
        },
        tooltip: {
            custom: ({
                series,
                seriesIndex,
                dataPointIndex,
                w
            }) => {
                const d = w.config.series[seriesIndex].data[dataPointIndex];
                const start = new Date(d.y[0]);
                const end = new Date(d.y[1]);
                const color = d.fillColor || '#888';
                return `
                <div style="background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); padding: 12px 16px; min-width: 180px; border-left: 5px solid ${color};">
                    <div style="font-weight:600; color:${color}; margin-bottom:4px;">
                        ${d.sessionType.charAt(0).toUpperCase() + d.sessionType.slice(1)}
                    </div>
                    <div style="font-size:13px; color:#333;">
                        <span style="font-weight:500;">Start:</span> ${start.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}<br>
                        <span style="font-weight:500;">End:</span> ${end.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                    </div>
                    <div style="font-size:12px; color:#888; margin-top:4px;">
                        Duration: ${Math.round((d.y[1] - d.y[0]) / 60000)} min
                    </div>
                </div>`;
            }
        },
        fill: {
            opacity: 0.8,
            colors: series[0].data.map(s => s.fillColor)
        },
        colors: series[0].data.map(s => s.fillColor),
        grid: {
            borderColor: '#e0e0e0',
            row: {
                colors: ['#f9f9f9', 'transparent'],
                opacity: 0.5
            }
        },
        legend: {
            show: false
        }
    };

    if (container._apexchart) container._apexchart.destroy();
    const chart = new ApexCharts(container, options);
    chart.render();
    container._apexchart = chart;
}

function loadAttendanceData() {
    const dateRange = $('#date_range').data('daterangepicker');
    const startDate = dateRange ? dateRange.startDate.format('YYYY-MM-DD') : '2025-06-10';
    const endDate = dateRange ? dateRange.endDate.format('YYYY-MM-DD') : '2025-06-26';
    const employees = $('#employee_select').val();


    $.ajax({
        url: timeAndAttendanceDataUrl,
        data: {
            start_date: startDate,
            end_date: endDate,
            user_id: employees ? employees : ''
        },
        type: "GET",
        beforeSend: function () {
            $('#attendance-body').html(
                `<tr><td colspan="12" class="text-center text-muted">Loading attendance data...</td></tr>`
            );
        },
        success: function (response) {
            if (response.data && Array.isArray(response.data)) {
                updateStatistics(response.summary, response.data);
                updateCharts(response.data);
                updateTable(response.data);
            } else {
                $('#attendance-body').html(
                    `<tr><td colspan="12" class="text-center text-danger">Invalid data received from server.</td></tr>`
                );
            }
        },
        error: function () {
            $('#attendance-body').html(
                `<tr><td colspan="12" class="text-center text-danger">Failed to load data. Please refresh.</td></tr>`
            );
        }
    });
}

function updateStatistics(summary, data) {
    $('#total_employees').text(summary.total_employees || 0);
    $('#total_records').text(summary.total_records || 0);
    $('#total_work_hours').text(summary.total_work_hours || '00:00');
    $('#total_break_time').text(summary.total_break_time || '00:00');
    $('#total_idle_time').text(summary.total_idle_time || '00:00');
    $('#avgUtilization').text(summary.average_utilization ? summary.average_utilization + '%' : '0%');
}

function updateActiveIdleTrendChart(data) {
    const dateMap = {};
    data.forEach(item => {
        if (!dateMap[item.date]) dateMap[item.date] = {
            active: 0,
            idle: 0
        };
        dateMap[item.date].active += toMinutes(item.active_time || '00:00');
        dateMap[item.date].idle += toMinutes(item.idle_time || '00:00');
    });

    const dates = Object.keys(dateMap).sort();
    const activeData = dates.map(date => +(dateMap[date].active / 60).toFixed(2));
    const idleData = dates.map(date => +(dateMap[date].idle / 60).toFixed(2));

    const options = {
        series: [{
            name: 'Active Hours',
            data: activeData
        },
        {
            name: 'Idle Hours',
            data: idleData
        }
        ],
        chart: {
            type: 'area',
            height: 350,
            toolbar: {
                show: false
            }
        },
        colors: ['#4CAF50', '#FFC107'],
        stroke: {
            curve: 'smooth',
            width: 3
        },
        dataLabels: {
            enabled: false
        },
        xaxis: {
            categories: dates.map(date => new Date(date).toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric'
            })),
            title: {
                text: 'Date'
            }
        },
        yaxis: {
            title: {
                text: 'Hours'
            },
            min: 0
        },
        legend: {
            position: 'top'
        },
        tooltip: {
            y: {
                formatter: val => decimalToHHMM(val)
            }
        },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.4,
                opacityTo: 0.1,
                stops: [0, 90, 100]
            }
        }
    };

    if (activeIdleTrendChart) activeIdleTrendChart.destroy();
    activeIdleTrendChart = new ApexCharts(document.querySelector("#activeIdleTrendChart"), options);
    activeIdleTrendChart.render();
}

function updateCharts(data) {
    updateActiveIdleTrendChart(data);

    const utilizationMap = {};
    data.forEach(item => {
        if (!utilizationMap[item.employee]) {
            utilizationMap[item.employee] = { productive: 0, idle: 0, break: 0, count: 0 };
        }
        utilizationMap[item.employee].productive += toMinutes(item.active_time || '00:00');
        utilizationMap[item.employee].idle += toMinutes(item.idle_time || '00:00');
        utilizationMap[item.employee].break += toMinutes(item.break_time || '00:00');
        utilizationMap[item.employee].count++;
    });

    const employees = Object.keys(utilizationMap);
    const productive = employees.map(u => +(utilizationMap[u].productive / utilizationMap[u].count / 60).toFixed(2));
    const idle = employees.map(u => +(utilizationMap[u].idle / utilizationMap[u].count / 60).toFixed(2));
    const breakT = employees.map(u => +(utilizationMap[u].break / utilizationMap[u].count / 60).toFixed(2));

    const utilOptions = {
        series: [
            { name: 'Productive', data: productive },
            { name: 'Idle', data: idle },
            { name: 'Break', data: breakT }
        ],
        chart: {
            type: 'bar',
            height: 350,
            stacked: true,
            toolbar: { show: false }
        },
        colors: ['#4CAF50', '#FFC107', '#F44336'],
        xaxis: { categories: employees },
        yaxis: {
            title: { text: 'Avg Hours/Day' }
        },
        plotOptions: {
            bar: {
                borderRadius: 6,
                horizontal: true
            }
        },
        legend: { position: 'top' },
        tooltip: {
            y: {
                formatter: val => decimalToHHMM(val)
            }
        }
    };

    if (utilizationChart) utilizationChart.destroy();
    utilizationChart = new ApexCharts(document.querySelector("#utilizationChart"), utilOptions);
    utilizationChart.render();

    const groupByDate = {};
    data.forEach(item => {
        if (!groupByDate[item.date]) groupByDate[item.date] = { present: 0, late: 0, absent: 0 };
        if (item.clock_in === '--') {
            groupByDate[item.date].absent++;
        } else {
            const ci = new Date(`${item.date} ${convertTo24Hour(item.clock_in)}`);
            const std = new Date(`${item.date} ` + WorkDayStartTime);
            ci > std ? groupByDate[item.date].late++ : groupByDate[item.date].present++;
        }
    });

    const dates = Object.keys(groupByDate).sort();
    const trendOptions = {
        series: [
            { name: 'Present', data: dates.map(d => groupByDate[d].present) },
            { name: 'Late', data: dates.map(d => groupByDate[d].late) },
            { name: 'Absent', data: dates.map(d => groupByDate[d].absent) }
        ],
        chart: {
            type: 'area',
            height: 350,
            toolbar: { show: false }
        },
        colors: ['#4CAF50', '#FFC107', '#F44336'],
        stroke: {
            curve: 'smooth',
            width: 3
        },
        dataLabels: { enabled: false },
        xaxis: {
            categories: dates.map(date => new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }))
        },
        yaxis: {
            title: { text: 'Employees' }
        },
        legend: { position: 'top' }
    };

    if (trendChart) trendChart.destroy();
    trendChart = new ApexCharts(document.querySelector("#attendanceTrendChart"), trendOptions);
    trendChart.render();

    const breakdownOptions = {
        series: [
            { name: 'Productive', data: productive },
            { name: 'Idle', data: idle },
            { name: 'Break', data: breakT }
        ],
        chart: {
            type: 'bar',
            height: 350,
            toolbar: { show: false }
        },
        colors: ['#4CAF50', '#FFC107', '#F44336'],
        xaxis: { categories: employees },
        yaxis: {
            title: { text: 'Avg Hours/Day' }
        },
        plotOptions: {
            bar: {
                borderRadius: 6,
                horizontal: false,
                columnWidth: '45%'
            }
        },
        legend: { position: 'top' },
        tooltip: {
            y: {
                formatter: val => decimalToHHMM(val)
            }
        }
    };

    if (userTimeBreakdownChart) userTimeBreakdownChart.destroy();
    userTimeBreakdownChart = new ApexCharts(document.querySelector("#userTimeBreakdownChart"), breakdownOptions);
    userTimeBreakdownChart.render();
}

function toMinutes(timeStr) {
    if (!timeStr || timeStr === '--' || typeof timeStr !== 'string' || !timeStr.includes(':')) return 0;
    const [h, m] = timeStr.split(':').map(Number);
    return h * 60 + m;
}

function updateTable(data) {
    let html = '';
    if (data.length > 0) {
        // Debug: Log first entry to see all available fields
        if (data[0]) {
            console.log('First entry data:', data[0]);
            console.log('Available keys:', Object.keys(data[0]));
        }
        data.forEach(entry => {
            const status = getAttendanceStatus(entry);
            const statusClass = status === 'Present' ? 'bg-label-success' :
                status === 'Late' ? 'bg-label-warning' : 'bg-label-danger';
            html += `
            <tr class="clickable-row" data-user-id="${entry.user_id}" data-date="${entry.date}">
                <td><strong>${entry.employee}</strong></td>
                <td>${entry.date}</td>
                <td>${entry.clock_in}</td>
                <td>${entry.clock_out}</td>
                <td>${entry.work_time}</td>
                <td>${entry.active_time}</td>
                <td>${entry.manual_time}</td>
                <td>${entry.pending_manual_time || '--'}</td>
                <td>${entry.break_time}</td>
                <td>${entry.idle_time}</td>
                <td>${entry.utilization}</td>
                <td><span class="badge ${statusClass}">${status}</span></td>
            </tr>
            <tr class="timeline-row d-none">
                <td colspan="12">
                    <div id="timelineChartContainer-${entry.user_id}-${entry.date}"></div>
                </td>
            </tr>`;
        });
    } else {
        html =
            `<tr><td colspan="12" class="text-center">No attendance records found for the selected filters.</td></tr>`;
    }
    $('#attendance-body').html(html);
}

function getAttendanceStatus(entry) {
    if (entry.clock_in === '--') return 'Absent';
    const ci = new Date(`${entry.date} ${convertTo24Hour(entry.clock_in)}`);
    const std = new Date(`${entry.date} ` + WorkDayStartTime);
    return ci > std ? 'Late' : 'Present';
}

function convertTo24Hour(timeStr) {
    if (!timeStr || timeStr === '--') return '00:00:00';
    const match = timeStr.match(/(\d{1,2}):(\d{2})\s*(AM|PM)?/i);
    if (!match) return timeStr;
    let [, hours, minutes, period] = match;
    hours = parseInt(hours, 10);
    if (period) {
        period = period.toUpperCase();
        if (period === 'PM' && hours < 12) hours += 12;
        if (period === 'AM' && hours === 12) hours = 0;
    }
    return `${hours.toString().padStart(2, '0')}:${minutes}:00`;
}

function exportToCSV() {
    $.ajax({
        url: timeAndAttendanceDataUrl,
        type: "GET",
        data: {
            start_date: $('#date_range').data('daterangepicker') ? $('#date_range').data('daterangepicker')
                .startDate.format('YYYY-MM-DD') : '',
            end_date: $('#date_range').data('daterangepicker') ? $('#date_range').data('daterangepicker')
                .endDate.format('YYYY-MM-DD') : '',
            employee_id: $('#employee_select').val() ? $('#employee_select').val() : ''
        },
        success: function (response) {
            const data = response.data;
            const csvContent = "data:text/csv;charset=utf-8," +
                "Employee,Date,Clock In,Clock Out,Work Time,Active Time,Break Time,Utilization,Status\n" +
                data.map(row =>
                    `${row.employee},${row.date},${row.clock_in},${row.clock_out},${row.work_time},${row.active_time},${row.break_time},${row.utilization},${row.status}`
                ).join("\n");
            const link = document.createElement("a");
            link.setAttribute("href", encodeURI(csvContent));
            link.setAttribute("download", "attendance_report.csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    });
}

function decimalToHHMM(decimal) {
    const totalMinutes = Math.round(decimal * 60);
    const hours = Math.floor(totalMinutes / 60);
    const minutes = totalMinutes % 60;
    return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
}
