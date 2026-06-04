@props(['active' => null])

@php
    $rail = config('navigation.rail', []);
@endphp

<aside class="rail" aria-label="Primary">
    <a href="{{ route('dashboard') }}" class="rail-brand" title="Taskify">T</a>

    @foreach($rail as $item)
        @if($item === '_divider')
            <span class="rail-divider"></span>
        @else
            <a href="{{ \Illuminate\Support\Facades\Route::has($item['route']) ? route($item['route']) : '#' }}"
               class="rail-btn"
               data-active="{{ $active === $item['id'] ? 'true' : 'false' }}"
               title="{{ $item['label'] }}"
               aria-label="{{ $item['label'] }}">
                <x-shared.icon :name="$item['icon']" size="17"/>
                @if(!empty($item['badge']))
                    <span class="rail-badge">{{ $item['badge'] }}</span>
                @endif
            </a>
        @endif
    @endforeach

    <div class="rail-foot">
        <button type="button" class="rail-btn" title="Settings"><x-shared.icon name="settings" size="17"/></button>
        <div class="rail-avatar">{{ mb_substr(auth()->user()?->name ?? 'AK', 0, 2) }}</div>
    </div>
</aside>
