# Changelog

All notable changes to the Taskify project will be documented in this folder.

## [2026-06-11] - Lead View Header Redesign
### Modified
- **`resources/views/leads/show.blade.php`**: Redesigned the header and breadcrumb section to conform to the new design system (matched with `projects.blade.php`). Removed `ol > li` based breadcrumbs in favor of `nav > a/span` structure and updated gap spacing classes. Removed duplicate 'Create Follow-up' button in the empty state. Fixed dark-mode empty state background bug. Added `modal-dialog-centered` to follow-up modals to align with the design system. Refined profile card typography and button alignments to perfectly center the contact action buttons using `btn-icon` and `rounded-circle`.
- **`resources/views/leads/edit.blade.php`**: Converted old breadcrumb layout to match the new design system and added null checks (`@if($lead->...)`) for lead source, stage, and assigned user select fields to prevent view crashes and unstyled output when relationship data is missing.
- **`resources/views/settings/general_settings.blade.php`**: Performed a complete UI overhaul to match the new 2-column bordered-panel design standard. Replaced hardcoded `bg-white` panel classes with theme-aware `card mb-3` and `card-body pt-3` classes to resolve stark white backgrounds in dark mode while subtly decreasing spacing. Moved the "Footer Text" field out of the basic info panel and into its own dedicated full-width panel at the bottom of the page. Fixed save button stretching by removing `flex-grow-1` and aligning them to the right. Refined the "Initialize Balances" button into a professional inline grey container. Added left margins (`ms-1`) to all tooltip icons to prevent them from crashing into text labels.
- **`resources/views/settings/company_info_settings.blade.php`**: Overhauled layout to match the new design system standard. Shifted breadcrumbs to the right and page title to the left. Replaced the outdated generic card with a modern, full-width single-card layout (`col-12`) featuring a proper "Company Details" header. Merged the save actions (Update/Cancel buttons) directly into the bottom of the card for a cohesive, professional look. Replaced incorrect `form-check-label` classes with proper `form-label` classes.
- **`resources/views/settings/security_settings.blade.php`**: Restructured to match the standard design system. Swapped the basic card out for a full-width `col-12` card with a dedicated "Security Configuration" header. Added logical inner-dividers (`h6` tags with bottom borders) to group fields into Access Settings, Upload Settings, and Google reCAPTCHA Settings. Corrected form labels, spaced tooltips with `ms-1`, updated the reCAPTCHA alert to use a modern flex layout with an icon, and aligned save buttons properly to the bottom right.
- **`resources/views/settings/email_settings.blade.php`**: Applied the same professional full-width layout standard. Moved the alert block to a modern flex-box layout with an icon. Wrapped fields in a `col-12` card with a header icon, fixed breadcrumb layouts, and right-aligned action buttons without stretch.
- **`resources/views/custom_fields/index.blade.php` & `public/assets/js/pages/custom-fields.js`**: Standardized the Custom Fields page. Re-aligned the header to place the page title on the left and the breadcrumbs + "Add" button on the right. Moved the "Module" filter outside of the main table card and upgraded it to a rich `tom-select` dropdown. Converted the table action buttons to use the standard "three-dot" (vertical dots) dropdown menu component.

## [2026-06-10] - Tasks Status & Priority UI Standardization
### Modified
- **`app/Http/Controllers/TasksController.php`**: Converted the inline-editable `select` dropdowns for the Status and Priority columns in the Tasks list view to static read-only badges to match the data display pattern found in the Projects controller.

## [2026-06-10] - Save Column Visibility Feature
### Added
- **`resources/views/components/tags-card.blade.php`**: Implemented the "Save Column Visibility" feature by adding user preference injection, a hidden tracker input, and conditional `data-visible` attributes for the Tags table.
- **`resources/views/components/status-card.blade.php`**: Added "Save Column Visibility" functionality with conditional column rendering based on user preferences.
- **`resources/views/components/priority-card.blade.php`**: Integrated "Save Column Visibility" into the `<x-tk-table>` component to persist user column display preferences.
- **`resources/views/task_lists/index.blade.php`**: Added the "Save Column Visibility" button logic and user preference tracking for Task Lists.

## [2026-06-10] - Action Columns UI Standardization
### Modified
- **`app/Http/Controllers/TaskListController.php`**: Refactored the action buttons in the task list view into a clean three-dots dropdown menu, matching the project's standard pattern.
- **`app/Http/Controllers/TagsController.php`**: Replaced inline edit/delete action buttons in the list view with a dropdown menu featuring a vertical three-dots trigger icon, aligning with the project's design system pattern.
- **`app/Http/Controllers/StatusController.php`**: Standardized the list view actions column to use the three-dots dropdown menu pattern.
- **`app/Http/Controllers/PriorityController.php`**: Updated the list view actions column to match the three-dots dropdown menu pattern used in projects.
- **`app/Http/Controllers/TasksController.php`**: Refactored the inline task actions (edit, delete, duplicate, quick view) into a streamlined three-dots dropdown menu for improved UI consistency.

## [2026-06-10] - Design System Badges and Components
### Added
- **`resources/views/components/badges/badge.blade.php`**: Exposed the design system badge component with mapping logic to support standard Bootstrap colors as tones.
- **`resources/views/components/badges/chip.blade.php`**: Added the design system chip component supporting active and removable states.
- **`resources/views/components/badges/kbd.blade.php`**: Exposed the design system keyboard shortcut (`kbd`) component.
- **`resources/views/components/badges/status-pill.blade.php`**: Exposed the design system status-pill component.
- **`resources/views/components/badges/tag.blade.php`**: Exposed the design system tag component.

### Modified
- **`public/assets/css/custom.css`**: Added revamp classes for `.chip`, `.tag`, `.status`, `.kbd`, `.dot`, and `.dot-indicator` under `body.v2-shell`. Defined revamp design system badge tone styles (`.badge-primary`, `.badge-ok`, `.badge-warn`, `.badge-err`, `.badge-info`). Removed the custom layout overrides (padding, border-radius, font-weight) from standard success badges (`bg-success`, `bg-label-success`) to let them fallback to default design system dimensions (18px height, 3px border-radius, 0 6px padding).
- **`public/assets/css/table.css`**: Removed the legacy `body.v2-shell .bootstrap-table .badge` override block, ensuring badges inside tables correctly fallback to design-system standard sizes.
- **`resources/views/components/badges/status-pill.blade.php`**: Enhanced the status-pill Blade component to dynamically map dynamic database status color keys (`success`, `warning`, `danger`, `primary`) to standard revamp keys (`done`, `review`, `blocked`, `progress`).
- **`resources/views/projects/project_information.blade.php`**: Refactored the tags, user/client assignment check messages, and priority badges to use the `<x-badges.badge>` component, and converted the project status display to use the `<x-badges.status-pill>` component with automatic tone mapping.

## [2026-06-10] - Actions Column Dropdown Menu
### Modified
- **`app/Http/Controllers/ProjectsController.php`**: Refactored the `actions` output in the `list` method to render a Bootstrap dropdown menu triggered by a three-dot menu icon with a custom class `project-actions-dropdown`, improving space and clean aesthetics.
- **`public/assets/js/custom.js`**: Enhanced the `.favorite-icon` and `.pinned-icon` AJAX success callbacks to dynamically toggle child icon classes (`bx-*`/`bxs-*`) and preserve static text labels (preventing verbose action tooltips from overwriting them) when clicked inside a dropdown menu.
- **`public/assets/css/table.css`**: Styled the actions three-dots dropdown trigger button as a light rounded block, refined the dropdown menu layout with rounded corners and soft box-shadows, styled dropdown list items to have margins and inset hover states, converted icons to neutral gray, and custom-styled the delete action with red text/icons and soft red hover backgrounds. Added explicit left-alignment and start-justified flex overrides on dropdown list items and nodes to bypass cell text-alignment inheritance.
- **`public/assets/css/custom.css`**: Overrote the success badge overrides (`bg-success`, `bg-label-success`) to use a modern emerald green palette (`#28c76f` text, soft `12%` opacity background) and spacious styling (`6px` padding, `6px` border-radius) matching the reference design.

## [2026-06-10] - Table Header and Footer Consistency
### Modified
- **`resources/views/layout.blade.php`**: Added a cache-buster query parameter to the `bootstrap-table.min.css` stylesheet link to prevent browsers from loading cached versions of the stylesheet.
- **`public/assets/css/bootstrap-table.min.css`**: Replaced all native `#dee2e6` dark borders with `var(--line)` to ensure consistency with the design system.
- **`public/assets/css/table.css`**: Configured last-row table cells to have a light brown bottom border (`border-bottom: 1px solid var(--line) !important`) and set pagination `border-top` to 0. Added global resets to remove any potential default dark wrapper borders/box-shadows.
- **`public/assets/css/custom.css`**: Updated last-row table cells to use `border-bottom: 1px solid var(--line) !important` and set pagination `border-top` to 0.
- **`public/assets/css/tk-design-system.css`**: Updated `.tk-table` last-row td borders to use `border-bottom: 1px solid var(--line) !important`.

## [2026-06-09] - Table Aesthetics Refinement
### Modified
- **`public/assets/css/table.css`**: Refined the table design system to match a cleaner, more spacious aesthetic. Removed `table-layout: fixed` to eliminate unwanted horizontal scrollbars. Removed the messy double-line border from the toolbar. Updated header typography to use normal case fonts. Adjusted toolbar alignment using flexbox to perfectly align search and refresh buttons to the right. Scaled down overall font sizes (13px), paddings, checkboxes, and buttons to maintain consistency with the compact design system. Fixed default blue link colors in table cells so project titles inherit the strong text color (`var(--fg-0)`) instead of looking out of place.
- **`app/Http/Controllers/ProjectsController.php`**: Cleaned up the table's "Title" column by extracting the quick action buttons (Favorite, Pin, Discussions) and moving them into the dedicated "Actions" column at the end of the table.

## [2026-06-09] - Table Layout Redesign & Restoration
### Added/Modified
- **`resources/views/components/tk-table.blade.php`**: Restored and improved the Blade component for all project tables.
- **`public/assets/css/table.css`**: Completely rebuilt the table design system to enforce a fixed-width layout (`table-layout: fixed`), remove ugly default Bootstrap borders, and properly align the toolbar using Flexbox so the search bar and action buttons are perfectly aligned on a single row.

## [2026-06-09] - Table Badge Styling
### Modified
- **`public/assets/css/table.css`**: Added a global `.badge` override for the bootstrap-table to ensure all badges (like status and priority) have proper spacing (`padding: 6px 12px`), rounded corners (`border-radius: 6px`), and font styling to match professional badge designs.

## [2026-06-09] - Badge CSS Fixes
### Fixed
- **`public/assets/css/custom.css`**: Fixed the text color for `.bg-label-primary` to properly match the soft background (colored text instead of dark text).
- **`app/Http/Controllers/ProjectsController.php`**: Updated "Not Assigned" badges to use the `.bg-label-primary` class for a softer, more professional appearance.

## [2026-06-09] - Project List View Update
### Modified
- **`app/Http/Controllers/ProjectsController.php`**: Removed inline dropdown selects for Status and Priority columns in the project list table (`list` API method) and replaced them with static badges as requested, ensuring data is displayed cleanly without unintended side effects.

## [2026-06-09] - Table UI Redesign
### Modified
- **`public/assets/css/table.css`**: Completely redesigned the table styles to match the new clean, modern look. Removed heavy borders, updated typography, added status badge classes, and refined checkbox and pagination styling.

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

# #   R e d e s i g n   P r o j e c t s   G r i d   V i e w \ n -   R e d e s i g n e d   
 e s o u r c e s / v i e w s / p r o j e c t s / g r i d _ v i e w . b l a d e . p h p   t o   u s e   t h e   n e w   d e s i g n   s y s t e m   s t y l e s   w i t h   m o d e r n   C S S   G r i d   a n d   . c a r d   s t y l i n g . \ n 
 
 ## Tom Select Filter Update
- Replaced Select2 with Tom Select for filters in `grid_view.blade.php` and `kanban.blade.php`.
- Implemented auto-submit filtering functionality on change, removing the manual filter button.

## Kanban Card and TomSelect Dark Mode Fixes
- Fixed Tom Select dropdown styling to display correctly in dark mode.
- Updated Kanban project cards to display Start Date, Tasks count, and Client avatars.

## Kanban Footer Dark Mode Fix
- Updated CSS to remove the white background from the Kanban footer and styled the 'Create Project' button appropriately for dark mode.

- Fixed 'Create Project' button text and icon alignment to be perfectly centered.

## [2026-06-09] - Layout & Dashboard Legacy CSS Cleanup
### Removed
- **`resources/views/dashboard.blade.php`**: Deleted the unused, hidden `Filter Card` and `<x-dashboard.statistics>` Blade component to clean up the DOM since the new toolkit SVGs fetch their own data directly.
- **`public/assets/css/custom.css`**: Removed CSS overrides hiding the legacy dashboard cards (`#project-statistics`, `#task-statistics`, etc.) since the DOM elements are no longer generated. Removed legacy `#dashboard-items` and `.draggable-item` styling. Kept necessary design system layout wrappers (`body.v2-shell .layout-page`).

## [2026-06-09] - Project Page UI Refactoring
### Modified
- **`resources/views/projects/project_information.blade.php`**: Stripped the obsolete Bootstrap `.card` and `.card-body` wrappers from within the `.tk-dock-body` offcanvas to ensure content renders flush and natively within the Graphite Studio design system (fixes white background bugs in dark mode). Converted `.nav-tabs` to design-system styled `.tk-tabs`.
- **`resources/views/projects/projects.blade.php`**: Upgraded legacy `.breadcrumb-style1` to the new design system `<nav class="breadcrumb">` component. Replaced `.badge-primary` with `.badge bg-primary`.
- **`resources/views/projects/grid_view.blade.php`**: Standardized breadcrumb structure and badges to match the new design system.
- **`resources/views/projects/kanban.blade.php`**: Standardized breadcrumb structure and badges to match the new design system.
- **`public/assets/css/custom.css`**: Removed the obsolete `z-index` rule for the legacy `#edit_project_modal`.
- **`public/assets/css/custom.css`**: Removed 160+ lines of dead CSS overrides that targeted `.card` wrappers inside `#project_detail_panel` (since the `.card` wrappers were deleted in the previous step).
- **`public/assets/css/custom.css`**: Removed redundant utility classes (`.m-0`, `.sr-only`, `.no-margin-p`, `.no-shadow`, `.h-2vh`, `.top-13`) that are natively handled by Bootstrap 5 or design system tokens.
- **`resources/views/estimates-invoices/view.blade.php`**: Replaced the custom `.no-margin-p` class with Bootstrap's native `.mb-0`.
- **`resources/views/components/dashboard/calendar-tab.blade.php`**: Replaced the custom `.no-shadow` class with Bootstrap's native `.shadow-none`.

## [2026-06-10] - Project Details Offcanvas Refactoring
### Modified
- **`resources/views/projects/project_information.blade.php`**: Converted the custom docked aside panel (`.tk-detail-dock`) into a standard Bootstrap offcanvas component (`.offcanvas.offcanvas-end`) to align with the core design system. Removed the task statistics chart and reorganized structural layout tags for cleaner code.

## [2026-06-10] - Offcanvas Details UI Polish
### Modified
- **`resources/views/projects/project_information.blade.php`**: Polished the project details offcanvas. Replaced the oversized `<h2>` title with a more compact `<h5>` and aligned the action icons. Standardized the Description and Additional Fields headers. Corrected checkbox layout and sizing, applying form-control-sm and form-select-sm where appropriate.

### Modified
- **`resources/views/projects/project_information.blade.php`**: Removed direct editing dropdowns for Status and Priority in the offcanvas, converting them to read-only badges. Replaced standard Boxicons with design system `<x-tk-icon>` components for Edit, File, Note, and Info icons for consistency.

## [2026-06-10] - Submenu Icons and Menu Search Improvements
### Added
- Added icons to all sidebar submenus inside `app/Services/MenuService.php`.
- Added keyboard shortcut `/` to focus the sidebar menu search.
- Highlighted search query in sidebar search results.
### Modified
- **`resources/views/components/menu.blade.php`**: Rendered submenu icons and updated the menu search input layout to use a native clear icon.
- **`public/assets/js/custom.js`**: Replaced legacy search logic with v2 search logic supporting text highlighting and shortcut.
