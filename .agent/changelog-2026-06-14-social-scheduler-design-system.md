# Changelog — Social Media Scheduler Design System Alignment

## [2026-06-14]

### Overview
Migrated the Post Scheduler module (index page, post detail page, and quick view modal) from bespoke hardcoded styles to the Taskify V2 design system. All colours now use CSS custom properties (`--bg-*`, `--fg-*`, `--line`, `--signal`, `--ok`, `--warn`, `--err`), and components use the `tk-*` class library from `tk-design-system.css`. Both light and dark themes are fully supported.

### Modified

- **`plugins/SocialMediaManagement/Resources/views/social-media-scheduler/index.blade.php`**:
  - Header action buttons: `btn btn-sm btn-primary` → `tk-btn tk-btn-primary tk-btn-sm` grouped in `tk-cluster`.
  - Default view badge: `badge bg-primary/bg-secondary` → `tk-badge tk-badge-primary` / `tk-badge`.
  - Filter card: `card mb-4` → `tk-card mb-4`, clear button → `tk-btn tk-btn-secondary tk-btn-sm`.
  - Empty state: replaced image-based empty card with `tk-empty` component (centered icon, heading, paragraph, CTA button).
  - Quick View Modal: `modal-content/header/body` now use `--bg-1`, `--line`, `--fg-0`, `--signal` tokens. Added `modal-dialog-scrollable`, dark-mode close button filter.

- **`plugins/SocialMediaManagement/Resources/views/social-media-scheduler/post_info.blade.php`**:
  - Complete rewrite using design system. All cards → `tk-card` with `tk-card-head` / `tk-card-title` / `tk-card-body`.
  - Status badges → `tk-badge tk-badge-{variant}`.
  - Post information section → `tk-meta` grid (dl/dt/dd).
  - Caption display → `tk-tile` surface with overflow scroll.
  - Media files → `tk-tile` cards with image/video + muted metadata.
  - Platform rows → `tk-tile` with `tk-between`, `tk-cluster`, `tk-badge` status, `tk-iconbtn` for external links.
  - Summary stats → `tk-facts` grid with `tk-fact-v` / `tk-fact-k` and colour-coded values.
  - Back button → `tk-btn tk-btn-secondary tk-btn-sm`.

- **`plugins/SocialMediaManagement/Resources/views/social-media-scheduler/calendar.blade.php`**:
  - Quick View Modal wrapper updated to match the design-system-aware version (same as index page).

- **`public/assets/js/social/social.js`**:
  - `showPostQuickView()`: Loading state → `tk-empty` + `tk-skel`. Error state → `tk-empty` with `--err` colour.
  - `showQuickView()`: Complete template rebuild — all `.qv-*` classes replaced with `tk-*`:
    - Header → `tk-tile` with `tk-between`, `tk-cluster`, `tk-badge` status, `tk-meta` dates.
    - Caption → `tk-card` with `tk-card-head` / `tk-card-body` / `tk-tile`.
    - Platform cards → `tk-tile` per platform with `tk-between`, `tk-badge`, `tk-muted`.
    - Error blocks → inline token-backed styles (`--err`).
    - Stats → `tk-facts` grid with `tk-fact-v` / `tk-fact-k`.

- **`public/assets/css/social/social.css`**:
  - Removed all `.qv-*` styles (loading, error, post header, caption, platform cards, stats grid, responsive overrides) — ~400 lines of hardcoded CSS deleted.
  - Removed global `.card`, `.card-header`, `.card-body` overrides that were clobbering all Bootstrap cards site-wide.
  - Removed redundant `.status-badge` redefinitions and `.btn-sm` overrides.
  - Added scoped `#quickViewModal` rules using design tokens for modal theming.
  - Added `[data-theme="dark"]` close button inversion.
  - Migrated `.platform-card`, `.post-preview`, `.remove-new-media`, `.char-counter-text`, `.accordion-button:not(.collapsed)` etc. from hardcoded colours to design token variables.
