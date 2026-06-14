# Changelog - Primary Color PWA Sync

This log details the synchronization of the database primary color to `localStorage` on load to fix PWA cache color flashing.

## [2026-06-14] - Primary Color PWA Sync

### Modified
- **`resources/views/layout.blade.php`**
  - Updated the inline `<script>` head block to always write the server-side `$general_settings['primary_color']` value into `localStorage.getItem("taskify.primaryColor")` on every database-sourced page load. This guarantees that pages served from the PWA/service-worker offline caches immediately fetch the latest custom primary color on refresh rather than rendering with outdated/green templates.
