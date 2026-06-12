# Changelog - Users Form Radio Selections Fix

## [2026-06-12]
### Fixed
- **`resources/views/users/create_user.blade.php`**:
  - Implemented dynamic checked values using the `old()` helper for the Active/Deactive (status) and Yes/No (require_ev) radio fields.
  - Standardized the wrapper class of radio button groups to `btn-group d-flex w-100` for proper layout formatting.
- **`resources/views/users/edit_user.blade.php`**:
  - Implemented dynamic checked values using the `old()` helper (retaining database values as fallbacks) for the Active/Deactive (status) radio fields.
  - Standardized the wrapper class of status radio button group to `btn-group d-flex w-100` for proper layout formatting.
