# Changelog - Users Duplicate Clear Filters Button Fix

## [2026-06-12]
### Fixed
- **`resources/views/users/users.blade.php`**:
  - Removed the duplicate manual "Clear Filters" button markup, relying entirely on the automated filter button injection logic from `custom.js`.
  - Preserved the `col-md-3` column widths for the three dropdown filters, allowing the dynamically generated filter button to align perfectly alongside them.
