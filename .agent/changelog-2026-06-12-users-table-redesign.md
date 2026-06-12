# Changelog - Users Table & Filters Redesign

## [2026-06-12]
### Modified
- **`resources/views/users/users.blade.php`**:
  - Moved the table filters outside and above the table card.
  - Converted the three filters (Status, Roles, Email Verification Status) from Select2/standard selects to Tom Select by replacing the class `js-example-basic-multiple` / `form-control` with `form-select tom_static_select`.
  - Upgraded the filters row wrapper to use the standard design system classes: `row g-3 align-items-end tk-filter-row mb-3`.
- **`public/assets/js/pages/users.js`**:
  - Configured `TableFilterSync` to synchronize the user list filters using the `tom-select` filter type instead of `select2`.
  - Set `ajaxType: null` for the roles filter, as options are statically loaded via Blade.
- **`app/Http/Controllers/UserController.php`**:
  - Refactored the `list` method to render a Bootstrap vertical three-dot (`bx-dots-vertical-rounded`) action dropdown menu instead of raw inline icons, matching the standard system design.
