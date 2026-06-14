# Changelog - Breadcrumbs Standardization

This log details the standardization of the breadcrumbs layout across the application views to conform to the native Bootstrap 5/Sneat design system layout (`breadcrumb-style1`).

## [2026-06-14] - Breadcrumbs Layout Standardization

### Modified
- **`resources/views/projects/projects.blade.php`**
- **`resources/views/projects/grid_view.blade.php`**
- **`resources/views/projects/kanban.blade.php`**
- **`resources/views/tasks/tasks.blade.php`**
- **`resources/views/tasks/board_view.blade.php`**
- **`resources/views/tasks/group_by_task_lists.blade.php`**
- **`resources/views/tasks/calendar_view.blade.php`**
- **`resources/views/notes/list.blade.php`**
- **`resources/views/leads/show.blade.php`**
- **`resources/views/leads/kanban.blade.php`**
- **`resources/views/leads/index.blade.php`**
- **`resources/views/leads/edit.blade.php`**
  - Replaced legacy flat `<nav class="breadcrumb">` structure and hardcoded `/` span separators with the Sneat-native standard `<nav aria-label="breadcrumb"><ol class="breadcrumb breadcrumb-style1"><li class="breadcrumb-item">...</li></ol></nav>` breadcrumbs markup.
