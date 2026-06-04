<?php

/**
 * UI variant maps. Components read from here so we keep a single
 * source of truth for naming + extending palettes.
 */

return [
    'button' => [
        'variants' => ['primary', 'secondary', 'ghost', 'outline', 'danger', 'success'],
        'sizes'    => ['sm', 'md', 'lg'],
    ],
    'badge' => [
        'tones' => ['neutral', 'primary', 'ok', 'warn', 'err', 'info'],
    ],
    'alert' => [
        'types' => ['info', 'success', 'warn', 'error'],
    ],
    'status' => [
        // task-board statuses → CSS class suffix
        'progress' => 'progress',
        'review'   => 'review',
        'done'     => 'done',
        'blocked'  => 'blocked',
    ],
];
