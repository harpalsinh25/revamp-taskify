@props([
    'title' => null,
    'subtitle' => 'Workspace',
])

<header class="cbar">
    <div class="cbar-crumb">
        <span class="mono cbar-ws">{{ mb_strtoupper(mb_substr(config('app.name', 'TK'), 0, 2)) }}</span>
        <span class="cbar-sep cbar-crumb-mid">/</span>
        <span class="cbar-crumb-mid" style="color: var(--fg-2);">{{ $subtitle }}</span>
        <span class="cbar-sep cbar-crumb-mid">/</span>
        <span class="cbar-crumb-title">{{ $title }}</span>
    </div>

    <button class="cbar-search" type="button" data-toggle="palette" title="Search · jump · run">
        <x-shared.icon name="search" size="13"/>
        <span class="cbar-search-text">Search · jump · run</span>
        <x-badges.kbd class="cbar-search-kbd">⌘K</x-badges.kbd>
    </button>

    <div class="cbar-actions">
        <x-buttons.icon-button icon="moon" tooltip="Toggle theme" data-toggle="theme"/>
        <x-buttons.icon-button icon="inbox" tooltip="Inbox"/>
        <x-buttons.icon-button icon="bell" tooltip="Notifications">
            <span class="dot-indicator"></span>
        </x-buttons.icon-button>
        <span class="cbar-divider"></span>
        {{ $actions ?? '' }}
    </div>
</header>
