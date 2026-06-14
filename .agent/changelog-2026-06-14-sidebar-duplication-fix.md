# Changelog - Sidebar Duplication Fix

## [2026-06-14]
### Removed
- **`resources/views/components/menu.blade.php`**:
  - Removed the `<div class="tk-rail-foot">` container entirely from the bottom of the `.tk-rail` navigation sidebar.
  - This removes the duplicate User profile avatar (which is already displayed in the top-right navbar dropdown) and the duplicate Preferences settings gear icon (which is already represented by the main Settings category icon in the sidebar rail).
