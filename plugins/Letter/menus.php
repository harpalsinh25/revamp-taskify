<?php

/**
 * Letter Plugin - Menu Configuration
 *
 * This file returns an array defining the menu structure for the Letter plugin.
 * Each menu and submenu includes properties such as id, label, url, icon, class, category, and visibility.
 * Visibility and active states are determined based on user permissions and current route.
 */

return [
    [
        'id' => 'letters',
        'label' => get_label('letters', 'Letters'),
        'url' => '',
        'icon' => 'bx bx-envelope',
        'class' => 'menu-item' . (request()->is('letters*') ? ' active open' : ''),
        'category' => 'utilities',
        'show' => isAdminOrHasAllDataAccess() ? 1 : 0,
        'badge' => '<span class="badge rounded-pill bg-label-info text-uppercase ms-2">' . get_label('plugin', 'Plugin') . '</span>',
        'submenus' => [
            [
                'id' => 'generate_letters',
                'label' => get_label('generate', 'Generate'),
                'url' => route('letters.index'),
                'class' => 'menu-item' . (request()->is('letters/generate') ? ' active open' : ''),
                'show' => isAdminOrHasAllDataAccess() ? 1 : 0,
            ],
            [
                'id' => 'create_template',
                'label' => get_label('templates', 'Templates'),
                'url' => route('letter-templates.index'),
                'class' => 'menu-item' . (request()->is('letters/templates*') ? ' active open' : ''),
                'show' => isAdminOrHasAllDataAccess() ? 1 : 0,
            ],
        ],
    ],
];
