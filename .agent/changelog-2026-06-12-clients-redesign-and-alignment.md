# Changelog - Client Management Redesign & Alignment

## [2026-06-12]
### Modified
- **`resources/views/clients/clients.blade.php`**:
  - Moved the table filters into their own card container (`card mb-4` with `card-body`) at the top of the page.
  - Converted filter inputs to `tom-select` static components using class `tom_static_select`.
  - Upgraded table layout wrapper to flat, border-only minimalist card (`card border shadow-none` with `card-body p-0`).
  - Fixed data table ID from hyphenated `data-table` to `data_table`.
- **`public/assets/js/pages/clients.js`**:
  - Reconfigured `TableFilterSync` to synchronize status, internal purpose, and email verification filters using `tom-select` instead of `select2`.
  - Disabled AJAX mapping (`ajaxType: null`) for internal purpose filters.
- **`app/Http/Controllers/ClientController.php`**:
  - Converted table action column buttons to standard Bootstrap vertical dots (`bx-dots-vertical-rounded`) actions dropdown.
- **`resources/views/clients/create_client.blade.php`**:
  - Restructured profile upload field to support standard dashed outline box styling and live preview container.
  - Converted Active/Deactive and Email Verification button groups to standard Bootstrap inline check radios (`form-check-input`) with `old()` fallback values.
  - Right-aligned bottom action buttons and added dynamic file change listener script.
- **`resources/views/clients/update_client.blade.php`**:
  - Restructured profile upload field to support standard dashed outline box styling and live preview container (renamed file input ID to `profile`).
  - Converted Active/Deactive and Email Verification button groups to standard Bootstrap inline check radios (`form-check-input`) with default checked values.
  - Right-aligned bottom action buttons and added dynamic file change listener script.
