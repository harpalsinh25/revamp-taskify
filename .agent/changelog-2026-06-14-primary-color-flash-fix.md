# Changelog - Primary Color Flash Fix

This log details the fix for the primary color flashing default purple style on page load/refresh.

## [2026-06-14] - Primary Color Flash Fix

### Modified
- **`resources/views/layout.blade.php`**
  - Updated the inline head `<script>` block to parse the primary color hex to its RGB values, and set `--bs-primary` and `--bs-primary-rgb` CSS variables on `document.documentElement` before the document renders. This prevents the default Bootstrap/Sneat theme primary color (`#696cff` / purple) from flashing prior to JavaScript loading.
- **`resources/views/settings/general_settings.blade.php`**
  - Updated the "Reset" button click handler and the color picker `"input"` event live-preview handler to update `--bs-primary` and `--bs-primary-rgb` alongside `--signal` to ensure live styling previews are consistent.
