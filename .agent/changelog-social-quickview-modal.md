# Changelog - Social Media Scheduler Quick View Modal Redesign

Refactored the social media scheduler's Quick View Modal (`quickViewModal`) to align with the V2 design system, including complete dark/light mode compatibility.

## Changes

### 1. Style & Theme Updates
- **File**: [social.css](file:///c:/Users/infin/OneDrive/Desktop/v2/public/assets/css/social/social.css)
- Updated all `.qv-` selectors to utilize theme CSS variables (`var(--bg-*)`, `var(--fg-*)`, `var(--line)`) instead of hardcoded hex codes.
- Redesigned status badges, platforms grid, and summary statistics widgets with semi-transparent background colors and design-system compatible border accents.
- Modernized loading and error states to dynamically shift colors depending on the active theme.
- Fixed `.day-post-item` to use dynamic theme backgrounds and borders in month/week timeslots.

### 2. View Markup Updates
- **Files**:
  - [calendar.blade.php](file:///c:/Users/infin/OneDrive/Desktop/v2/plugins/SocialMediaManagement/Resources/views/social-media-scheduler/calendar.blade.php)
  - [index.blade.php](file:///c:/Users/infin/OneDrive/Desktop/v2/plugins/SocialMediaManagement/Resources/views/social-media-scheduler/index.blade.php)
- Added `<div class="modal-footer border-0">` with action buttons (Publish Now, Edit, Delete, Close) inside `#quickViewModal` to match the references and event bindings in the Javascript controllers (`social.js` and `social-calendar.js`).
