# Changelog - Country Code & Phone Number Overlap Fix

## [2026-06-12]
### Fixed
- **`public/assets/css/custom.css`**:
  - Excluded `#phone` from global `.form-control` and `.input-group .form-control` styling rules that override padding properties using `!important`.
  - Added specific styling rule block for `#phone` in the `body.v2-shell` layout context, preserving dynamic padding control for `intl-tel-input`.
  - Excluded `#phone` from standard focus and group-merge focus rules overriding padding properties.
  - Adjusted `.iti input` general styles to omit hardcoded `padding-left: 84px !important`, letting `intl-tel-input` dynamically calculate start position spacing based on selected country code.
