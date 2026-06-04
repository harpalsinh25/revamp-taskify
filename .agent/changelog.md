# Changelog

All notable changes to the Taskify project will be documented in this folder.

## [2026-06-03] - Sidebar UI Redesign
### Added
- Appended v2 Graphite Studio design tokens (OKLCH color space, radius, typography, transitions) and sidebar layout CSS rules (`.rail` and `.panel`) to `public/assets/css/custom.css`.
- Added CSS overrides for `.panel .menu-inner`, `.panel .menu-item`, `.panel .menu-link`, and `.panel .menu-sub` to style collapsible list navigation.
- Configured media breakpoint overrides to align the layout page margins to `288px` (56px rail + 232px panel) for seamless double-sidebar desktop views.

### Modified
- Refactored `resources/views/components/menu.blade.php` to implement the Toolkit double-sidebar UI (rail + panel).
  - Maintained all existing backend logic (`$groupedMenus`, permissions, badges, workspaces).
  - Integrated `.rail` to show top-level category icons and a workspace switcher / user profile dropdown at the bottom (`.rail-foot`).
  - Integrated `.panel` to dynamically render categories, menus, and submenus.
  - Used exact custom CSS classes provided by Taskify Revamp kit (`.rail-btn`, `.panel-item`, `.panel-sub-list`, `.workspace-selector`) to ensure proper rendering without modifying backend routes.

## [2026-06-03] - Sidebar Revamp Implementation
### Added
- Added `.double-sidebar-wrapper`, `.rail`, and `.panel` styles to `public/assets/css/custom.css`.
- Extracted and ported Design Tokens from Revamp Kit `tokens.css`.
### Modified
- Overwrote `menu.blade.php` to implement the rail and context-panel layout.
- Determined the active category using URL parsing (`request()->url()`).
- Relocated the workspace switcher dropdown to the `.panel-head`.
- Categories now output as `.rail-btn` icons, mapping `?active_category` logic for selection.
