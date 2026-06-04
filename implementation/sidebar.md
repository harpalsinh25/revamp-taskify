# Taskify Sidebar Redesign Plan (Revamp Kit Style)

This plan details the steps required to redesign the Taskify sidebar layout using the double-sidebar structure (Left Icon Rail + Context Panel) from the Revamp Kit while fully preserving core dynamic features (dynamic permissions, plugins, custom sorting, badges, and workspaces).

---

## 1. Objectives

- **Visual Refresh**: Replace the single traditional vertical menu with the premium double-sidebar layout:
  1. **Left Icon Rail (`.rail`)**: Slim sidebar for primary section icons, brand logo, user avatar, and settings.
  2. **Context Panel (`.panel`)**: A secondary menu containing specific category sections and submenu links.
- **Maintain Core Logic**:
  - Keep dynamic permission checking (`$user->can()`).
  - Support the dynamic loading of plugin submenus from `/plugins`.
  - Preserve user-customized menu order.
  - Maintain real-time unread/pending badges.
  - Keep the workspace switching functionality fully operational.

---

## 2. File Architecture & Changes

### CSS Styles (`public/assets/css/custom.css`)
- **Add Design Tokens**: Include custom variables for sizes (`--rail-w: 56px;`, `--panel-w: 232px;`, `--cbar-h: 48px;`) and base properties.
- **Port Rail Styles**:
  - `.rail`, `.rail-brand`, `.rail-btn`, `.rail-badge`, `.rail-divider`, `.rail-foot`, `.rail-avatar`.
- **Port Panel Styles**:
  - `.panel`, `.panel-head`, `.panel-title`, `.panel-body`, `.panel-section`, `.panel-label`, `.panel-item`.
- **Responsive Layout**: Adjust the sidebar wrapper to gracefully transition to mobile layout (collapsing secondary panel on smaller screens).

### Layout Wrapper (`resources/views/layout.blade.php`)
- Modify the main layout structure wrapping `<x-menu />` to support side-by-side flex container for `.rail` and `.panel`.
- Adjust main content container padding to match the new width of `--rail-w + --panel-w`.

### PHP Menu Service (`app/Services/MenuService.php`)
- Retain the service as the single source of truth.
- Verify icons to make sure each menu item has a suitable icon class compatible with Sneat / Boxicons.

### Sidebar Component (`resources/views/components/menu.blade.php`)
Refactor the view into two side-by-side elements inside `<aside>`:

#### A. Left Rail Structure
```html
<div class="rail-container">
    <!-- Brand / Logo -->
    <a href="{{ url('home') }}" class="rail-brand">T</a>

    <!-- Primary Icons (e.g., Dashboard, Projects, Finance, HRMS, Settings) -->
    @foreach ($railItems as $item)
        <a href="{{ $item['url'] }}" class="rail-btn" data-active="{{ $item['active'] ? 'true' : 'false' }}" title="{{ $item['label'] }}">
            <i class="menu-icon tf-icons {{ $item['icon'] }}"></i>
            @if ($item['badge'])
                <span class="rail-badge">{!! $item['badge'] !!}</span>
            @endif
        </a>
    @endforeach

    <!-- Footer -->
    <div class="rail-foot">
        <a href="{{ url('settings/general') }}" class="rail-btn" title="Settings">
            <i class="bx bx-cog"></i>
        </a>
        <div class="rail-avatar">
            {{ mb_substr($user->name ?? 'U', 0, 2) }}
        </div>
    </div>
</div>
```

#### B. Context Panel Structure
```html
@if ($activeRailItem && $activeRailItem->hasSubmenus())
<div class="panel-container">
    <!-- Workspace Switcher -->
    <div class="panel-head">
        <span class="panel-title">{{ $activeRailItem['label'] }}</span>
    </div>
    
    <!-- Menu Search Input -->
    <div class="panel-search px-2 pb-2">
        <input type="text" id="menu-search" class="form-control form-control-sm" placeholder="Search...">
    </div>

    <!-- Category Groups / Submenus -->
    <div class="panel-body">
        @foreach ($activeRailItem['submenus'] as $submenu)
            <a class="panel-item" href="{{ $submenu['url'] }}" data-active="{{ $submenu['active'] ? 'true' : 'false' }}">
                <span>{{ $submenu['label'] }}</span>
            </a>
        @endforeach
    </div>
</div>
@endif
```

---

## 3. Implementation Steps

1. **Step 1: Write CSS Rules**
   Append the design tokens and layout classes for `.rail` and `.panel` to the bottom of [custom.css](file:///c:/Users/infin/OneDrive/Desktop/taskify/public/assets/css/custom.css).
   
2. **Step 2: Update HTML Container**
   Update [layout.blade.php](file:///c:/Users/infin/OneDrive/Desktop/taskify/resources/views/layout.blade.php) wrapper classes so that the main viewport expands and correctly positions the double sidebars next to the central content.

3. **Step 3: Refactor Sidebar Component**
   Reorganize [menu.blade.php](file:///c:/Users/infin/OneDrive/Desktop/taskify/resources/views/components/menu.blade.php):
   - Extract the active section dynamically by comparing the current route path.
   - Build a list of primary categories (e.g. Dashboard, Projects, Finance, HRM, Settings, Utilities, Team) to populate the Left Rail.
   - Extract submenus and category child items to populate the Context Panel for the active section.
   - Place the Workspace Dropdown at the top of the Context Panel.

4. **Step 4: JavaScript Enhancement**
   Update the menu toggle behavior in [main.js](file:///c:/Users/infin/OneDrive/Desktop/taskify/public/assets/js/main.js) (or inline script) to allow menu searching, workspace switching, and drawer toggling on mobile.

---

## 4. Verification Checklist

- [ ] Check sidebar rendering on desktop screens (Left Rail + Context Panel align correctly).
- [ ] Verify Workspace switcher dropdown opens and changes workspace correctly.
- [ ] Verify permissions: different user roles see only authorized options.
- [ ] Verify badges (unread chat messages, pending todos, pending leaves) render correctly.
- [ ] Check responsive layout: hides/collapses the sidebar on mobile and mobile hamburger button opens it.
- [ ] Verify menu search filter works on submenu links.
