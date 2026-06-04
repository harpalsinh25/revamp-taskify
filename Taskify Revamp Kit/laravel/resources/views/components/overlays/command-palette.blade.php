@props([
    'commands' => null,    // optional override; defaults to navigation.rail
])

@php
    $commands = $commands ?? collect(config('navigation.rail', []))
        ->filter(fn($it) => is_array($it))
        ->map(fn($it) => [
            'label' => $it['label'],
            'icon'  => $it['icon'],
            'href'  => \Illuminate\Support\Facades\Route::has($it['route']) ? route($it['route']) : '#',
        ])
        ->all();
@endphp

<div class="palette-host" id="palette" hidden data-open="false">
    <div class="overlay-backdrop" data-dismiss="palette"></div>
    <div class="palette" role="dialog" aria-modal="true" aria-label="Command palette">
        <div class="palette-dialog">
            <header class="palette-search">
                <x-shared.icon name="search" size="14"/>
                <input type="search" placeholder="Search · jump · run" autocomplete="off"/>
                <x-badges.kbd>ESC</x-badges.kbd>
            </header>
            <div class="palette-list">
                @foreach($commands as $cmd)
                    <a class="palette-item" data-href="{{ $cmd['href'] }}">
                        <span class="palette-item-icon"><x-shared.icon :name="$cmd['icon']" size="14"/></span>
                        <span>{{ $cmd['label'] }}</span>
                        @if(!empty($cmd['shortcut']))
                            <x-badges.kbd class="palette-item-shortcut">{{ $cmd['shortcut'] }}</x-badges.kbd>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('click', (e) => {
        const tg = e.target.closest('[data-toggle="palette"]');
        if (tg) { e.preventDefault(); document.dispatchEvent(new KeyboardEvent('keydown', { key: 'k', metaKey: true })); }
        if (e.target.matches('[data-dismiss="palette"]')) {
            document.getElementById('palette').dataset.open = 'false';
            document.getElementById('palette').setAttribute('hidden', '');
            document.body.style.overflow = '';
        }
    });
</script>
@endpush
