# Taskify — Laravel Blade Architecture

Production-ready Laravel Blade conversion of the Taskify v2 design system.

---

## 📁 Folder structure

\`\`\`
laravel/
├── config/
│   ├── navigation.php       Source of truth for rail + panel + page titles
│   └── ui.php               Variant maps for buttons / badges / alerts
│
├── resources/
│   ├── css/
│   │   ├── tokens.css       Design tokens (colors, type, spacing, motion, z-index)
│   │   ├── base.css         Resets + element defaults + util classes
│   │   ├── typography.css   Type scale + heading styles
│   │   ├── components/      One file per component family
│   │   │   ├── buttons.css
│   │   │   ├── forms.css
│   │   │   ├── badges.css
│   │   │   ├── cards.css
│   │   │   ├── navigation.css
│   │   │   ├── overlays.css
│   │   │   ├── feedback.css
│   │   │   └── data.css
│   │   └── app.css          Entry — Vite-built
│   │
│   ├── js/
│   │   ├── app.js           JS entry — initializes all modules
│   │   └── modules/         Vanilla, event-delegated, attribute-driven
│   │       ├── theme.js     dark/light toggle (persisted)
│   │       ├── dropdown.js  data-dropdown protocol
│   │       ├── modal.js     data-toggle="modal" protocol
│   │       ├── offcanvas.js drawer protocol (same as modal)
│   │       ├── palette.js   ⌘K command palette w/ keyboard nav
│   │       ├── multiselect.js ajax + local multiselect w/ tag tokens
│   │       ├── table.js     sort + bulk-select
│   │       └── toast.js     window.Toast.show({...})
│   │
│   └── views/
│       ├── layouts/         app · auth · guest
│       ├── components/
│       │   ├── buttons/     button, icon-button, segmented
│       │   ├── forms/       field, input, textarea, select, multiselect,
│       │   │               checkbox, radio, switch, datepicker,
│       │   │               file-upload, avatar-upload
│       │   ├── badges/      badge, chip, tag, kbd, status-pill
│       │   ├── cards/       card, stat-card
│       │   ├── navigation/  sidebar, context-panel, header,
│       │   │               breadcrumb, tabs
│       │   ├── overlays/    modal, offcanvas, dropdown, dropdown-item,
│       │   │               command-palette, tooltip
│       │   ├── feedback/    alert, spinner, skeleton, empty-state,
│       │   │               confirmation-modal
│       │   ├── data/        table, kanban-board, kanban-card,
│       │   │               schedule-row, metric-strip, progress-bar,
│       │   │               sparkline, area-chart, donut
│       │   └── shared/      icon, avatar, avatar-stack, dot, divider
│       └── pages/           Page-level views composing components
│           ├── auth/login.blade.php
│           ├── dashboard/index.blade.php
│           ├── projects/index.blade.php
│           └── settings/profile.blade.php
└── README.md
\`\`\`

---

## 🎨 Design system at a glance

| Token group | Tokens                                                       |
|-------------|--------------------------------------------------------------|
| Color       | `--signal`, `--ok`, `--warn`, `--err`, `--info`             |
| Surface     | `--bg-0` → `--bg-3`, `--line`, `--line-2`                    |
| Foreground  | `--fg-0` → `--fg-3`, `--fg-inv`                              |
| Typography  | `--fs-xs` (10.5) → `--fs-4xl` (36); Inter + JetBrains Mono   |
| Spacing     | `--sp-1` (4) → `--sp-16` (64) on a 4px grid                  |
| Radius      | `--r-1` (3) → `--r-4` (12), `--r-pill` (999)                 |
| Shadow      | `--shadow-1` → `--shadow-4`, `--shadow-focus` (signal glow)  |
| Motion      | `--t-1` (100ms) → `--t-4` (360ms), `--ease` curve            |
| Z-index     | layered: base → raised → dropdown → tooltip → modal → toast  |

Dark mode is toggled via `[data-theme="dark"]` on `<html>`. All component
CSS uses `var(--…)` exclusively — switching themes touches no JS but the
attribute.

---

## 🧱 Component usage

All components live under `<x-…>` namespaced by folder. Every component
documents its props with `@props([…])` at the top.

### Button

\`\`\`blade
<x-buttons.button variant="primary" size="md" icon="plus">
    Create project
</x-buttons.button>

<x-buttons.button variant="danger" :loading="true">
    Deleting…
</x-buttons.button>

<x-buttons.button href="/login" variant="ghost">Sign in</x-buttons.button>
\`\`\`

| Prop      | Type / values                                            |
|-----------|----------------------------------------------------------|
| variant   | primary · secondary · ghost · outline · danger · success |
| size      | sm · md · lg                                             |
| icon      | any icon name (left)                                     |
| iconAfter | any icon name (right)                                    |
| href      | renders `<a>` instead of `<button>`                      |
| loading   | bool — shows centered spinner, blocks interaction        |
| disabled  | bool — `aria-disabled`, dimmed                           |
| block     | bool — full-width                                        |

### Modal

\`\`\`blade
<x-buttons.button data-toggle="modal" data-target="#confirm-delete">Delete</x-buttons.button>

<x-overlays.modal id="confirm-delete" size="sm" title="Delete project?">
    <p>This will permanently remove the project and all its tasks.</p>

    <x-slot:footer>
        <x-buttons.button variant="ghost" data-dismiss="modal">Cancel</x-buttons.button>
        <x-buttons.button variant="danger">Delete</x-buttons.button>
    </x-slot:footer>
</x-overlays.modal>
\`\`\`

### Offcanvas (drawer)

\`\`\`blade
<x-buttons.button data-toggle="offcanvas" data-target="#task-drawer">Open</x-buttons.button>

<x-overlays.offcanvas id="task-drawer" position="right" title="Task details">
    <p>Drawer body content here.</p>
    <x-slot:footer><x-buttons.button variant="primary">Save</x-buttons.button></x-slot:footer>
</x-overlays.offcanvas>
\`\`\`

### Multiselect (ajax / local)

\`\`\`blade
{{-- Ajax-backed --}}
<x-forms.multiselect name="user_ids" endpoint="/api/users/search"
                     placeholder="Pick teammates…"/>

{{-- Local --}}
<x-forms.multiselect name="tags"
                     :options="[
                         ['value' => 'design',   'label' => 'design'],
                         ['value' => 'frontend', 'label' => 'frontend'],
                         ['value' => 'backend',  'label' => 'backend'],
                     ]"
                     :values="['design']"/>
\`\`\`

Endpoint must respond with JSON: `{ results: [{ value, label }, ...] }`.

### Table

\`\`\`blade
<x-data.table
    selectable
    :columns="[
        ['key' => 'name',  'label' => 'Name',   'sortable' => true],
        ['key' => 'email', 'label' => 'Email'],
        ['key' => 'role',  'label' => 'Role'],
    ]"
    :rows="$users->toArray()">

    <x-slot:toolbar>
        <strong>{{ $users->total() }} users</strong>
        <span class="right">
            <x-forms.input placeholder="Search…" icon="search" size="sm"/>
            <x-buttons.button variant="primary" size="sm" icon="plus">Invite</x-buttons.button>
        </span>
    </x-slot:toolbar>

    <x-slot:footer>
        Showing {{ $users->firstItem() }}–{{ $users->lastItem() }} of {{ $users->total() }}
        <span class="right">{{ $users->links() }}</span>
    </x-slot:footer>
</x-data.table>
\`\`\`

### Toast (JS)

\`\`\`html
<script>
    Toast.success({ title: 'Saved', message: 'Profile updated.' });
    Toast.error({ title: 'Failed', message: 'Network unreachable.' });
</script>
\`\`\`

---

## ♿ Accessibility

- Every interactive element has a visible `:focus-visible` ring (signal glow).
- Modal / offcanvas / palette trap focus implicitly via `overflow:hidden` on
  `<body>`, close on **Esc**, and use `role="dialog"` + `aria-modal`.
- Form inputs flip to `aria-invalid="true"` when validation fails, picking
  up red border + ring automatically.
- Icons inside buttons are `aria-hidden`; buttons themselves use `title` +
  `aria-label` when text-less.
- Status colors are paired with shape/text so they don't carry meaning alone.

---

## 📱 Responsive

| Breakpoint | Width   | Effect                                               |
|-----------:|---------|------------------------------------------------------|
| xs         | < 480   | Header right-slot buttons go icon-only               |
| sm         | < 600   | Breadcrumb collapses to title only                   |
| md         | < 760   | Header overflow menu kicks in; d-grid → 1 col        |
| lg         | < 960   | Search collapses to icon-only                        |
| xl         | < 1180  | d-grid → 2 cols; donut card stacks                   |

---

## 🔌 Adding a new component

1. Drop CSS in `resources/css/components/<family>.css` and import in
   `app.css` (or just add a new `@import` line if it's a new family).
2. Add the Blade view at
   `resources/views/components/<family>/<name>.blade.php` with
   `@props([...])` at the top.
3. If JS is required, add a module at `resources/js/modules/<name>.js` and
   call its `init()` from `app.js`. Prefer `document`-level event delegation
   so dynamically-added DOM picks it up automatically.

That's it — no compiled framework, no opinionated state layer. Plays
nicely with Livewire, Inertia, or vanilla Blade forms.

---

## 🚀 Vite / build

Add to `vite.config.js`:

\`\`\`js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [laravel({
        input: ['resources/css/app.css', 'resources/js/app.js'],
        refresh: true,
    })],
});
\`\`\`

Then in any layout: `@vite(['resources/css/app.css', 'resources/js/app.js'])`.


---

## 🎨 Appearance preferences

End-users can configure their own appearance via **Settings → Appearance**:

- **Theme:** Light · Dark · System (follows OS).
- **Accent color:** 7 preset hues + a free OKLCH hue slider.
- **Active menu style:** 19 variants for how the active rail item is highlighted.
- **Density:** Compact · Cozy · Comfortable.

Preferences are stored in `localStorage['taskify.appearance']` and applied
on every page load by `resources/js/modules/appearance.js`. The module
overrides the four `--signal*` tokens at `:root`, so any component using
`var(--signal)` updates instantly.

To read prefs server-side (e.g. SSR-friendly themes), persist the same shape
on the user model and have the layout's `<html data-theme>` reflect it.
