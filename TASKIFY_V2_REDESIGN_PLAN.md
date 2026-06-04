# Taskify v2 "Graphite Studio" Redesign — Implementation Plan

> **Goal:** Replace the entire UI of Taskify with the v2 *Graphite Studio* design
> system (from `Taskify Revamp Kit/`) and ship it as one big update to 400+
> existing CodeCanyon customers — **without changing any business logic, without
> any database migration, and without any risk to existing customer data.**

---

## 0. Non-negotiable guardrails (the "don't break anything" contract)

These are the rules every commit must satisfy. They are what make this redesign
**data-safe and logic-safe**.

1. **View layer only.** We change `resources/views/**`, `resources/css/**`,
   `resources/js/**`, `config/navigation.php`, `config/ui.php`, and the compiled
   `public/build/**`. We do **not** touch controllers, models, services,
   requests, routes, jobs, events, or middleware logic.
   - *Allowed exception:* a controller may pass **additional** view data (e.g.
     `compact(...)` gains a variable) where a new component needs it — never
     removing or changing existing return values.
2. **Zero database migrations.** No new tables, no new columns, no schema
   changes. Appearance preferences (theme/accent/density) are stored in
   **`localStorage` only** (per your decision). → Existing customer data is
   physically untouched by this update.
3. **Routes and route names are frozen.** Existing URLs keep working so
   bookmarks, deep links, the mobile/PWA, and the API stay valid.
4. **`get_label()` i18n preserved.** Every visible string keeps going through the
   existing translation helper so all language packs keep working.
5. **Permissions preserved.** Every menu item / action that is currently
   `@can`/permission-gated stays gated after the redesign.
6. **Reversible.** Because we keep the old layout in place until the final
   cutover, any screen can be reverted by switching one `@extends` line back.

---

## 1. Key architectural decisions

### 1.1 Two layouts during the build, full cutover at release
The new `base.css` applies **global element resets** (`body`, `button`, `a`,
`input`, scrollbars). Loading it on the same page as Bootstrap **breaks the old
UI**. Therefore:

- Keep the existing **`resources/views/layout.blade.php` (Bootstrap) untouched.**
- Add the new shell as a **separate** layout (`layouts/app` — see 1.2).
- A screen is migrated by switching its `@extends('layout')` →
  `@extends('layouts.app')`. **Only one CSS world loads per request → no bleed.**
- When the **last** screen is migrated, the legacy layout + `public/assets`
  Bootstrap/jQuery bundle are removed in the final cutover commit. Release ships
  as one update, but is built screen-by-screen so it's reviewable and reversible
  at every step.

### 1.2 Adapt the new shell to `@extends` (not slots) — minimize churn
The kit ships a **slot-based** `layouts/app.blade.php` (`{{ $slot }}`,
`<x-slot:headerActions>`). Your 128 pages use **`@extends` + `@section('content')`**.
Rather than rewrite every page into component-slot syntax, we create an
`@extends`-compatible version of the shell:

```blade
{{-- resources/views/layouts/app.blade.php (adapted) --}}
<!DOCTYPE html><html data-theme="...">
<head> @vite([...]) @stack('head') @yield('page_styles') </head>
<body>
  <div class="app">
    <x-navigation.sidebar :active="$active ?? null"/>
    <x-navigation.context-panel :active="$active ?? null"/>
    <div class="main">
      <x-navigation.header .../>
      <main class="content fade-in">@yield('content')</main>
    </div>
  </div>
  <x-overlays.command-palette/>
  @stack('overlays') @yield('page_scripts') @stack('scripts')
</body></html>
```

Now each page migration = **(a)** change `@extends('layout')` →
`@extends('layouts.app')`, **(b)** restyle the `@section('content')` body with the
new components. Page-level `@yield('page_styles')` / `@yield('page_scripts')`
keep working, so per-page assets (kanban, gantt, etc.) still load.

### 1.3 Navigation must be rebuilt from the real menu
The kit's `config/navigation.php` is demo data (`finance.index`, `hrms.index`,
`projects.mine` — routes that don't exist) with **no permission concept**. Your
[menu.blade.php](resources/views/components/menu.blade.php) (271 lines) is
**permission-gated and workspace-aware**. Work required:
- Rebuild `config/navigation.php` from the **real** routes + labels in the
  current menu.
- Extend `<x-navigation.sidebar>` and `<x-navigation.context-panel>` to accept a
  `permission` (or `can`) key per item and **hide items the user can't access**.
- Port the **workspace switcher** and **menu search** from the old menu into the
  new shell (the kit shell has neither out of the box).

### 1.4 jQuery plugins → kit vanilla components, **with documented exceptions**
Per your decision, replace plugins with the kit's vanilla modules **where the kit
provides an equivalent**. The kit covers: tables, multiselect, modals, offcanvas,
dropdowns, command palette, toasts, charts (area/donut/sparkline).

**The kit does NOT provide** equivalents for these — so they are explicit
exceptions (restyle-and-keep, or build new only if you want):

| Feature (current plugin)      | Kit equivalent? | Plan                                            |
|-------------------------------|-----------------|-------------------------------------------------|
| Rich text editor (TinyMCE)    | ❌ none          | Keep TinyMCE, theme it to match tokens          |
| Calendar (FullCalendar)       | ❌ none          | Keep FullCalendar, restyle with tokens          |
| Gantt (frappe-gantt)          | ❌ none          | Keep frappe-gantt, restyle                       |
| Kanban drag-drop (Dragula)    | ⚠️ static markup only | Keep Dragula for DnD; render with kit kanban-card markup |
| Tables (Bootstrap-Table)      | ✅ `x-data.table` | Replace (re-implement export/server-side as needed) |
| Select2                       | ✅ `x-forms.multiselect`/`select` | Replace; AJAX endpoints return `{results:[{value,label}]}` |
| Toastr                        | ✅ `Toast.*`     | Replace                                          |
| Bootstrap modals/offcanvas    | ✅ `x-overlays.*`| Replace (`data-toggle`/`data-target`)           |
| daterangepicker               | ✅ `x-forms.datepicker` | Replace where simple; keep for advanced ranges |
| ApexCharts                    | ✅ kit charts    | Replace simple charts; keep ApexCharts for complex |

> ⚠️ **Effort note:** Bootstrap-Table → `x-data.table` is the single biggest
> functional risk because the current tables likely use server-side pagination,
> export (CSV/Excel/PDF), and column filters. The kit table does sort +
> bulk-select + slots only. Re-implement search/pagination/export via the slots
> and existing controller endpoints, screen by screen, and QA each.

---

## 2. Phased execution (build order: **core screens first**, release together)

### Phase 0 — Foundation & build pipeline (½–1 day)
- [ ] Copy kit assets into the app (additive — no overwrites, folders differ):
  - `Taskify Revamp Kit/laravel/resources/css/**` → `resources/css/` (new files
    alongside the existing `app.css`; **do not** clobber the current `app.css`
    used by Vite today — name the new entry e.g. `v2.css` if needed).
  - `…/resources/js/**` → `resources/js/`
  - `…/resources/views/components/**` → `resources/views/components/` (kit folders
    `buttons/ badges/ forms/ cards/ data/ navigation/ overlays/ feedback/ shared/`
    don't collide with existing flat components; **only** `ui/offcanvas` vs
    `overlays/offcanvas` differ by folder — safe).
  - `…/resources/views/layouts/**` → `resources/views/layouts/`
  - `…/config/navigation.php`, `…/config/ui.php` → `config/`
- [ ] Update `vite.config.js` inputs to include the new css/js entry.
- [ ] **Critical for auto-update:** commit the **compiled** `public/build/**`.
      Customers receive files, not a build step (see §3).
- [ ] Adapt `layouts/app.blade.php` to `@extends` form (§1.2).
- [ ] Verify on a scratch route: a `<x-buttons.button>` renders styled in light +
      dark, and the old UI is **completely unaffected**.
- Commit: `chore(ui): install Taskify v2 design system foundation`

### Phase 1 — App shell + navigation (2–3 days)
- [ ] Rebuild `config/navigation.php` from the real menu (rail + panels + titles).
- [ ] Add permission gating to `<x-navigation.sidebar>` / `context-panel`.
- [ ] Port workspace switcher + menu search into the shell.
- [ ] Wire ⌘K command palette to the existing global search endpoint.
- [ ] Put existing "Create / +"-style primary actions into the header actions slot.
- Commit: `feat(ui): v2 app shell (rail + context panel + header) with permissions`

### Phase 2 — Primitive swaps, app-wide (3–5 days, several commits)
Lowest-risk first, each its own commit. These are global helper components used
on every page, so doing them early de-risks page migration:
- [ ] Buttons → `<x-buttons.button>` / `icon-button`
- [ ] Badges / chips / tags / status pills → `<x-badges.*>`
- [ ] Form controls → `<x-forms.field>` + `input|select|textarea|switch|checkbox|radio`
- [ ] Avatars → `<x-shared.avatar>` / `avatar-stack`
- [ ] Toasts: replace Toastr calls (and the layout's session-flash toasts) with `Toast.*`
- [ ] Modals/offcanvas: swap `data-bs-toggle` → kit `data-toggle`/`data-target`

### Phase 3 — Core screens (build-order priority) (4–7 days)
Port using the kit example pages as the exact pattern. **View only; controllers
untouched.** One screen per commit:
- [ ] **Auth** (login, register, forgot/reset) ← `pages/auth/login.blade.php`
- [ ] **Dashboard** ← `pages/dashboard/index.blade.php` (metric strip, charts, schedule)
- [ ] **Projects + Tasks** ← `pages/projects/index.blade.php` (board + task drawer)
- [ ] **Settings / Profile** ← `pages/settings/profile.blade.php`
- [ ] **Appearance** ← `pages/settings/appearance.blade.php` (localStorage-only)

### Phase 4 — Remaining modules (the bulk; group by similarity) (2–4 weeks)
Every remaining module reuses the Phase-2 primitives + `x-data.table` +
`x-cards.card` + charts. Suggested batches (each batch = a few commits):
- [ ] **CRM:** Leads, Lead forms/sources/stages, Clients, Estimates/Invoices, Payments
- [ ] **HRMS:** Users, Roles, Candidates, Interviews, Contracts, Payslips,
      Allowances, Deductions, Taxes, **Leave** (requests/balances/allowances)
- [ ] **Work:** Task lists, Todos, Time trackers/timesheets, Meetings, Notes, Tags,
      Priorities, Statuses, Custom fields
- [ ] **Comms:** Chat (Chatify), Email/Mail, Email templates, Notifications
- [ ] **Platform:** Calendar, File manager, Activity log, Reports, Bulk import,
      Settings (all tabs), Plugins, Languages
- [ ] Exceptions handled as in §1.4 (TinyMCE, FullCalendar, frappe-gantt, kanban DnD).

### Phase 5 — Personalization (½ day)
- [ ] Appearance page live: theme · accent hue · active-rail style · density.
- [ ] Persist to `localStorage['taskify.appearance']` (no DB). `appearance.js`
      already does the client half.
- [ ] Minimize theme-flash: write an inline `<script>` in the layout `<head>`
      that reads `localStorage` and sets `data-theme` + `--signal*` **before**
      first paint. (No server round-trip, no DB.)

### Phase 6 — Cutover, QA, packaging (3–5 days)
- [ ] Confirm **every** page extends `layouts/app` (grep for stray `@extends('layout')`).
- [ ] Remove legacy `layout.blade.php` + dead `public/assets` Bootstrap/jQuery libs
      **only after** the grep is clean. Keep plugin assets still in use
      (TinyMCE, FullCalendar, gantt, Dragula).
- [ ] Responsive sweep (xs/sm/md/lg/xl), dark-mode parity, a11y pass (focus rings,
      `aria-invalid`, dialog roles, keyboard nav).
- [ ] Cross-browser: Safari/Firefox/Chrome (OKLCH, backdrop-filter).
- [ ] Build the update package (see §3) and dry-run it on a **copy of a real
      customer DB snapshot** to prove zero data changes.

---

## 3. Auto-update & release packaging (the operational risk)

Your updater (`siddharthgor/update-file-generator`, `app:make-update`) ships
**git-changed files + migrations** as a zip customers apply. Two things matter:

1. **Ship compiled assets.** The new UI requires a Vite build, but customers
   **won't run `npm run build`**. So the build output **`public/build/**` must be
   committed and included** in the update package, and the layout must reference
   the built manifest (`@vite` resolves to `public/build` in production). Verify
   `public/build/manifest.json` + hashed assets are in the package.
2. **Expand the updater config.** `config/update-file-generator.php` currently has
   empty `archiveable_dirs` / `single_files`. For this release add (at minimum):
   - `resources/views` (archived), `resources/css`, `resources/js`,
     `public/build`, `config/navigation.php`, `config/ui.php`, `vite.config.js`,
     `package.json`.
   - Keep `truncate_tables` empty and ship **no migrations** → guarantees the
     update is **files-only** and **cannot alter customer data**.
3. **No `.env`/storage in package** (already excluded) → customer settings, logos,
   uploads, and DB stay as-is.

> **Data-safety proof for the release:** because the package contains **zero
> migrations and zero SQL**, applying it changes only PHP/CSS/JS/asset files. The
> database is never opened. This is the concrete guarantee that "existing users'
> data won't be gone."

---

## 4. Risk register

| Risk | Likelihood | Mitigation |
|------|-----------|------------|
| New `base.css` breaks old UI if globally loaded | High | Dual-layout isolation (§1.1); only one CSS world per page until cutover |
| Customers don't rebuild assets → blank styles | High | Commit & ship compiled `public/build/**` (§3) |
| Bootstrap-Table → `x-data.table` loses export/server-side pagination | High | Re-implement via slots + existing endpoints; QA each table; keep Bootstrap-Table for the few complex grids if needed |
| Permission-gated menu items leak after nav rebuild | Med | Add `can`/permission key to sidebar/panel; diff against old menu |
| TinyMCE/FullCalendar/gantt/kanban have no kit equivalent | Certain | Documented exceptions (§1.4): restyle-and-keep |
| Theme flash (FOUC) without server persistence | Med | Inline pre-paint `data-theme` script from localStorage (§Phase 5) |
| Big-bang full release magnifies any single regression | Med-High | Build screen-by-screen, full QA matrix, staging dry-run on customer DB snapshot before publishing the update |
| OKLCH / backdrop-filter on old browsers | Low | Evergreen target; add hex fallback layer if support tickets appear |

---

## 5. Testing & rollback

- **Per-screen:** visual diff vs Style Guide, light+dark, keyboard nav, the
  screen's primary action (create/edit/delete) still hits the same controller and
  succeeds.
- **Regression:** run the existing test suite (`php artisan test` / Pest) after
  each phase — logic is untouched, so tests must stay green.
- **Staging dry-run:** restore a real customer DB snapshot, apply the generated
  update zip, confirm row counts unchanged and the app boots on the new UI.
- **Rollback:** until cutover, revert a screen by restoring its `@extends('layout')`.
  Post-cutover, the previous release zip is the rollback artifact.

---

## 6. Definition of done

- [ ] Every production screen renders through `layouts/app` (no stray `@extends('layout')`).
- [ ] No screen loads the legacy Bootstrap stylesheet.
- [ ] Dark mode + accent hue + density switch live from Appearance (localStorage).
- [ ] All permission gates intact; all `get_label()` strings intact; all routes intact.
- [ ] Update package contains compiled assets, **zero migrations, zero SQL**.
- [ ] Staging dry-run on a customer DB snapshot proves **no data change**.
- [ ] Existing test suite green.

---

## 7. Rough timeline

| Phase | Effort |
|-------|--------|
| 0 Foundation | ½–1 day |
| 1 Shell + nav | 2–3 days |
| 2 Primitives | 3–5 days |
| 3 Core screens | 4–7 days |
| 4 All remaining modules | 2–4 weeks |
| 5 Personalization | ½ day |
| 6 Cutover + QA + packaging | 3–5 days |
| **Total** | **~5–8 weeks** for the full big-bang release |
