# Changelog - Interviews Table & Filters Redesign

## [2026-06-12]
### Modified
- **`resources/views/components/interview-card.blade.php`**:
  - Moved the table filters outside and above the table card.
  - Converted Sort select from Select2/standard select to Tom Select by replacing the class `js-example-basic-multiple` with `form-select tom_static_select` and adding a label.
  - Converted Status select to Tom Select by using the class `form-select tom_static_select` and adding a label.
  - Wrote Date Range filter manually with a standard `<label class="form-label">` to ensure all filters on the filter row align perfectly on the bottom.
  - Added a "Clear Filters" button to reset all filters.
  - Migrated the raw `<table>` element to the system-wide `<x-tk-table>` component and wrapped it in standard card classes (`card border shadow-none > card-body p-0`).
- **`public/assets/js/pages/interview.js`**:
  - Configured `TableFilterSync` to synchronize the interview list filters using the `tom-select` filter type instead of `select`.
  - Added a click event listener on `.clear-interview-filters` to clear the filters (including the date range picker, sort selection, and status selection) and refresh the bootstrap table.
