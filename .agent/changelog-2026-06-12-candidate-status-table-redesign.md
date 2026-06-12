# Changelog - Candidate Status Table Redesign

## [2026-06-12]
### Modified
- **`resources/views/components/candidate-status-card.blade.php`**:
  - Migrated the raw `<table>` element to the system-wide `<x-tk-table>` component.
  - Configured table columns using PHP `$columns` array.
  - Wrapped the new table element in standard minimalist card classes (`card border shadow-none > card-body p-0`), matching other tables in the design system.
