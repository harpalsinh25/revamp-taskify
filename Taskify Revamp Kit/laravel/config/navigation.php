<?php

/**
 * Navigation source of truth. Both the rail (left icon sidebar) and the
 * context panel (per-module submenu) read from here, so adding a module
 * is a one-line change.
 */

return [
    'rail' => [
        ['id' => 'dashboard', 'icon' => 'home',     'label' => 'Dashboard', 'route' => 'dashboard'],
        ['id' => 'projects',  'icon' => 'columns',  'label' => 'Projects',  'route' => 'projects.index'],
        ['id' => 'leads',     'icon' => 'target',   'label' => 'Leads',     'route' => 'leads.index'],
        ['id' => 'finance',   'icon' => 'wallet',   'label' => 'Finance',   'route' => 'finance.index'],
        ['id' => 'hrms',      'icon' => 'users',    'label' => 'HRMS',      'route' => 'hrms.index'],
        '_divider',
        ['id' => 'chat',      'icon' => 'msg',      'label' => 'Chat',      'route' => 'chat.index', 'badge' => 3],
        ['id' => 'mail',      'icon' => 'mail',     'label' => 'Email',     'route' => 'mail.index'],
        ['id' => 'calendar',  'icon' => 'calendar', 'label' => 'Calendar',  'route' => 'calendar.index'],
        ['id' => 'notes',     'icon' => 'book',     'label' => 'Notes',     'route' => 'notes.index'],
        ['id' => 'files',     'icon' => 'folder',   'label' => 'Files',     'route' => 'files.index'],
        '_divider',
        ['id' => 'activity',  'icon' => 'activity', 'label' => 'Activity',  'route' => 'activity.index'],
        ['id' => 'settings',  'icon' => 'settings', 'label' => 'Settings',  'route' => 'settings.index'],
    ],

    /**
     * Context-panel sections, keyed by rail-id. Each value is an array of
     * sections with optional label + items[{icon,label,route,active?}].
     */
    'panels' => [
        'dashboard' => [
            ['label' => 'Overview', 'items' => [
                ['icon' => 'home',     'label' => 'Today',          'route' => 'dashboard'],
                ['icon' => 'activity', 'label' => 'Activity feed',  'route' => 'activity.index'],
                ['icon' => 'inbox',    'label' => 'Inbox',          'route' => 'inbox.index'],
            ]],
            ['label' => 'Pinned', 'items' => [
                ['icon' => 'columns',  'label' => 'Brand Refresh',  'route' => 'projects.show', 'route_params' => ['project' => 'brand-refresh']],
                ['icon' => 'target',   'label' => 'Q2 Pipeline',    'route' => 'leads.index'],
            ]],
        ],
        'projects' => [
            ['label' => 'Workspace', 'items' => [
                ['icon' => 'columns',  'label' => 'All projects',   'route' => 'projects.index'],
                ['icon' => 'star',     'label' => 'My tasks',       'route' => 'projects.mine'],
                ['icon' => 'archive',  'label' => 'Archive',        'route' => 'projects.archive'],
            ]],
        ],
        'settings' => [
            ['label' => 'Account', 'items' => [
                ['icon' => 'user',     'label' => 'Profile',        'route' => 'settings.profile'],
                ['icon' => 'sun',      'label' => 'Appearance',     'route' => 'settings.appearance'],
                ['icon' => 'shield',   'label' => 'Security',       'route' => 'settings.security'],
                ['icon' => 'bell',     'label' => 'Notifications',  'route' => 'settings.notifications'],
            ]],
            ['label' => 'Workspace', 'items' => [
                ['icon' => 'users',    'label' => 'Members',        'route' => 'settings.members'],
                ['icon' => 'wallet',   'label' => 'Billing',        'route' => 'settings.billing'],
                ['icon' => 'key',      'label' => 'API & tokens',   'route' => 'settings.api'],
            ]],
        ],
    ],

    /**
     * Page title + subtitle map. The header reads from here when not given
     * explicit props.
     */
    'titles' => [
        'dashboard' => ['title' => 'Today',     'subtitle' => 'Workspace'],
        'projects'  => ['title' => 'Projects',  'subtitle' => 'Workspace'],
        'leads'     => ['title' => 'Pipeline',  'subtitle' => 'Sales'],
        'finance'   => ['title' => 'Finance',   'subtitle' => 'Books'],
        'hrms'      => ['title' => 'People',    'subtitle' => 'HRMS'],
        'settings'  => ['title' => 'Settings',  'subtitle' => 'Workspace'],
    ],
];
