<?php

use App\Models\User;
use App\Models\Workspace;
use App\Models\LeaveRequest;
use Chatify\ChatifyMessenger;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

$user = getAuthenticatedUser();
if (isAdminOrHasAllDataAccess()) {
    $workspaces = Workspace::all()->take(5);
    $total_workspaces = Workspace::count();
} else {
    $workspaces = $user->workspaces;
    $total_workspaces = count($workspaces);
    $workspaces = $user->workspaces->skip(0)->take(5);
}
$current_workspace_id = getWorkspaceId();
$current_workspace = Workspace::find($current_workspace_id);
// Check if the current workspace is in the list of workspaces retrieved
$workspace_ids = $workspaces->pluck('id')->toArray();
if (!in_array($current_workspace_id, $workspace_ids)) {
    // If not, prepend the current workspace to the list
    $current_workspace = Workspace::find($current_workspace_id);
    $workspaces->prepend($current_workspace);
    // If there are more than 5 workspaces, remove the last one
    if ($workspaces->count() > 5) {
        $workspaces->pop();
    }
}
$current_workspace_title = $current_workspace->title ?? 'No workspace(s) found';
$messenger = new ChatifyMessenger();
$unread = $messenger->totalUnseenMessages();
$pending_todos_count = $user->todos(0)->count();
$ongoing_meetings_count = $user->meetings('ongoing')->count();
$query = LeaveRequest::where('status', 'pending')->where('workspace_id', $current_workspace_id);
if (!is_admin_or_leave_editor()) {
    $query->where('user_id', $user->id);
}
$pendingLeaveRequestsCount = $query->count();
?>

@php
    // ---------------------------------------------------------------
    // Menu data (UNCHANGED logic): real permission-gated menus, plugin
    // menus, and the per-user saved ordering from the menu_orders table.
    // ---------------------------------------------------------------
    $menuOrder = json_decode(
        DB::table('menu_orders')
            ->where(getGuardName() == 'web' ? 'user_id' : 'client_id', getAuthenticatedUser()->id)
            ->value('menu_order'),
        true,
    );

    $menus = getMenus();
    $pluginMenus = []; // Initialize safely

    $pluginPath = base_path('plugins');

    if (File::exists($pluginPath)) {
        $pluginDirs = glob($pluginPath . '/*', GLOB_ONLYDIR);

        foreach ($pluginDirs as $pluginDir) {
            $pluginJsonFile = $pluginDir . '/plugin.json';

            if (File::exists($pluginJsonFile)) {
                $pluginData = json_decode(File::get($pluginJsonFile), true);

                // Check if plugin is enabled
                if (!empty($pluginData['enabled'])) {
                    $menuFile = $pluginDir . '/menus.php';

                    if (File::exists($menuFile)) {
                        $pluginMenuItems = include $menuFile;

                        if (is_array($pluginMenuItems)) {
                            $pluginMenus = array_merge($pluginMenus, $pluginMenuItems);
                        }
                    }
                }
            }
        }
    }

    // Merge your core menus with plugin menus
    $menus = array_merge($menus, $pluginMenus);
    // Sort menus based on saved order
    $sortedMenus = [];

    if ($menuOrder) {
        foreach ($menuOrder as $categoryData) {
            // Ensure 'menus' key exists in categoryData
            if (!isset($categoryData['menus']) || !is_array($categoryData['menus'])) {
                continue;
            }

            foreach ($categoryData['menus'] as $order) {
                if (!isset($order['id'])) {
                    continue; // Skip if id is missing
                }

                // Find menu by ID from $menus
                $menu = collect($menus)->firstWhere('id', $order['id']);
                if ($menu) {
                    // Sort submenus if present
                    if (!empty($order['submenus'])) {
                        $submenuIds = collect($order['submenus'])->pluck('id')->toArray();
                        $menu['submenus'] = collect($menu['submenus'] ?? [])
                            ->whereNotNull('id')
                            ->sortBy(function ($submenu) use ($submenuIds) {
                                return array_search($submenu['id'], $submenuIds) ?? PHP_INT_MAX;
                            })
                            ->toArray();
                    }

                    $sortedMenus[] = $menu;
                }
            }
        }
    } else {
        // Use default order if no saved menu order
        $sortedMenus = $menus;
    }

    // Group menus by category
    $groupedMenus = collect($sortedMenus)->groupBy('category');

    // ---------------------------------------------------------------
    // v2 transform (presentation only): reshape the SAME menu data into
    // a rail (one icon per category) + a context panel (per category).
    // Permissions ($menu['show']), badges and active state all flow
    // straight through from the data above — nothing is recomputed.
    // ---------------------------------------------------------------
    $tkCategoryIcons = [
        'dashboard'                    => 'home',
        'projects_and_task_management' => 'columns',
        'team'                         => 'users',
        'finance'                      => 'wallet',
        'hrms'                         => 'briefcase',
        'email'                        => 'mail',
        'reports'                      => 'file-text',
        'todos'                        => 'check-square',
        'notes'                        => 'edit',
        'asset'                        => 'box',
        'social_media'                 => 'share',
        'file_manager'                 => 'folder',
        'utilities'                    => 'list',
        'settings'                     => 'settings',
    ];
    $tkIconPaths = [
        'home'         => '<path d="M3 11 12 3l9 8"/><path d="M5 9v11h5v-7h4v7h5V9"/>',
        'columns'      => '<path d="M4 4h6v16H4zM14 4h6v16h-6z"/>',
        'users'        => '<circle cx="9" cy="8" r="3"/><path d="M3 20c0-3 3-5 6-5s6 2 6 5"/><circle cx="17" cy="9" r="2.5"/><path d="M21 19c0-2-2-3.5-4-3.5"/>',
        'wallet'       => '<path d="M3 7a2 2 0 0 1 2-2h13a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z"/><path d="M16 13h2"/>',
        'list'         => '<path d="M3 6h18M3 12h18M3 18h18"/>',
        'settings'     => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1A1.7 1.7 0 0 0 4.6 9a1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1Z"/>',
        'briefcase'    => '<rect width="20" height="14" x="2" y="7" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>',
        'mail'         => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><path d="m22 6-10 7L2 6"/>',
        'file-text'    => '<path d="M14 2H6a2 2 0 0 0-2 2v16c0 1.1.9 2 2 2h12a2 2 0 0 0 2-2V8l-6-6z"/><path d="M14 3v5h5M16 13H8M16 17H8M10 9H8"/>',
        'check-square' => '<path d="m9 11 3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>',
        'edit'         => '<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4Z"/>',
        'box'          => '<path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>',
        'share'        => '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.6 13.5 6.8 4M15.4 6.5l-6.8 4"/>',
        'folder'       => '<path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>',
    ];

    // helper: is a url a real navigation target (not a placeholder)?
    $tkRealUrl = function ($url) {
        $url = trim((string) $url);
        return $url !== '' && stripos($url, 'javascript:') === false && $url !== '#';
    };

    // Build per-category rail + pane structures, preserving order + permissions.
    $tkRail = [];
    foreach ($groupedMenus as $category => $catMenus) {
        // visible top-level menus (same rule as the legacy menu)
        $visibleMenus = collect($catMenus)->filter(function ($menu) {
            return !isset($menu['show']) || $menu['show'] === 1;
        });
        if ($visibleMenus->isEmpty()) {
            continue;
        }

        // category active if any of its visible menus is active
        $isActive = $visibleMenus->contains(function ($menu) {
            return isset($menu['class']) && strpos($menu['class'], 'active') !== false;
        });

        // badge count for the rail = sum of numeric badges in the category
        $badgeCount = 0;
        foreach ($visibleMenus as $menu) {
            if (!empty($menu['badge'])) {
                $badgeCount += (int) trim(strip_tags($menu['badge']));
            }
        }

        // pick a real landing URL (first menu, then first submenu)
        $landing = '#';
        foreach ($visibleMenus as $menu) {
            if ($tkRealUrl($menu['url'] ?? '')) { $landing = $menu['url']; break; }
            foreach ($menu['submenus'] ?? [] as $sub) {
                if ((!isset($sub['show']) || $sub['show'] === 1) && $tkRealUrl($sub['url'] ?? '')) {
                    $landing = $sub['url']; break 2;
                }
            }
        }

        $iconName = $tkCategoryIcons[$category] ?? 'list';
        $tkRail[] = [
            'category' => $category,
            'label'    => get_label($category, ucfirst(str_replace('_', ' ', $category))),
            'icon'     => $tkIconPaths[$iconName] ?? $tkIconPaths['list'],
            'url'      => $landing,
            'active'   => $isActive,
            'badge'    => $badgeCount,
            'menus'    => $visibleMenus,
        ];
    }

    // default active = first category if none matched the current route
    $tkActiveRow = collect($tkRail)->firstWhere('active', true);
    $tkActive = $tkActiveRow['category'] ?? ($tkRail[0]['category'] ?? null);

    $tkUserPhoto = ($user->photo && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->photo))
        ? asset('storage/' . $user->photo)
        : asset('storage/photos/no-image.jpg');
@endphp

{{-- ============================ RAIL ============================ --}}
<aside class="tk-rail" aria-label="{{ get_label('primary_navigation', 'Primary navigation') }}">
    <a href="{{ url('home') }}" class="tk-rail-brand" title="{{ $general_settings['company_title'] ?? 'Taskify' }}">
        <img src="{{ asset($general_settings['favicon'] ?? 'storage/logos/default_favicon.png') }}" alt="" />
    </a>

    @foreach ($tkRail as $item)
        <a href="{{ $item['url'] }}"
            class="tk-rail-btn"
            data-panel="{{ $item['category'] }}"
            data-active="{{ $item['active'] ? 'true' : 'false' }}"
            title="{{ $item['label'] }}"
            aria-label="{{ $item['label'] }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                {!! $item['icon'] !!}
            </svg>
            @if ($item['badge'] > 0)
                <span class="tk-rail-badge">{{ $item['badge'] > 99 ? '99+' : $item['badge'] }}</span>
            @endif
        </a>
    @endforeach

    <div class="tk-rail-foot">
        <a href="{{ url('preferences') }}" class="tk-rail-btn" title="{{ get_label('preferences', 'Preferences') }}"
            aria-label="{{ get_label('preferences', 'Preferences') }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                {!! $tkIconPaths['settings'] !!}
            </svg>
        </a>
        <a href="{{ url('/account/' . $user->id) }}" class="tk-rail-avatar"
            title="{{ Str::limit($user->first_name . ' ' . $user->last_name, 24) }}">
            <img src="{{ $tkUserPhoto }}" alt="" />
        </a>
    </div>
</aside>

{{-- ====================== CONTEXT PANEL ======================= --}}
<aside class="tk-panel" id="tk-context-panel" aria-label="{{ get_label('secondary_navigation', 'Secondary navigation') }}">
    {{-- Workspace switcher (logic preserved from the legacy menu) --}}
    <div class="tk-panel-head">
        <div class="btn-group dropend tk-ws w-100">
            <button type="button"
                class="btn {{ getAuthenticatedUser()->hasVerifiedEmail() || getAuthenticatedUser()->hasRole('admin') ? 'dropdown-toggle' : '' }}"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="text-truncate">{{ strlen($current_workspace_title) > 20 ? substr($current_workspace_title, 0, 20) . '...' : $current_workspace_title }}</span>
            </button>
            @if (getAuthenticatedUser()->hasVerifiedEmail() || getAuthenticatedUser()->hasRole('admin'))
                <ul class="dropdown-menu">
                    @if ($total_workspaces > 0)
                        @foreach ($workspaces as $workspace)
                            <?php $checked = $workspace->id == $current_workspace_id ? "<i class='menu-icon tf-icons bx bx-check-square text-primary'></i>" : "<i class='menu-icon tf-icons bx bx-square text-solid'></i>"; ?>
                            <li>
                                <a class="dropdown-item" href="{{ url('/workspaces/switch/' . $workspace->id) }}">
                                    {!! $checked !!}
                                    {{ $workspace->title }}
                                    @if ($workspace->is_primary)
                                        <span class="badge bg-success">{{ get_label('primary', 'Primary') }}</span>
                                    @endif
                                    @if ($user->default_workspace_id == $workspace->id)
                                        <span class="badge bg-primary">{{ get_label('default', 'Default') }}</span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                        <li><hr class="dropdown-divider" /></li>
                    @endif
                    @if ($user->can('manage_workspaces'))
                        <li>
                            <a class="dropdown-item" href="{{ url('workspaces') }}">
                                <i class='menu-icon tf-icons bx bx-bar-chart-alt-2 text-success'></i>
                                {!! get_label('manage_workspaces', 'Manage workspaces') !!}
                                {!! $total_workspaces > 5 ? '<span class="badge bg-primary"> + ' . ($total_workspaces - 5) . '</span>' : '' !!}
                            </a>
                        </li>
                        @if ($user->can('create_workspaces'))
                            <li>
                                <span data-bs-toggle="modal" data-bs-target="#createWorkspaceModal">
                                    <a class="dropdown-item" href="javascript:void(0);">
                                        <i class='menu-icon tf-icons bx bx-plus text-warning'></i>
                                        {!! get_label('create_workspace', 'Create workspace') !!}
                                    </a>
                                </span>
                            </li>
                        @endif
                        @if ($user->can('edit_workspaces'))
                            <li>
                                <a class="dropdown-item edit-workspace" href="javascript:void(0);" data-id="{{ getWorkspaceId() }}">
                                    <i class='menu-icon tf-icons bx bx-edit text-primary'></i>
                                    {!! get_label('edit_workspace', 'Edit workspace') !!}
                                </a>
                            </li>
                        @endif
                    @endif
                    @if ($current_workspace)
                        <li>
                            <a class="dropdown-item" href="#" id="remove-participant">
                                <i class='menu-icon tf-icons bx bx-exit text-danger'></i>
                                {!! get_label('remove_me_from_workspace', 'Remove me from workspace') !!}
                            </a>
                        </li>
                    @endif
                </ul>
            @endif
        </div>
    </div>

    {{-- Menu search (filters the active pane, behaviour wired in custom.js) --}}
    <div class="tk-panel-search" style="position: relative;">
        <input type="text" id="menu-search" autocomplete="off"
            placeholder="{{ get_label('search_menu', 'Search Menu...') }} (/)">
        <i class="bx bx-x" id="menu-search-clear" style="display: none; position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; font-size: 18px; color: var(--bs-body-color);"></i>
    </div>

    {{-- Per-category panes --}}
    <div class="tk-panel-body">
        @foreach ($tkRail as $item)
            <div class="tk-panel-pane" data-panel="{{ $item['category'] }}" @if ($item['category'] !== $tkActive) hidden @endif>
                <div class="tk-panel-title">{{ $item['label'] }}</div>

                @foreach ($item['menus'] as $menu)
                    @php
                        $menuActive = isset($menu['class']) && strpos($menu['class'], 'active') !== false;
                        $visibleSubs = collect($menu['submenus'] ?? [])->filter(function ($sub) {
                            return !isset($sub['show']) || $sub['show'] === 1;
                        });
                    @endphp

                    @if ($visibleSubs->isNotEmpty())
                        <div class="tk-panel-group">
                            <div class="tk-panel-label">{{ $menu['label'] }}</div>
                            @foreach ($visibleSubs as $sub)
                                @php $subActive = isset($sub['class']) && strpos($sub['class'], 'active') !== false; @endphp
                                <a class="tk-panel-item" href="{{ $sub['url'] ?? 'javascript:void(0)' }}"
                                    data-active="{{ $subActive ? 'true' : 'false' }}">
                                    @if (!empty($sub['icon']))<i class="{{ $sub['icon'] }}"></i>@endif
                                    <span>{{ $sub['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <a class="tk-panel-item" href="{{ $menu['url'] ?? 'javascript:void(0)' }}"
                            data-active="{{ $menuActive ? 'true' : 'false' }}">
                            @if (!empty($menu['icon']))<i class="{{ $menu['icon'] }}"></i>@endif
                            <span>{{ $menu['label'] }}</span>
                            @if (!empty($menu['badge'])){!! $menu['badge'] !!}@endif
                        </a>
                    @endif
                @endforeach
            </div>
        @endforeach
    </div>
</aside>
