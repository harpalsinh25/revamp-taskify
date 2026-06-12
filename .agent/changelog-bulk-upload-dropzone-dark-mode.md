# Changelog - Bulk Upload Dropzone Dark Mode Fix

### Changed
- **`public/assets/css/custom.css`**:
  - Added comprehensive Dropzone.js theme-aware overrides to `custom.css`.
  - Overrode the hardcoded `background: #fff` and `border: 2px solid rgba(0,0,0,0.3)` from `dropzone.min.css` using `!important` rules.
  - Set `.dropzone` and `.bulk-upload-dropzone` to use `var(--bg-1)` for background and `var(--line-2)` for the dashed border in both light and dark themes.
  - Added hover / drag-over states that shift border to `var(--bs-primary)` and background to `var(--bg-2)`.
  - Styled inner `.dz-message`, `.dz-button`, file preview chips (`.dz-filename`, `.dz-size`) and progress bar using design system variables.
  - Added dark-mode specific `html[data-theme="dark"]` selectors for stronger cascade override.
  - Defined `.border-dashed` as a reusable utility class with `2px dashed var(--line-2)` border.
