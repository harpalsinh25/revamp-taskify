# Taskify Revamp — Implementation Plan

> **Goal:** migrate the current Taskify product UI to the **v2 "Graphite Studio"**
> design system, delivered here as a production-ready Laravel Blade component
> library + a single-page interactive Style Guide.
>
> **Status:** design finalized ✓ · component library built ✓ · ready for integration.

---

## 0. What's in this kit

```
Taskify Revamp Kit/
├── Style Guide.html          ← open in a browser; live, interactive design reference
├── design-system/
│   └── assets/
│       ├── system.css        ← the entire design system, concatenated (browser-ready)
│       ├── docs.css          ← Style Guide chrome only
│       └── docs.js           ← Style Guide interactions (copy, scroll-spy, tooltips)
├── laravel/                  ← the real deliverable — drop into your Laravel app
│   ├── config/
│   │   ├── navigation.php     single source of truth: rail + panels + titles
│   │   └── ui.php             variant maps
│   ├── resources/
│   │   ├── css/               tokens + base + 8 component files + app.css entry
│   │   ├── js/                vanilla, attribute-driven modules
│   │   └── views/
│   │       ├── layouts/       app · auth · guest
│   │       ├── components/    30+ <x-…> Blade components
│   │       └── pages/         example pages (dashboard, projects, settings, auth)
│   └── README.md             architecture + every component's props + usage
├── PLAN.md                   ← this file
└── CLAUDE_CODE_PROMPT.md     ← paste into Claude Code to drive the migration
```

---

## 1. Design language (what's changing)

| Aspect            | Old Taskify            | v2 Graphite Studio                                  |
|-------------------|------------------------|-----------------------------------------------------|
| Accent            | multiple colors        | **one signal accent** (default Lime, user-tunable)  |
| Surfaces          | flat / heavy borders   | layered `--bg-0…3` with hairline `--line`           |
| Type              | system / Roboto        | **Inter** (UI) + **JetBrains Mono** (data/IDs)      |
| Color model       | hex / rgb              | **OKLCH** tokens (perceptual, theme-stable)         |
| Dark mode         | partial / bolted-on    | **at parity** — single `[data-theme]` switch        |
| Radius            | mixed                  | tight, deliberate scale (3 / 5 / 8 / 12px)          |
| Density           | fixed                  | user preference (compact · cozy · comfortable)      |
| Personalization   | none                   | **Appearance settings** (theme, hue, rail style)    |

---

## 2. Migration strategy

We migrate **screen by screen**, not all at once. The new system is additive —
it lives under `resources/css/` + `resources/views/components/` and does not
touch existing Blade until you swap a page over.

### Phase 0 — Foundation (½ day)
- [ ] Copy `laravel/config/`, `laravel/resources/css/`, `laravel/resources/js/` into the app.
- [ ] Add the two `@vite` inputs (`app.css`, `app.js`) to the main layout `<head>`.
- [ ] Register `config/navigation.php` + `config/ui.php` (already namespaced — just drop in).
- [ ] Verify tokens load: `document.documentElement` shows `--signal` etc.
- [ ] Confirm dark mode flips with `<html data-theme="dark">`.

### Phase 1 — Shell (1 day)
- [ ] Replace the global layout with `layouts/app.blade.php`
      (rail + context-panel + header + content slot).
- [ ] Wire real routes into `config/navigation.php` (rail items, panel sections).
- [ ] Hook the ⌘K command palette to your search endpoint.
- [ ] Header actions slot → your existing "Create" / "Ask AI" buttons.

### Phase 2 — Primitives swap (1–2 days)
Replace ad-hoc markup with components, lowest-risk first:
- [ ] Buttons → `<x-buttons.button>` (variant/size/icon/loading).
- [ ] Badges, chips, tags, status pills → `<x-badges.*>`.
- [ ] Form inputs → `<x-forms.*>` (field + input + select + switch + …).
- [ ] Avatars → `<x-shared.avatar>` / `<x-shared.avatar-stack>`.

### Phase 3 — Page templates (2–4 days)
Port one screen at a time using the provided examples as the pattern:
- [ ] **Dashboard** → `pages/dashboard/index.blade.php` (metric strip, charts, schedule).
- [ ] **Projects** → `pages/projects/index.blade.php` (kanban board + task drawer).
- [ ] **Settings** → `pages/settings/profile.blade.php` + `appearance.blade.php`.
- [ ] **Auth** → `pages/auth/login.blade.php` (split layout).
- [ ] Remaining modules (Leads, Finance, HRMS, Chat, Mail, Calendar, Notes, Files)
      follow the same composition — reuse `<x-data.table>`, `<x-cards.card>`, charts.

### Phase 4 — Data + interactivity (2–3 days)
- [ ] Tables → `<x-data.table>` (sort, bulk-select, pagination slots).
- [ ] Charts → `<x-data.area-chart>` / `donut` / `sparkline` (hover tooltips included).
- [ ] Multiselect (ajax) → point `endpoint` at your `/api/*/search` routes
      returning `{ results: [{ value, label }] }`.
- [ ] Modals / drawers → `data-toggle="modal|offcanvas"` + `data-target`.
- [ ] Toasts → `window.Toast.success({ title, message })`.

### Phase 5 — Personalization (½ day)
- [ ] Appearance settings page live (theme · accent hue · rail style · density).
- [ ] Persist prefs to the user model (mirror `localStorage['taskify.appearance']`)
      so SSR renders the correct `<html data-theme>` on first paint.

### Phase 6 — Polish + QA (2 days)
- [ ] Responsive sweep at xs / sm / md / lg / xl breakpoints.
- [ ] Accessibility pass: focus rings, `aria-invalid`, dialog roles, keyboard nav.
- [ ] Cross-browser: Safari (backdrop-filter, OKLCH), Firefox, Chrome.
- [ ] Remove the legacy stylesheet once all screens are migrated.

---

## 3. Component inventory (ready to use)

**Primitives** — button, icon-button, segmented, icon (43 icons), avatar,
avatar-stack, dot, divider.
**Badges** — badge (6 tones), chip, tag, kbd, status-pill.
**Forms** — field, input, textarea, select, multiselect (ajax+local),
checkbox, radio, switch, datepicker, file-upload, avatar-upload.
**Surfaces** — card, stat-card, metric-strip.
**Feedback** — alert, spinner, skeleton, empty-state, confirmation-modal.
**Navigation** — sidebar, context-panel, header, breadcrumb, tabs, pagination.
**Overlays** — modal, offcanvas, dropdown, dropdown-item, command-palette, tooltip.
**Data** — table, kanban-board, kanban-card, schedule-row, progress-bar,
sparkline, area-chart, donut (all charts include hover tooltips).

> Full prop tables + usage examples: see **`laravel/README.md`** and the live
> **Style Guide.html**.

---

## 4. Risks & mitigations

| Risk                                            | Mitigation                                                        |
|-------------------------------------------------|-------------------------------------------------------------------|
| OKLCH unsupported on old browsers               | Targets evergreen browsers; add a hex fallback layer if needed.   |
| `backdrop-filter` (frosted rail, palette) perf  | Limited to small surfaces; degrades gracefully to solid.          |
| Big-bang migration breaks prod                  | Screen-by-screen swap; old + new CSS coexist until cutover.       |
| Kanban drag-drop needs state                    | Provided as static markup — wire with Livewire/Alpine or vanilla. |
| Theme flash on load (FOUC)                       | Persist pref server-side; render `data-theme` in initial HTML.    |

---

## 5. Definition of done

- [ ] Every production screen renders through `layouts/app.blade.php`.
- [ ] No screen references the legacy stylesheet.
- [ ] Dark mode + accent hue + density all switch live from Appearance settings.
- [ ] Lighthouse a11y ≥ 95 on dashboard, projects, settings.
- [ ] All interactive components keyboard-navigable + screen-reader labeled.
- [ ] Style Guide kept in sync as the team's living reference.
