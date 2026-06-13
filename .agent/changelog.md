# Changelog

All notable changes to the Taskify project will be documented in this folder.

## [2026-06-13] - Email & Email Template Standardization
### Modified
- **`resources/views/components/email-history-card.blade.php`** & **`resources/views/components/email-templates-card.blade.php`**:
  - Swapped raw `<table>` with the modern `<x-tk-table>` blade component.
  - Wrapped tables in `<div class="card border shadow-none">` to follow the flat border system guidelines.
- **`public/assets/js/custom.js`**:
  - Fixed `TomSelect` initialization for `.statusDropdown` and `.priorityDropdown` by removing `controlInput: null` to restore search functionality.
  - Removed `class="item"` from the custom `item` render function to prevent `TomSelect` from erroneously applying an inner gray box/border around the selected badge.
- **`public/assets/css/custom.css`**:
  - Nullified `border`, `background`, and `box-shadow` on `body.v2-shell .ts-control input` to fix the double-border "overlapping" visual bug where the internal search input inherited `form-control` styles.
- **`resources/views/offcanvas.blade.php`**:
  - Standardized `.statusDropdown` wrapping classes to use `.form-select` consistently with `.priorityDropdown` for proper `TomSelect` alignment.
- **`app/Http/Controllers/EmailSendController.php`** & **`app/Http/Controllers/EmailTemplateController.php`**:
  - Updated actions columns to use the standard Bootstrap vertical three-dots dropdown menu (`bx-dots-vertical-rounded`).
- **`public/assets/js/pages/email-history.js`**:
  - Refactored `emailHistoryActionsFormatter` to return the standard UI actions button layout for previewing history.
- **`resources/views/modals.blade.php`**:
  - Converted `createTemplateModal` to `createTemplateOffcanvas` (`.offcanvas .offcanvas-end`) for standardized sliding form views.
  - Converted `editEmailTemplateModal` to `editEmailTemplateOffcanvas`.
  - Cleaned up `#previewModal` to match the system design standards by removing hardcoded `bg-light` borders, custom paddings, and `text-primary` classes.
- **`resources/views/email/send.blade.php`**:
  - Fixed dark mode UI layout bugs by removing legacy `.nav-align-top` and `.nav-tabs-shadow` wrappers to eliminate stark white backgrounds.
  - Converted the internal attachment and delivery options sections to use standard flat borderless `.card.border.shadow-none` classes, dropping the hardcoded `.bg-label-primary` headers.
  - Changed `.modal('show')` syntax to `bootstrap.Offcanvas().show()` for email templates edit form.
- **`resources/views/email-templates/index.blade.php`** & **`resources/views/components/empty-state-card.blade.php`**:
  - Updated standard button targets from `data-bs-toggle="modal"` to `data-bs-toggle="offcanvas"` for the email template create triggers.

## [2026-06-13] - Task & Project Filter Design Consistency Fix
### Modified
- **`resources/views/components/tasks-card.blade.php`**:
  - Replaced `x-advanced-date-filters` component usage with inline date filter fields matching `project_information.blade.php` pattern.
  - Changed all `form-control` → `form-select form-select-sm` on select fields, `form-control-sm` on date inputs.
  - Added `card border shadow-none` + `card-body p-3` wrapping (matching project_information reference).
  - Added hidden fields for date range from/to values directly in the card (previously in the component).
  - **Removed the duplicate Clear Filters button** — it already exists in Bootstrap Table's toolbar.
- **`resources/views/components/projects-card.blade.php`**:
  - Same inline date filter expansion + sm-size controls + removed duplicate Clear Filters button.
  - Added proper hidden fields for date range values.

## [2026-06-13] - Task & Project Filter Row Layout Fix
### Modified
- **`resources/views/components/tasks-card.blade.php`**:
  - Changed all filter column classes from `col-md-4` to `col-md-3` — filters now render 4 per row (3 date + 5 dropdowns = 8 total, filling 2 clean rows of 4).
  - Passed `colClass="col-md-3"` to `x-advanced-date-filters` so date filters match.
  - Added a **Clear Filters** button (`col-md-3`) in the last slot of the filter row.
- **`resources/views/components/projects-card.blade.php`**:
  - Same `col-md-4` → `col-md-3` fix + `colClass="col-md-3"` for date filters.
  - Added **Clear Filters** button to project filter row as well.
- **`resources/views/components/advanced-date-filters.blade.php`**:
  - Removed `mb-3` from filter divs (spacing handled by parent `row g-3`).
  - Added a `<label>` above each date input for visual alignment with the dropdown filters.

## [2026-06-13] - Bulk Assign & Import via Excel Offcanvas Redesign
### Modified
- **`plugins/AssetManagement/Resources/views/assets/offcanvas.blade.php`**:
  - **Bulk Assign offcanvas**: Removed `w-50 style="max-width:..."` width hack → changed to `offcanvas-responsive`. Removed unnecessary `card > card-header > card-body` nesting. Restructured to flat sections with `h6` icons for "Assignment Details" and "Notes". Used `row g-3` for form fields. Standardized footer to `d-flex justify-content-end gap-2`.
  - **Import via Excel offcanvas**: Fixed invisible header text (removed `text-white`, `btn-close-white`) — added `border-bottom`. Removed `bg-light` from body. Replaced `card border-0 shadow-sm` with `card border shadow-none`. Replaced `alert-info border-0` with flat card instructions block. Replaced `bg-light border-top p-3` footer with standard `d-flex justify-content-end gap-2 mt-3`. **Removed `@dd($errors)` debug call** (critical bug fix). Standardized both download buttons to `btn-outline-secondary`.

## [2026-06-13] - Asset Module Create/Update Offcanvas UI Fix
### Modified
- **`plugins/AssetManagement/Resources/views/assets/offcanvas.blade.php`**:
  - Fixed broken two-column (`col-lg-8` / `col-lg-4`) layout inside Create/Update Asset offcanvas that caused the image section to overflow and appear misaligned.
  - Restructured both forms to a clean stacked single-column layout with the image upload card at the **top** (full-width).
  - Added proper section headers: Asset Information and Purchase Details with `row g-3 col-md-6` grid.
  - Used `general_settings['currency_symbol']` in the Purchase Cost input group.
  - All Tom Select selects use `tom_static_select` class; all IDs preserved for JS compatibility.
- **`plugins/AssetManagement/Resources/views/assets/index.blade.php`**:
  - Upgraded breadcrumb to new design system `nav > a / span` format.

## [2026-06-13] - Task View Toggles Standardization
### Modified
- **`resources/views/tasks/tasks.blade.php`**:
  - Upgraded breadcrumbs to use design system `<nav class="breadcrumb">` structure.
  - Replaced individual view toggles and create action buttons with the unified `.seg` segmented controller tab container.
- **`resources/views/tasks/board_view.blade.php`**:
  - Upgraded breadcrumbs to use design system `<nav class="breadcrumb">` structure.
  - Replaced view toggles with the unified `.seg` segmented controller tab container.
- **`resources/views/tasks/group_by_task_lists.blade.php`**:
  - Upgraded breadcrumbs to use design system `<nav class="breadcrumb">` structure.
  - Replaced view toggles with the unified `.seg` segmented controller tab container.
- **`resources/views/tasks/calendar_view.blade.php`**:
  - Upgraded breadcrumbs to use design system `<nav class="breadcrumb">` structure.
  - Replaced view toggles with the unified `.seg` segmented controller tab container.

## [2026-06-13] - Milestones, Media & Activity Log Redesign & UI Standardizations
### Modified
- **`resources/views/projects/project_information.blade.php`**:
  - Restructured the Milestones tab to include a clean header section with a title and "+ Add milestone" button.
  - Wrapped Milestone filter inputs inside a dedicated `.card` component with `row g-3 align-items-end tk-filter-row` layout utilizing 4-column grid spacing (`col-md-3`) to align filters in a single row.
  - Converted the status selector filter from Select2 (`js-example-basic-multiple`) to Tom Select (`tom_static_select`).
  - Wrapped the milestones bootstrap table container in a standard flat bordered card (`card border shadow-none`) with `p-0` body padding.
  - Restructured the Media tab to include a clean header section with a title and "+ Add Media" button on the right.
  - Wrapped the project media bootstrap table container inside a flat bordered card (`card border shadow-none`) with `p-0` body padding.
  - Restructured the Activity Log tab to include a clean header section with an "Activity log" title.
  - Wrapped Activity Log filter inputs inside a dedicated `.card` component with `row g-3 align-items-end tk-filter-row` layout utilizing 2-column grid spacing (`col-6`) to prevent label truncation.
  - Converted the User, Client, Activities, and Types filters in the Activity Log tab to use Tom Select (`tom_users_select`, `tom_clients_select`, and `tom_static_select`).
  - Wrapped the activity log bootstrap table container in a standard flat bordered card (`card border shadow-none`) with `p-0` body padding.
- **`public/assets/js/pages/project-information.js`**:
  - Updated the `.clear-milestones-filters` click handler to safely clear the status filter's Tom Select instance (`statusFilter.tomselect.clear()`) if it exists, falling back to triggering change event.
  - Updated the `.clear-activity-log-filters` click handler to safely clear Tom Select instances for User, Client, Activities, and Types filters.

## [2026-06-13] - Asset Module Redesign
### Modified
- **`plugins/AssetManagement/Controllers/AssetsController.php`**:
  - Updated actions column inside `list()` to use standard Bootstrap vertical three-dots dropdown menu (`bx-dots-vertical-rounded`), incorporating Edit, Duplicate, and Delete options.
- **`plugins/AssetManagement/Controllers/AssetsCategoryController.php`**:
  - Updated actions column inside `list()` to use standard Bootstrap vertical three-dots dropdown menu, incorporating Edit and Delete options.
- **`plugins/AssetManagement/Resources/views/assets/index.blade.php`**:
  - Swapped raw `<table>` with the modern `<x-tk-table>` blade component.
  - Wrapped table in `<div class="card border shadow-none">` to follow the flat border system guidelines.
  - Wrapped filter selectors inside a dedicated `<div class="card mb-4">` card wrapper.
- **`plugins/AssetManagement/Resources/views/assets/category/index.blade.php`**:
  - Swapped raw `<table>` with `<x-tk-table>` blade component.
  - Wrapped table in `<div class="card border shadow-none">`.
- **`plugins/AssetManagement/public/js/assets.js`**:
  - Implemented `initAssetTomSelectWithAjax()` to fetch categories, users, and assets via AJAX using Tom Select.
  - Instantiated `TableFilterSync` to synchronize status, category, and assigned user filters, maintaining URL queries and table state automatically.

## [2026-06-13] - Notes Module Redesign & UI Standardizations
### Modified
- **`public/assets/css/custom.css`**:
  - Removed all legacy `.sticky-notes` and `.sticky-note-bg-` color styles to clean up and reduce CSS footprint.
- **`resources/views/notes/list.blade.php`**:
  - Redesigned notes display to use standard design-system `.tcard` grid layout matching candidate/task cards.
  - Aligned page breadcrumbs and action controls to follow standard headers.
  - Standardized color indicator badges and text styling.
- **`resources/views/modals.blade.php`**:
  - Redesigned create and edit note modals body layout to use `.row.g-3` for clean, responsive input alignment.
  - Ensured all form labels use the `form-label` class.

## [2026-06-13] - Interview Modals UI & Selector Upgrades
### Modified
- **`public/assets/js/custom.js`**:
  - Replaced Select2 with Tom Select for Candidate and Interviewer select elements.
- **`public/assets/js/pages/interview.js`**:
  - Updated the `.edit-interview-btn` click handler to set select values on Tom Select instances using `.tomselect.setValue()`.
- **`resources/views/modals.blade.php`**:
  - Aligned form layouts inside Schedule and Edit Interview modals using `row g-3` and added standard `form-label` class to all form labels.

## [2026-06-12] - Leads, Candidates & Tasks Kanban Drag & Drop Fixes
### Fixed
- **`public/assets/js/pages/leads-kanban.js`**:
  - Restricted Dragula movement to elements with `.kanban-card` class, preventing non-card buttons or footer controls from being dragged.
  - Simplified status update AJAX payload from stringified JSON to standard form POST variables.
- **`public/assets/js/pages/candidate-kanban.js`**:
  - Restricted Dragula movement to elements with `.kanban-card` class, preventing non-card buttons or footer controls from being dragged.
  - Simplified status update AJAX payload from stringified JSON to standard form POST variables.
- **`public/assets/js/pages/task-board.js`**:
  - Restricted Dragula movement to elements with `.tcard` class, preventing non-card elements from being dragged.
  - Updated AJAX request method to POST with `_method: "PUT"` and set the `X-CSRF-TOKEN` headers to ensure 100% reliable CSRF verification.

## [2026-06-12] - Project Kanban Drag & Drop Fix
### Fixed
- **`public/assets/js/pages/project-kanban.js`**:
  - Corrected Dragula `moves`, `accepts`, and `invalid` validation handlers to restrict movement exclusively to elements containing the `.kanban-card` class. This prevents the `.kanban-footer` container (Create project button) from being dragged or accepted as a draggable card.
  - Simplified and standardized the status update AJAX request to send standard POST parameter key-values instead of JSON-stringified raw data, ensuring smooth integration with Laravel request validators and headers.

## [2026-06-12] - To-Do Drag & Drop AJAX Status & Routing Fix
### Fixed
- **`public/assets/js/pages/todos.js`**:
  - Prefixed AJAX URLs `/todos/update_status` and `/todos/store` with `baseUrl` to ensure correct sub-directory routing.
  - Rewrote the AJAX configuration for the status update request to use `type: 'POST'`, set headers to include `X-CSRF-TOKEN`, and send the PUT directive as `_method: 'PUT'` in the payload (matching the working pattern in `custom.js`), preventing CSRF validation failures.
- **`resources/views/todos/list.blade.php`**:
  - Appended `todo-priority-{{ $completed_todo->priority }}` to completed items so their original priority badge class is preserved and correctly restored when dragged back to the incomplete column.

## [2026-06-12] - To-Do Drag & Drop Status Fix
### Fixed
- **`public/assets/js/pages/todos.js`**:
  - Fixed selector checks to use `.todo-card-complete` and `.todo-card-incomplete` classes (instead of the deprecated `.todo-gradient-` classes) when resolving drop targets.
  - Corrected `updateCounters()` to query layout elements and header labels using the redesigned class selectors, resolving null pointer script crashes.
  - Updated DOM replacement templates inside drop handlers to output standard design system badges (`.badge.badge-ok`, `.badge.badge-err`, `.badge.badge-warn`, `.badge.badge-info`) instead of the deprecated custom class elements.
  - Standardized the dynamic inline add action AJAX markup templates to match the new drag handles, edit/delete buttons, and styling standards.

## [2026-06-12] - To-Do Color Alignment
### Fixed
- **`public/assets/css/custom.css`**:
  - Replaced legacy references to `var(--primary)` with the system design primary color variable `var(--signal)` in `.todo-card.todo-card-incomplete`, `.todo-card-incomplete .todo-header-icon i`, `.todo-item:hover`, and `.todo-progress .progress-bar`.

## [2026-06-12] - To-Do Redesign
### Modified
- **`resources/views/todos/list.blade.php`**:
  - Replaced legacy color gradient header classes with flat top border accents for status columns.
  - Standardized custom inline priority tags to utilize standard design system badges (`badge-err`, `badge-warn`, `badge-info`).
  - Swapped custom completed tags with design system soft-green badges (`badge-ok`).
  - Modernized the reordering drag-handle icons and layout spacing.
- **`public/assets/css/custom.css`**:
  - Removed previous legacy `.todo-card`, `.todo-gradient-primary`, `.todo-gradient-success`, and related styling blocks entirely.
  - Wrote a new clean, system-compliant CSS block for To-Dos using CSS variables (`var(--bg-1)`, `var(--line)`, etc.) to support both light and dark modes natively.
  - Styled `.todo-card` and `.todo-item` as flat bordered components with dynamic border color transitions on hover.
  - Refined the progress completion tracker to use theme variables.

## [2026-06-12] - Client Management Redesign & Alignment
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

## [2026-06-12] - Country Code & Phone Number Overlap Fix
### Fixed
- **`public/assets/css/custom.css`**:
  - Excluded `#phone` from global `.form-control` and `.input-group .form-control` styling rules that override padding properties using `!important`.
  - Added specific styling rule block for `#phone` in the `body.v2-shell` layout context, preserving dynamic padding control for `intl-tel-input`.
  - Excluded `#phone` from standard focus and group-merge focus rules overriding padding properties.
  - Adjusted `.iti input` general styles to omit hardcoded `padding-left: 84px !important`, letting `intl-tel-input` dynamically calculate start position spacing based on selected country code.

## [2026-06-12] - Users Forms Layout & Preview Design Upgrades
### Modified
- **`resources/views/users/create_user.blade.php`**:
  - Replaced the simple file input with a modern dashed border upload block featuring a circular image preview.
  - Added a dynamic JavaScript listener at the bottom to update the image preview instantly when a file is selected.
  - Converted status and require email verification button groups to standard Bootstrap inline check radios (`form-check-input`) to fix vertical stretching and ensure selection states are clearly visible.
- **`resources/views/users/edit_user.blade.php`**:
  - Upgraded the file upload row block to match the dashed border outline style with preview.
  - Added the dynamic JavaScript image preview listener.
  - Converted the status button group to standard inline Bootstrap check radios.

## [2026-06-12] - Users Form Radio Selections Fix
### Fixed
- **`resources/views/users/create_user.blade.php`**:
  - Implemented dynamic checked values using the `old()` helper for the Active/Deactive (status) and Yes/No (require_ev) radio fields.
  - Standardized the wrapper class of radio button groups to `btn-group d-flex w-100` for proper layout formatting.
- **`resources/views/users/edit_user.blade.php`**:
  - Implemented dynamic checked values using the `old()` helper (retaining database values as fallbacks) for the Active/Deactive (status) radio fields.
  - Standardized the wrapper class of status radio button group to `btn-group d-flex w-100` for proper layout formatting.

## [2026-06-12] - Users Create/Edit Forms Alignment
### Modified
- **`resources/views/users/create_user.blade.php`**:
  - Right-aligned the action buttons (Cancel/Create) using Bootstrap's flexbox utilities (`d-flex justify-content-end gap-2 mt-4`).
- **`resources/views/users/edit_user.blade.php`**:
  - Right-aligned the action buttons (Cancel/Update) using Bootstrap's flexbox utilities (`d-flex justify-content-end gap-2 mt-4`).

## [2026-06-12] - Users Duplicate Clear Filters Button Fix
### Fixed
- **`resources/views/users/users.blade.php`**:
  - Removed the duplicate manual "Clear Filters" button markup, relying entirely on the automated filter button injection logic from `custom.js`.
  - Preserved the `col-md-3` column widths for the three dropdown filters, allowing the dynamically generated filter button to align perfectly alongside them.

## [2026-06-12] - Users Filter Card Wrapper
### Modified
- **`resources/views/users/users.blade.php`**:
  - Wrapped the user page filter row in a proper Bootstrap Card container (`card mb-4` with `card-body`) at the top of the page.
  - Added a "Clear Filters" button inside the filter row and adjusted column classes to `col-md-3`.
  - Updated the user table card container to use the standard flat styling (`card border shadow-none` with `card-body p-0`).

## [2026-06-12] - Users Table & Filters Redesign
### Modified
- **`resources/views/users/users.blade.php`**:
  - Moved the table filters outside and above the table card.
  - Converted the three filters (Status, Roles, Email Verification Status) from Select2/standard selects to Tom Select by replacing the class `js-example-basic-multiple` / `form-control` with `form-select tom_static_select`.
  - Upgraded the filters row wrapper to use the standard design system classes: `row g-3 align-items-end tk-filter-row mb-3`.
- **`public/assets/js/pages/users.js`**:
  - Configured `TableFilterSync` to synchronize the user list filters using the `tom-select` filter type instead of `select2`.
  - Set `ajaxType: null` for the roles filter, as options are statically loaded via Blade.
- **`app/Http/Controllers/UserController.php`**:
  - Refactored the `list` method to render a Bootstrap vertical three-dot (`bx-dots-vertical-rounded`) action dropdown menu instead of raw inline icons, matching the standard system design.

## [2026-06-12] - UI & Table Standardization
### Modified
- **`resources/views/settings/permission_settings.blade.php`**: Standardized the permission roles table by allowing the `tk-table` component to properly initialize as a bootstrap-table (removed `data-toggle=""`), removed all custom inline styles (`font-size`, `border-radius`, etc.), and converted custom badge colors to standard `bg-primary` and `bg-secondary` badges.
- **`resources/views/settings/languages.blade.php`**: Fixed invalid `<form>` tag nesting that was causing layout issues and wrapping around flex-box containers incorrectly. Added correct layout containers (`mb-3 mt-4` flex) for the header and right-aligned buttons. Kept all logic identical.
- **`resources/views/units/list.blade.php`**: Replaced the custom raw HTML `<table id="table"...>` markup with the core `<x-tk-table>` blade component, defining the `$columns` configuration array in PHP, thereby keeping the system table designs entirely uniform.

## [2026-06-12] - Settings & Integrations UI Redesigns (Minimalist Overhaul)
### Modified
- **`resources/views/settings/sms_gateway_settings.blade.php`**: Restructured the page from a single-card tabbed layout to a modern 2-column layout. Placed the SMS Gateway settings (using transparent-background inner tab-contents to fix dark mode issues) in the left column (`col-xl-8`). Placed WhatsApp and Slack settings cards in the right column (`col-xl-4`). Standardized the breadcrumbs and header layout. Repositioned action buttons to be right-aligned with standard design system gap spacing. Preserved all logical IDs, attributes, class configurations, and input name arrays (`header_key[]`, etc.) to ensure JS operations and forms submit perfectly. Applied a compact, minimalist spacing and style.
- **`resources/views/settings/google_calendar_settings.blade.php`**: Redesigned the settings view to match the compact minimalist design system. Added standard header and breadcrumbs. Overhauled the alert banner to be borderless and light. Compacted form controls and buttons and right-aligned actions. All variable names and form actions were preserved.
- **`resources/views/settings/pusher_settings.blade.php`**: Applied standard headers, breadcrumbs, borderless light alert banners, compact inputs (`.form-control-sm`), and right-aligned actions (`.btn-xs`).
- **`resources/views/settings/media_storage_settings.blade.php`**: Redesigned to use standard header/breadcrumbs and card layouts. Integrated compact form selects, AWS fields row alignments, and right-aligned buttons while preserving the dynamic JS toggles.
- **`resources/views/settings/terms_privacy_about.blade.php`**: Refined tab layout to avoid shadow backgrounds. Sized textareas to be compact, styled forms to be minimalist, and right-aligned update/cancel controls.
- **`resources/views/pwa/index.blade.php`**: Overhauled page container, titles, breadcrumbs, alert containers, file upload preview panel, and form elements. Preserved input IDs, validation targets, and dataset variables utilized by the reset script.
- **`resources/views/settings/security_settings.blade.php`**: Cleaned up top-level breadcrumb gaps. Redesigned to use flat, border-only minimalist card structures (`shadow-none border`), scaled-down inputs (`.form-control-sm`), custom flat alerts, and right-aligned footer actions (`.btn-xs`).
- **`resources/views/components/tk-table.blade.php`**: Added a `{{ $slot ?? '' }}` slot placeholder inside the main `<table>` element to support static server-side rendered table rows.
- **`resources/views/settings/permission_settings.blade.php`**: Replaced custom HTML table layout with the system's `<x-tk-table>` component, defining the `$columns` configurations in PHP, passing the `<tbody>` inside the default slot, and overriding standard dynamic AJAX data-attributes to align with the server-side rendering logic. All individual permission badges were unified to use the soft green `badge-ok` style.

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

## 2026-06-12 (Tom Select in Contract Modals)
- Upgraded the Select dropdowns in the Create/Edit Contract modals to use Tom Select for better UI design (
esources/views/modals.blade.php).
- Updated public/assets/js/custom.js to correctly populate Tom Select inputs for the Edit Contract modal via the .edit-contract click event.

## 2026-06-12 (Three Dots Menu for Contract Types)
- Converted inline action icons to a three-dot dropdown menu in ContractsController.php inside contract_types_list() method for UI consistency.

## 2026-06-12 (Payslips UI Improvements)
- Extracted filter dropdowns into a separate card layout in 
esources/views/payslips/list.blade.php.
- Converted select dropdowns in Payslip filters to use Tom Select (	om_users_select, 	om_clients_select, 	om_static_select).
- Upgraded the action column in PayslipsController.php's list method to use the consistent three-dots dropdown menu.

## 2026-06-12 (Payslips Forms UI Improvements)
- Converted Select2 dropdowns to Tom Select (	om_users_select, 	om_static_select, 	om_allowances_select, 	om_deductions_select) in payslips/create.blade.php and payslips/update.blade.php for a more modern UI while preserving existing fetched data and JS logic.
- Added Tom Select initializers for .tom_allowances_select and .tom_deductions_select in public/assets/js/custom.js.

## 2026-06-12 (Allowances UI Consistency)
- Converted inline action icons to a three-dot dropdown menu in AllowancesController.php's list method to maintain UI consistency across the application.

## 2026-06-12 (Contracts List UI Consistency)
- Updated 
esources/views/contracts/list.blade.php to wrap the filter panel and table in proper Bootstrap card and card-body classes instead of custom 	k-filter-panel and 	k-table-card classes.

## 2026-06-12 (Global Table Standard Update)
- Standardized llowances/list.blade.php, deductions/list.blade.php, contracts/list.blade.php, and payslips/list.blade.php to use the x-tk-table component instead of manually written 	able markup, matching the global standard used in pages like units/list.blade.php.
- Corrected the table wrapper structure to <div class="card border shadow-none"><div class="card-body p-0"> for a proper flat, bordered UI.

## 2026-06-12 (Deductions UI Updates)
- Converted the traditional multi-select in 
esources/views/deductions/list.blade.php to a Tom Select (	om_static_select) for a more modern appearance.
- Refactored the ctions column in pp/Http/Controllers/DeductionsController.php to use the standard "three dots" Bootstrap dropdown menu instead of inline buttons.

## 2026-06-12 (Expenses UI Updates)
- Standardized the Expenses list (
esources/views/expenses/list.blade.php) to use the <x-tk-table> component structure.
- Extracted filters in the Expenses list outside of the table card.
- Converted standard dropdowns to Tom Select (	om_users_select and 	om_expense_types_select).
- Updated public/assets/js/custom.js to initialize 	om_expense_types_select.
- Changed the inline action buttons in pp/Http/Controllers/ExpensesController.php to use the standard "three dots" dropdown menu.

## 2026-06-12 (Expenses Bug Fixes)
- Removed the duplicated "Clear Filters" button from the top filter row in 
esources/views/expenses/list.blade.php to clean up the UI layout.
- Initialized the daterangepicker properly in public/assets/js/pages/expenses.js so the Date Between filter functions as expected.
- Refactored TableFilterSync config in expenses.js to correctly target 	omselect instead of select2 for syncing state.

## 2026-06-12 (Clear Filters UI Fix)
- Removed #multi_select from all <x-tk-table> components across expenses, payslips, and contracts to eliminate the duplicated 'Clear Filters' button inside the table toolbar. The table toolbar now cleanly only shows 'Delete Selected' and 'Save Column Visibility'.
- Added properly aligned 'Clear Filters' buttons directly next to the filters inside the top filter cards across expenses, payslips, and deductions.
- Added missing click handler for .clear-deductions-filters in deductions.js to ensure the table correctly resets.

## 2026-06-12 (Expenses Modal UI Fixes)
- Converted standard dropdowns to use Tom Select (	om_expense_types_select and 	om_users_select) inside the "Create Expense" and "Edit Expense" modals located in 
esources/views/modals.blade.php.
- Fixed layout alignment of the form fields in both modals by upgrading .col to .col-md-6 to ensure proper side-by-side rendering on desktop.
- Refactored .edit-expense modal populator logic in public/assets/js/custom.js to correctly assign loaded values using the 	omselect API instead of standard select modifications.

## 2026-06-12 (Expense Types Table Standardization)
- Standardized the Expense Types table (
esources/views/expenses/expense_types.blade.php) to use the <x-tk-table> component framework for UI consistency.
- Updated the action buttons in pp/Http/Controllers/ExpensesController.php (expense_types_list) to use the standard three-dots dropdown menu.

## 2026-06-12 (Payments Standardization)
- Standardized the Payments list (
esources/views/payments/list.blade.php) to use the <x-tk-table> component framework.
- Extracted filters from the table card to a dedicated filter card at the top.
- Updated action buttons in pp/Http/Controllers/PaymentsController.php (list method) to use the standard three-dots dropdown menu.
- Converted the create and edit payment modals (
esources/views/modals.blade.php) to use col-md-6 layout grid and replaced Select2 with Tom Select.
- Added 	om_invoices_select initialization and updated .edit-payment population logic in public/assets/js/custom.js to correctly interface with TomSelect.
- Updated TableFilterSync setup and enabled Date Range Picker init in public/assets/js/pages/payments.js.

## 2026-06-12 (Payment Methods Standardization)
- Standardized the Payment Methods list (
esources/views/payment_methods/list.blade.php) to use the <x-tk-table> component framework.
- Updated action buttons in pp/Http/Controllers/PaymentMethodsController.php to use the standard three-dots dropdown menu.

## 2026-06-12 (Taxes, Units, and Items Standardization)
- Standardized the Taxes list (
esources/views/taxes/list.blade.php), Units list (
esources/views/units/list.blade.php), and Items list (
esources/views/items/list.blade.php) to use the <x-tk-table> component framework.
- Extracted filters from the table cards in Taxes and Items to a dedicated filter card at the top, replacing Select2 with Tom Select (	om_static_select).
- Updated action buttons in TaxesController, UnitsController, and ItemsController list methods to use the standard three-dots dropdown menu.

## 2026-06-12 (Item Modals Standardization)
- Converted the unit_id Select2 input to Tom Select (	om_static_select) in both the Create and Edit Item modals (
esources/views/modals.blade.php).
- Updated the .edit-item logic in public/assets/js/pages/items.js to seamlessly use Tom Select's .setValue() and .clear() methods to populate the modal fields.

## 2026-06-12 (Social Media Scheduler Standardization)
- Standardized the Social Media Scheduler list view (plugins/SocialMediaManagement/Resources/views/social-media-scheduler/index.blade.php) to use the <x-tk-table> component structure.
- Extracted select_social_platforms and select_social_stastuses filters out of the table card into a standalone filter card and replaced Select2 with Tom Select (	om_static_select).
- Updated the table action buttons in SocialMediaController.php's list method to be bundled within the standard three-dots dropdown menu.
- Added TableFilterSync configuration in social.js to ensure proper refreshing and functionality of the "Clear filters" button.

## 2026-06-13 (Asset Module Redesign JS Bindings & Project Hash Routing)
- Updated `plugins/AssetManagement/public/js/assets.js` and `public/assets/js/asset-plugin/assets.js` to correctly interface with static Tom Select dropdowns (`.tom_static_select`).
- Updated the `updateAssetOffcanvasBtn` click event handler to use Tom Select's `.setValue()` for category, status, and assignment inputs rather than jquery `.trigger('change')`.
- Updated the trigger from Bootstrap modal (`#updateAssetModal`) to offcanvas (`#updateAssetOffcanvas`) to match the revamped blade view component.
- Updated reset logic to reset tomselect dropdowns on offcanvas hide/reset events (`hidden.bs.offcanvas`).
- Completely eliminated Select2 calls and initialization function `initAssetSelect2` inside `public/assets/js/asset-plugin/assets.js` and replaced them with `initAssetTomSelectWithAjax` and filters sync configuration.
- Updated `public/assets/js/pages/project-information.js` to automatically display the `#project_detail_panel` offcanvas when landing on the page with a tab hash target like `#navs-top-discussions` using jQuery's `.offcanvas('show')` with a 200ms delay, and configured it to open by default on all page loads when no tab hash is present.
