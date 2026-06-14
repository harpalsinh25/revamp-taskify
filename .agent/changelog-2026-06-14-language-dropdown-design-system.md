# Changelog - Language Dropdown Redesign & Polish

## [2026-06-14]
### Modified
- **`resources/views/partials/_navbar.blade.php`**:
  - Polished the language switcher dropdown list to conform to the premium design system.
  - Replaced native checkbox squares (`bx-square` / `bx-check-square`) with a clean `bx bx-check` checkmark icon on the right side of the active language, colored dynamically with the theme primary color (`var(--signal)`).
  - Aligned language list items using Bootstrap flexbox `justify-content-between` to position checkmarks on the right.
  - Updated the "Primary" and "Set as primary" indicators at the bottom to use standard `.tk-badge` and `.tk-badge-primary` classes from the design system helper, replacing outdated Bootstrap native background styles.
