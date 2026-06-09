<?php

return [
    [
        'id' => 'Social Media Management',
        'label' => get_label('social_media', 'Social Media'),
        'class' => 'menu-item' . (request()->is('social-media-scheduler/*') || request()->is('social-media-scheduler') ? ' active open' : ''),
        'category' => 'social_media',
        'show' => getAuthenticatedUser()->hasRole('admin') || ($user->can('manage_posts')) ? 1 : 0,
        'badge' => '<span class="badge rounded-pill bg-label-info text-uppercase ms-2">' . get_label('plugin', 'Plugin') . '</span>',
        'icon' => 'bx bx-share-alt',
        'submenus' => [
            [
                'id' => 'social_posts',
                'label' => get_label('posts', 'Posts'),
                'url' => url('social-media-scheduler'),
                'class' => 'menu-item' . (request()->is('social-media-scheduler') ? ' active' : ''),
                'show' => isAdminOrHasAllDataAccess() || ($user->can('manage_posts')) ? 1 : 0,
            ],
            [
                'id' => 'create_post',
                'label' => get_label('create_post', 'Create Post'),
                'url' => url('social-media-scheduler/create'),
                'class' => 'menu-item' . (request()->is('social-media-scheduler/create') ? ' active' : ''),
                'show' => isAdminOrHasAllDataAccess() || ($user->can('create_post')) ? 1 : 0,
            ],
            [
                'id' => 'social_calendar',
                'label' => get_label('calendar', 'Calendar'),
                'url' => url('social-media-scheduler/calendar'),
                'class' => 'menu-item' . (request()->is('social-media-scheduler/calendar') ? ' active' : ''),
                'show' => isAdminOrHasAllDataAccess() || $user->can('manage_posts') ? 1 : 0,
            ],
            [
                'id' => 'social_analytics',
                'label' => get_label('analytics', 'Analytics'),
                'url' => url('social-media-scheduler/analytics'),
                'class' => 'menu-item' . (request()->is('social-media-scheduler/analytics') ? ' active' : ''),
                'show' => isAdminOrHasAllDataAccess() || $user->can('manage_posts') ? 1 : 0,
            ],
            [
                'id' => 'social_settings',
                'label' => get_label('settings', 'Settings'),
                'url' => url('social-media-scheduler/social-settings'),
                'class' => 'menu-item' . (request()->is('social-media-scheduler/social-settings') ? ' active' : ''),
                'show' => isAdminOrHasAllDataAccess() || $user->can('manages_posts') ? 1 : 0,
            ],
        ],
    ],
];
