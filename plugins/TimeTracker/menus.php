<?php

/**
 * Time Tracker Plugin - Menu Configuration
 *
 * This file returns an array defining the menu structure for the Time Tracker plugin.
 * Each menu and submenu includes properties such as id, label, url, icon, class, category, and visibility.
 * Visibility and active states are determined based on user permissions and current route.
 */

return [
    [
        'id' => 'team_monitoring_and_productivity_tracker',
        'label' => get_label('team_insights', 'Team Insights'),
        'url' => route('timetracker.index'),
        'icon' => 'bx bx-alarm',
        'class' => 'menu-item' . (request()->is('timetracker*') ? ' active open' : ''),
        'category' => 'team_monitoring_and_productivity_tracker',
        'badge' => '<span class="badge rounded-pill bg-label-info text-uppercase ms-2">' . get_label('plugin', 'Plugin') . '</span>',
        'show' =>  1,
        'submenus' => [
            [
                'id' => 'productivity_dashboard',
                'label' => get_label('productivity_dashboard', 'Productivity Dashboard'),
                'url' => route('timetracker.index'),
                'icon' => 'bx bx-grid-alt',
                'class' => 'menu-item' . (request()->is('timetracker') ? ' active open' : ''),
                'show' => isAdminOrHasAllDataAccess() ? 1 : 0,
            ],
            [
                'id' => 'screenshots',
                'label' => get_label('screenshot_gallery', 'Screenshot Gallery'),
                'url' => route('timetracker.screenshots'),
                'icon' => 'bx bx-image',
                'class' => 'menu-item' . (request()->is('timetracker/screen-shots*') ? ' active open' : ''),
                'show' => isAdminOrHasAllDataAccess() ? 1 : 0,
            ],
            [
                'id' => 'time_and_attendance',
                'label' => get_label('time_and_attendance', 'Time And Attendance'),
                'url' => route('time_and_attendance.index'),
                'icon' => 'bx bx-calendar-check',
                'class' => 'menu-item' . (request()->is('timetracker/time-and-attendance*') ? ' active open' : ''),
                'show' => isUser() ?  1 : 0,
            ],
            [
                'id' => 'manual_time',
                'label' => get_label('manual_time', 'Manual Time'),
                'url' => route('timetracker.manual_time.index'),
                'icon' => 'bx bx-edit-alt',
                'class' => 'menu-item' . (request()->is('timetracker/manual-time*') ? ' active open' : ''),
                'show' => isUser() ? 1 : 0,
            ],
            [
                'id' => 'configuration',
                'label' => get_label('configuration', 'Configuration'),
                'url' => route('timetracker.configuration'),
                'icon' => 'bx bx-cog',
                'class' => 'menu-item' . (request()->is('timetracker/configuration') ? ' active open' : ''),
                'show' => isAdminOrHasAllDataAccess() ? 1 : 0,
            ],
            [
                'id' => 'downloads',
                'label' => get_label('downloads', 'Downloads'),
                'url' => route('timetracker.downloads.index'),
                'icon' => 'bx bx-download',
                'class' => 'menu-item' . (request()->is('timetracker/downloads*') ? 'active open' : ''),
                'show' => 1
            ]
        ],
    ],
];
