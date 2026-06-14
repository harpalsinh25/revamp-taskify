# Changelog - Dark Mode Styling Fixes (Social Scheduler & Notifications Dropdown)

## [2026-06-14]
### Fixed
- **`public/assets/css/social/social.css`**:
  - Added specific dark mode overrides (`[data-theme="dark"]`) for platform selector cards, accordion items, accordion headers, accordion bodies, and the post preview container.
  - Resolved white-on-white text/icon visibility issues on the platform selector cards by ensuring they adopt `var(--bg-2)` as their background in dark mode.
  - Mapped accordion buttons and items to the correct semantic design-system variables (`var(--bg-1)` and `var(--bg-2)`).
- **`public/assets/js/social/social.js`**:
  - Integrated dynamic dark mode styling for the TinyMCE iframe editor text area.
  - Added a `MutationObserver` on the `html` element to dynamically update the TinyMCE editor styling (background and text color) when the user toggles themes.
- **`public/assets/css/custom.css`**:
  - Updated the global `.fixed-header` and `.fixed-footer` backgrounds to `var(--bg-1, white) !important`. This fixes the notifications dropdown header and footer styling in dark mode (making them dark instead of remaining stark white).
  - Aligned `.badge-notifications` correctly relative to `.tk-icon-btn` and added a premium `border` cutout matching `--bg-0` to prevent badge overlap.
  - Styled `.dropdown-menu` and items within the notifications dropdown to apply premium border-radius, background, custom signals for icons (`bx-bell` styling), and design system typography.
