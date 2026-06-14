# Changelog - Dashboard Welcome Card Gradient Color & Dynamic Primary Color Fixes

## [2026-06-14]
### Modified
- **`public/assets/css/custom.css`**:
  - Replaced the hardcoded green oklch color for `--signal-glow` on `:root` (around line 3276) with a dynamic `color-mix(in srgb, var(--signal) 35%, transparent)`. This forces the dashboard welcome card gradient (which uses `--signal-glow`) to dynamically align with the primary color configured in General Settings.
  - Replaced the hardcoded green oklch color for `--signal-soft` on `:root` (around line 3318) with a dynamic `color-mix(in srgb, var(--signal) 12%, var(--bg-1))`, which dynamically shifts the background of active chips and progress status elements based on the theme and primary color.
  - Removed the hardcoded `--signal-soft` override in the `[data-theme="dark"]` section (around line 3328) so that dark mode dynamically inherits the root `color-mix` definition.
  - Updated the dark mode calendar cell highlight background `html[data-theme="dark"] body.v2-shell .fc-day-today` (around line 8704) to use `var(--signal-glow)` instead of a hardcoded green color.
