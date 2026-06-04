@props(['name', 'size' => 16, 'stroke' => 1.6])

@php
    // Static cache of icon path data. To add icons, append here.
    static $icons = null;
    if ($icons === null) {
        $icons = [
            'home'      => '<path d="M3 11 12 3l9 8"/><path d="M5 9v11h5v-7h4v7h5V9"/>',
            'columns'   => '<path d="M4 4h6v16H4zM14 4h6v16h-6z"/>',
            'target'    => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1.5" fill="currentColor"/>',
            'wallet'    => '<path d="M3 7a2 2 0 0 1 2-2h13a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z"/><path d="M16 13h2"/>',
            'users'     => '<circle cx="9" cy="8" r="3"/><path d="M3 20c0-3 3-5 6-5s6 2 6 5"/><circle cx="17" cy="9" r="2.5"/><path d="M21 19c0-2-2-3.5-4-3.5"/>',
            'msg'       => '<path d="M21 12a8 8 0 0 1-11.3 7.3L4 21l1.7-5.7A8 8 0 1 1 21 12Z"/>',
            'mail'      => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
            'calendar'  => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 9h18M8 3v4M16 3v4"/>',
            'book'      => '<path d="M4 4v15a2 2 0 0 0 2 2h13V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2Z"/><path d="M19 21H6a2 2 0 0 1 0-4h13"/>',
            'folder'    => '<path d="M3 6a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z"/>',
            'activity'  => '<path d="M3 12h4l3-8 4 16 3-8h4"/>',
            'settings'  => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1A1.7 1.7 0 0 0 4.6 9a1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1Z"/>',
            'search'    => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
            'plus'      => '<path d="M12 5v14M5 12h14"/>',
            'check'     => '<path d="m5 13 4 4 10-10"/>',
            'close'     => '<path d="M6 6l12 12M18 6 6 18"/>',
            'chevDown'  => '<path d="m6 9 6 6 6-6"/>',
            'chevRight' => '<path d="m9 6 6 6-6 6"/>',
            'chevLeft'  => '<path d="m15 6-6 6 6 6"/>',
            'chevUp'    => '<path d="m6 15 6-6 6 6"/>',
            'arrowUp'   => '<path d="M12 19V5M5 12l7-7 7 7"/>',
            'arrowDown' => '<path d="M12 5v14M19 12l-7 7-7-7"/>',
            'moreH'     => '<circle cx="5" cy="12" r="1.4" fill="currentColor"/><circle cx="12" cy="12" r="1.4" fill="currentColor"/><circle cx="19" cy="12" r="1.4" fill="currentColor"/>',
            'moreV'     => '<circle cx="12" cy="5" r="1.4" fill="currentColor"/><circle cx="12" cy="12" r="1.4" fill="currentColor"/><circle cx="12" cy="19" r="1.4" fill="currentColor"/>',
            'filter'    => '<path d="M3 5h18M6 12h12M10 19h4"/>',
            'sort'      => '<path d="M3 6h18M6 12h12M10 18h4"/>',
            'sun'       => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M2 12h2M20 12h2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
            'moon'      => '<path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8Z"/>',
            'bell'      => '<path d="M6 9a6 6 0 1 1 12 0c0 7 3 7 3 9H3c0-2 3-2 3-9ZM10 21a2 2 0 0 0 4 0"/>',
            'inbox'     => '<path d="M3 13h4l2 3h6l2-3h4M3 13l3-7h12l3 7M3 13v6a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-6"/>',
            'ai'        => '<path d="M5 3v4M3 5h4M19 17v4M17 19h4"/><path d="M12 4 9 9l-5 3 5 3 3 5 3-5 5-3-5-3z"/>',
            'eye'       => '<path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>',
            'star'      => '<path d="m12 3 2.7 6.3 6.8.6-5.1 4.6 1.5 6.6L12 17.8 6.1 21.1l1.5-6.6L2.5 9.9l6.8-.6Z"/>',
            'archive'   => '<rect x="3" y="4" width="18" height="4" rx="1"/><path d="M5 8v11a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8"/><path d="M10 12h4"/>',
            'user'      => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-7 8-7s8 3 8 7"/>',
            'shield'    => '<path d="M12 3 4 6v6c0 5 4 8 8 9 4-1 8-4 8-9V6Z"/>',
            'key'       => '<circle cx="8" cy="15" r="4"/><path d="m10.5 12 9-9M16 6l3 3M14 8l3 3"/>',
            'paperclip' => '<path d="m21 12-8.5 8.5a5 5 0 0 1-7-7l9-9a3.5 3.5 0 0 1 5 5l-9 9a2 2 0 0 1-3-3l8-8"/>',
            'branch'    => '<circle cx="6" cy="5" r="2"/><circle cx="6" cy="19" r="2"/><circle cx="18" cy="9" r="2"/><path d="M6 7v8a4 4 0 0 0 4 4h4M6 11h8a4 4 0 0 0 4-4"/>',
            'list'      => '<path d="M3 6h18M3 12h18M3 18h18"/>',
            'timeline'  => '<path d="M5 8h8M5 12h12M5 16h6"/>',
            'cmd'       => '<path d="M9 6V4.5A2.5 2.5 0 1 1 11.5 7H9m0 0V9m0-3h6m0 0V4.5A2.5 2.5 0 1 0 12.5 7H15m0 0V9m0 0h-6m6 0v6m-6-6v6m0 0v1.5A2.5 2.5 0 1 1 6.5 14H9m0 0h6m0 0v1.5A2.5 2.5 0 1 0 17.5 14H15"/>',
            'arrow'     => '<path d="M5 12h14M13 5l7 7-7 7"/>',
        ];
    }

    $path = $icons[$name] ?? '<circle cx="12" cy="12" r="9"/>';
@endphp

<svg {{ $attributes->merge(['class' => 'icon']) }}
     width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24"
     fill="none" stroke="currentColor" stroke-width="{{ $stroke }}"
     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    {!! $path !!}
</svg>
