# Changelog - Input Hover & Focus Styles Unification

This log details the unification of hover and focus styles for all form input controls, select dropdowns, search boxes, and file input selectors across the application to conform to Option A (Minimalist/Muted gray border style, no outline, no box-shadow, and light-gray hover states).

## [2026-06-15] - Input Hover & Focus Standardization

### Modified
- **`public/assets/css/tk-design-system.css`**
  - Updated `.tk-input`, `.tk-select`, `.tk-textarea`, `.tk-inputgroup` hover style (`var(--line-2)`) and focus style (`var(--fg-1)`, outline: none, box-shadow: none).
  - Updated bootstrap-table search input (`.tk-table .fixed-table-toolbar .search input` and `.form-control`) hover style (`var(--line-2)`) and focus style (`var(--fg-1)`, outline: none, box-shadow: none).
- **`public/assets/css/custom.css`**
  - Added hover styles (`var(--line-2)`) for Select2 containers (`.select2-selection--single` and `.select2-selection--multiple`).
  - Added hover style (`var(--line-2)`) and updated focus style (`var(--fg-1)`, outline: none, box-shadow: none) for global filter bar inputs.
  - Added hover style (`var(--line-2)`) and updated focus style (`var(--fg-1)`, outline: none) for V2 panel search inputs.
  - Added custom styling for `input[type="file"]` controls and their inner `::file-selector-button` to align with the V2 design system hover and color aesthetics.
