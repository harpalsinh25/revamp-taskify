# Changelog

All notable changes to the Taskify project will be documented in this folder.

## [2026-06-05] - Fix Task Date Range Picker Initialization
### Modified
- **`tasks-card.blade.php`**: Wrapped script tags at the bottom of the component inside `@section('page_scripts')` and `@endsection` to ensure they execute after `custom.js` and `dynamic_table_filter_manager.js` load, resolving initialization timing issues with `TableFilterSync` and the date range picker.
- **`board_view.blade.php`**: Wrapped status array and task-board.js script tags inside `@section('page_scripts')` and `@endsection` to guarantee they run after layout scripts, fixing the ReferenceError for dragula and preventing picker initialization blockages.
- **`group_by_task_lists.blade.php`**: Wrapped group-by-task-lists.js script tag inside `@section('page_scripts')` and `@endsection` to align execution order.
- **`task_information.blade.php`**: Wrapped task-information.js and delete variables script tags inside `@section('page_scripts')` and `@endsection` to ensure dependencies load beforehand.

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

## [2026-06-04] - Dashboard Module Redesign
### Modified
- **`calendar-tab.blade.php`**: Replaced solid blue pill buttons with clean, underlined standard tabs. Added custom styling to match the provided Revamp Kit image (green/yellow underline for active states depending on the parent tab context).
- **`upcoming-birthdays-card.blade.php`**: Converted `.tk-filter-bar` to a flexbox layout (`d-flex flex-wrap gap-3`) to eliminate negative margins, ensuring the filter inputs correctly align and match the exact width of the data table beneath them.
- **`upcoming-work-anniversaries-card.blade.php`**: Applied identical flexbox alignment and width corrections for the filter bar.
- **`members-on-leave-card.blade.php`**: Applied identical flexbox alignment and width corrections for the filter bar.
- **`priority-card.blade.php`**: Replaced the standard HTML `<table data-toggle="table">` with the `<x-tk-table>` component and removed redundant `.table-responsive` wrappers to ensure the page accurately adopts the Revamp Kit design system, specifically fixing the white card background issue in dark mode.
- **`tabs.blade.php` (Dashboard)**: Removed the `.nav-align-top` wrapper and added `.bg-transparent .shadow-none` to the `.tab-content`. This eliminates the massive white "card" background wrapper rendering incorrectly behind the Upcoming Birthdays/Anniversaries/Leave sections in dark mode.

## [2026-06-04] - UI Style Improvements & Consistency
### Modified
- **`custom.css`**:
  - Overhauled Tom Select styles to use flex centering on selected items (`.item`) and tag removal buttons (`.remove`) to ensure the "x" sits perfectly in the center.
  - Custom-styled dropdown highlighting in Tom Select (`.ts-dropdown .option.active`) to use design system token `var(--bg-2)` instead of Bootstrap default blue.
  - Standardized `.form-control` and `.form-select` styling under `body.v2-shell` (background, borders, placeholder colors, border-radii, and focus outline/box-shadow rings) to guarantee input visual consistency across the entire app.
  - Reset styles on `.ts-wrapper` elements inheriting `.form-select` or `.form-control` to prevent outer "double borders" (field inside of a field) and ensure consistent margins/paddings.
  - Implemented interactive border transition styling on hover (`var(--line-2)`) for inputs, selects, and Tom Select fields.
  - Reset wrapper borders and margins on `.input-group` and `.input-group-merge` to resolve styling and alignment conflicts on standard form inputs (e.g. number inputs).
  - Explicitly aligned and standardized placeholder text color (`var(--fg-3)`) and opacity (`1`) globally for all input, select, and form control placeholders.
  - Enforced exact vertical alignment and a height of 32px on standard inputs and Tom Select controls by resetting vertical padding on `.ts-control` and adding strict `padding-top: 0`, `padding-bottom: 0`, and `line-height: 30px` rules to `.form-control` and `.form-select`.
  - Added strict `box-sizing: border-box !important` globally on `.form-control`, `.form-select`, and `.ts-control` to ensure browser height declarations include borders and padding consistently.

