# Changelog - Users Forms Layout & Preview Design Upgrades

## [2026-06-12]
### Modified
- **`resources/views/users/create_user.blade.php`**:
  - Replaced the simple file input with a modern dashed border upload block featuring a circular image preview.
  - Added a dynamic JavaScript listener at the bottom to update the image preview instantly when a file is selected.
  - Converted status and require email verification button groups to standard Bootstrap inline check radios (`form-check-input`) to fix vertical stretching and ensure selection states are clearly visible.
- **`resources/views/users/edit_user.blade.php`**:
  - Upgraded the file upload row block to match the dashed border outline style with preview.
  - Added the dynamic JavaScript image preview listener.
  - Converted the status button group to standard inline Bootstrap check radios.
