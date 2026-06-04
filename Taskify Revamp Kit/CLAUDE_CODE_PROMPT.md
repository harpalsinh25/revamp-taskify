# Claude Code Prompt — Integrate the Taskify v2 Design System

> Copy everything below the line into Claude Code (run it from the root of your
> Laravel app, with this **`Taskify Revamp Kit/`** folder available — e.g. copied
> into the repo or referenced by path). Adjust the two PATH lines at the top.

---

## Context

You are integrating a **finalized, production-ready design system** into an existing
**Laravel + Blade** application called **Taskify**. The design system is delivered as
a kit of Blade components, CSS tokens, and vanilla JS modules. **Do not redesign
anything** — the visual language is locked. Your job is to wire it in cleanly and
migrate screens to it, screen by screen.

**Kit location:** `./Taskify Revamp Kit/`
**Laravel app root:** `./`  (the current repository)

Read these first, in order:
1. `Taskify Revamp Kit/PLAN.md` — the migration phases and definition of done.
2. `Taskify Revamp Kit/laravel/README.md` — architecture + every component's props.
3. Open `Taskify Revamp Kit/Style Guide.html` mentally as the visual reference
   (tokens, every component, every variant).

---

## Hard rules

- **Never invent new colors, fonts, spacing, or components.** Everything you need
  exists in the kit. If something seems missing, compose it from existing components
  rather than writing new CSS.
- **No inline styles** for anything a token or component covers. Use `var(--…)`
  tokens and component classes.
- **Reuse, don't duplicate.** If two screens need the same markup, it's a component.
- **Keep dark mode at parity** — only `[data-theme]` on `<html>` may switch themes;
  never fork CSS per theme beyond the token layer.
- **Accessibility is non-negotiable:** focus-visible rings, `aria-invalid` on bad
  fields, `role="dialog"`+`aria-modal` on overlays, keyboard nav on menus.
- Work in **small, reviewable commits**, one phase (or one screen) per commit.

---

## Task 0 — Install the foundation

1. Copy into the app (preserving structure):
   - `Taskify Revamp Kit/laravel/config/*` → `config/`
   - `Taskify Revamp Kit/laravel/resources/css/*` → `resources/css/`
   - `Taskify Revamp Kit/laravel/resources/js/*` → `resources/js/`
   - `Taskify Revamp Kit/laravel/resources/views/components/*` → `resources/views/components/`
   - `Taskify Revamp Kit/laravel/resources/views/layouts/*` → `resources/views/layouts/`
2. Update `vite.config.js` so the inputs include
   `resources/css/app.css` and `resources/js/app.js` (see README's Vite section).
3. Add `@vite(['resources/css/app.css','resources/js/app.js'])` to the app layout head.
4. Run `npm install && npm run build` (or `dev`) and confirm no build errors.
5. **Verify:** a scratch route rendering `<x-buttons.button variant="primary">Hi</x-buttons.button>`
   shows the styled button in both light and dark (`<html data-theme="dark">`).

Commit: `chore(ui): install Taskify v2 design system foundation`.

## Task 1 — App shell

1. Make the global layout extend `layouts/app.blade.php`.
2. Populate `config/navigation.php` with the app's **real** routes for the rail
   items and context-panel sections. Keep the data shape exactly as provided.
3. Wire the ⌘K command palette (`<x-overlays.command-palette>`) — feed it the
   navigable routes (it already defaults to the rail).
4. Put the existing primary actions into the header `actions` slot.

**Verify:** every top-level route highlights the correct rail item
(`data-active`) and the panel shows that module's sections.

Commit: `feat(ui): adopt v2 app shell (rail + panel + header)`.

## Task 2 — Swap primitives app-wide

Find-and-replace existing markup with components, in this order (each its own commit):
1. Buttons → `<x-buttons.button>` / `<x-buttons.icon-button>`.
2. Badges / chips / tags / status → `<x-badges.*>`.
3. Form controls → `<x-forms.field>` wrapping `<x-forms.input|select|textarea|switch|checkbox|radio>`.
4. Avatars → `<x-shared.avatar>` / `<x-shared.avatar-stack>`.

For each, grep the codebase for the old class names / patterns, replace, and
visually diff against the Style Guide.

## Task 3 — Migrate pages (one per commit)

Use the kit's example pages as the exact pattern to follow:
- Dashboard ← `pages/dashboard/index.blade.php`
- Projects (board + drawer) ← `pages/projects/index.blade.php`
- Settings (profile + appearance) ← `pages/settings/*.blade.php`
- Auth ← `pages/auth/login.blade.php`

For each screen: identify reusable sections → compose from existing components →
bind real data from the controller → keep the controller/business logic intact.
**Only the view layer changes.**

## Task 4 — Data + interactivity

- Tables → `<x-data.table>` with real `:columns` + `:rows`; use the `toolbar`
  and `footer` slots for search + pagination (`{{ $paginator->links() }}`).
- Charts → `<x-data.area-chart|donut|sparkline>` fed from controller data
  (hover tooltips are automatic).
- Multiselect → `<x-forms.multiselect name="..." endpoint="/api/.../search">`;
  implement the endpoint to return `{ "results": [{ "value", "label" }] }`.
- Modals / drawers → trigger with `data-toggle="modal|offcanvas" data-target="#id"`.
- Toasts → after a successful action, fire `Toast.success({ title, message })`.

## Task 5 — Personalization

- Ship `pages/settings/appearance.blade.php`.
- Persist the appearance prefs (theme, signalHue, activeStyle, density) on the
  authenticated user; on page load, render `<html data-theme="…">` and inline the
  saved `--signal*` hue so there's **no theme flash**.
- Keep `localStorage['taskify.appearance']` in sync for instant client updates
  (the `appearance.js` module already does the client half).

## Task 6 — QA & cutover

- Responsive pass at the documented breakpoints (xs/sm/md/lg/xl).
- Accessibility pass (Lighthouse a11y ≥ 95 on the main screens).
- Remove the legacy stylesheet once every screen is migrated.
- Update the team README to point at `Style Guide.html` as the living reference.

---

## Output expectations per task

For each task, respond with:
1. **Plan** — the files you'll touch and why.
2. **Diffs** — the actual edits (small, focused).
3. **Verification** — how you confirmed it (route hit, screenshot, or `php artisan` check).
4. **Commit message** — conventional-commits style.

Stop after each task for review before starting the next. Do **not** batch the
whole migration into one giant change.
