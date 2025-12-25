/**
 * Advanced Date Range Filter Module
 * Provides centralized daterangepicker initialization with preset ranges
 * for consistent date filtering across Taskify
 *
 * @version 1.0.0
 * @author Taskify Development Team
 */

(function (window) {
    'use strict';

    /**
     * Initialize advanced date range picker with preset ranges
     *
     * @param {Object} config - Configuration object
     * @param {string} config.selector - jQuery selector for the input element
     * @param {string} config.hiddenFrom - Selector for hidden 'from' date field
     * @param {string} config.hiddenTo - Selector for hidden 'to' date field
     * @param {string} config.tableId - ID of the table to refresh on date change
     * @param {Object} config.locale - Optional locale settings for daterangepicker
     * @param {Function} config.callback - Optional callback function after date selection
     * @param {boolean} config.autoApply - Auto apply selection (default: false)
     * @param {boolean} config.showDropdowns - Show year/month dropdowns (default: true)
     * @param {number} config.maxSpan - Maximum span in days (optional)
     */
    window.initAdvancedDateRangePicker = function (config) {
        // Validate required config
        if (!config || !config.selector || !config.hiddenFrom || !config.hiddenTo) {
            console.error('Advanced Date Range Filter: Missing required configuration');
            return;
        }

        var $input = $(config.selector);
        var $hiddenFrom = $(config.hiddenFrom);
        var $hiddenTo = $(config.hiddenTo);
        var tableId = config.tableId;

        // Check if elements exist
        if (!$input.length) {
            console.warn('Advanced Date Range Filter: Input element not found - ' + config.selector);
            return;
        }

        // IMPORTANT: Destroy any existing daterangepicker first
        if ($input.data('daterangepicker')) {
            console.log('Advanced Date Filter: Removing existing daterangepicker from ' + config.selector);
            $input.data('daterangepicker').remove();
            $input.off('.daterangepicker');
        }

        console.log('Advanced Date Filter: Initializing ' + config.selector + ' with preset ranges');

        // Define preset ranges with translations
        var ranges = {};

        // Get labels from window or use defaults
        var labels = {
            today: window.label_today || 'Today',
            yesterday: window.label_yesterday || 'Yesterday',
            last7Days: window.label_last_7_days || 'Last 7 Days',
            last30Days: window.label_last_30_days || 'Last 30 Days',
            thisMonth: window.label_this_month || 'This Month',
            lastMonth: window.label_last_month || 'Last Month',
            thisYear: window.label_this_year || 'This Year',
            customRange: window.label_custom_range || 'Custom Range'
        };

        ranges[labels.today] = [moment(), moment()];
        ranges[labels.yesterday] = [moment().subtract(1, 'days'), moment().subtract(1, 'days')];
        ranges[labels.last7Days] = [moment().subtract(6, 'days'), moment()];
        ranges[labels.last30Days] = [moment().subtract(29, 'days'), moment()];
        ranges[labels.thisMonth] = [moment().startOf('month'), moment().endOf('month')];
        ranges[labels.lastMonth] = [
            moment().subtract(1, 'month').startOf('month'),
            moment().subtract(1, 'month').endOf('month')
        ];
        ranges[labels.thisYear] = [moment().startOf('year'), moment().endOf('year')];

        // Default locale settings
        var defaultLocale = {
            format: window.js_date_format || 'YYYY-MM-DD',
            separator: ' - ',
            applyLabel: window.label_apply || 'Apply',
            cancelLabel: window.label_cancel || 'Cancel',
            fromLabel: window.label_from || 'From',
            toLabel: window.label_to || 'To',
            customRangeLabel: labels.customRange,
            weekLabel: 'W',
            daysOfWeek: moment.weekdaysMin(),
            monthNames: moment.monthsShort(),
            firstDay: moment.localeData().firstDayOfWeek()
        };

        // Merge locale with config
        var locale = $.extend({}, defaultLocale, config.locale || {});

        // Daterangepicker options
        var options = {
            autoUpdateInput: false,
            autoApply: config.autoApply || false,
            showDropdowns: config.showDropdowns !== false,
            linkedCalendars: true,
            showCustomRangeLabel: true,
            alwaysShowCalendars: true,
            ranges: ranges,
            locale: locale,
            opens: config.opens || 'left'
        };

        // Add max span if provided
        if (config.maxSpan) {
            options.maxSpan = {
                days: config.maxSpan
            };
        }

        // Add min/max dates if provided
        if (config.minDate) {
            options.minDate = config.minDate;
        }
        if (config.maxDate) {
            options.maxDate = config.maxDate;
        }

        // Initialize daterangepicker
        $input.daterangepicker(options, function (start, end, label) {
            // This callback is called when date is selected
            if (config.callback && typeof config.callback === 'function') {
                config.callback(start, end, label);
            }
        });

        // Handle apply event
        $input.on('apply.daterangepicker', function (ev, picker) {
            var startDate = picker.startDate.format('YYYY-MM-DD');
            var endDate = picker.endDate.format('YYYY-MM-DD');

            // Update hidden fields
            $hiddenFrom.val(startDate);
            $hiddenTo.val(endDate);

            // Update visible input with formatted date
            $(this).val(
                picker.startDate.format(locale.format) +
                locale.separator +
                picker.endDate.format(locale.format)
            );

            // Refresh table if tableId provided
            if (tableId && $('#' + tableId).length) {
                $('#' + tableId).bootstrapTable('refresh');
            }

            // Trigger custom event
            $(this).trigger('daterange:applied', [startDate, endDate]);
        });

        // Handle cancel event
        $input.on('cancel.daterangepicker', function (ev, picker) {
            // Clear all fields
            $(this).val('');
            $hiddenFrom.val('');
            $hiddenTo.val('');

            // Reset picker to current date
            picker.setStartDate(moment());
            picker.setEndDate(moment());

            // Refresh table if tableId provided
            if (tableId && $('#' + tableId).length) {
                $('#' + tableId).bootstrapTable('refresh');
            }

            // Trigger custom event
            $(this).trigger('daterange:cancelled');
        });

        // Handle show event to add custom classes
        $input.on('show.daterangepicker', function (ev, picker) {
            // Add custom class for styling if needed
            picker.container.addClass('advanced-daterange-picker');
            console.log('Advanced Date Filter: Daterangepicker shown for ' + config.selector);
        });

        var picker = $input.data('daterangepicker');
        console.log('Advanced Date Filter: Successfully initialized ' + config.selector + ' with ' + Object.keys(ranges).length + ' preset ranges');

        return picker;
    };

    /**
     * Initialize multiple date range pickers at once
     *
     * @param {Array} configs - Array of configuration objects
     */
    window.initMultipleDateRangePickers = function (configs) {
        if (!Array.isArray(configs)) {
            console.error('Advanced Date Range Filter: configs must be an array');
            return;
        }

        var pickers = [];
        configs.forEach(function (config) {
            var picker = window.initAdvancedDateRangePicker(config);
            if (picker) {
                pickers.push(picker);
            }
        });

        return pickers;
    };

    /**
     * Clear all date range filters on a page
     *
     * @param {string} prefix - Prefix for filter IDs (e.g., 'task', 'project')
     */
    window.clearDateRangeFilters = function (prefix) {
        var filterTypes = ['date_between', 'start_date_between', 'end_date_between'];

        filterTypes.forEach(function (type) {
            var inputId = '#' + prefix + '_' + type;
            var fromId = '#' + prefix + '_' + type.replace('_between', '_from');
            var toId = '#' + prefix + '_' + type.replace('_between', '_to');

            var $input = $(inputId);
            if ($input.length && $input.data('daterangepicker')) {
                var picker = $input.data('daterangepicker');
                $input.val('');
                $(fromId).val('');
                $(toId).val('');
                picker.setStartDate(moment());
                picker.setEndDate(moment());
            }
        });
    };

    /**
     * Get current date range values
     *
     * @param {string} selector - Selector for the input element
     * @returns {Object} Object with startDate and endDate
     */
    window.getDateRangeValues = function (selector) {
        var $input = $(selector);
        if (!$input.length || !$input.data('daterangepicker')) {
            return null;
        }

        var picker = $input.data('daterangepicker');
        return {
            startDate: picker.startDate.format('YYYY-MM-DD'),
            endDate: picker.endDate.format('YYYY-MM-DD'),
            startDateFormatted: picker.startDate.format(picker.locale.format),
            endDateFormatted: picker.endDate.format(picker.locale.format)
        };
    };

    /**
     * Set date range values programmatically
     *
     * @param {string} selector - Selector for the input element
     * @param {string} startDate - Start date (YYYY-MM-DD)
     * @param {string} endDate - End date (YYYY-MM-DD)
     */
    window.setDateRangeValues = function (selector, startDate, endDate) {
        var $input = $(selector);
        if (!$input.length || !$input.data('daterangepicker')) {
            console.warn('Advanced Date Range Filter: Picker not found - ' + selector);
            return;
        }

        var picker = $input.data('daterangepicker');
        picker.setStartDate(moment(startDate));
        picker.setEndDate(moment(endDate));
        $input.val(
            moment(startDate).format(picker.locale.format) +
            picker.locale.separator +
            moment(endDate).format(picker.locale.format)
        );
    };

})(window);

