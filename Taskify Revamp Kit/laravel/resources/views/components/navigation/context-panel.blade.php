@props(['active' => null])

@php
    $sections = config('navigation.panels.' . $active, []);
    $title    = config('navigation.titles.' . $active . '.title', \Illuminate\Support\Str::title($active));
@endphp

@if($sections)
<aside class="panel" aria-label="Secondary">
    <div class="panel-head">
        <span class="panel-title">{{ $title }}</span>
        <x-buttons.icon-button icon="plus" size="sm" tooltip="New"/>
    </div>
    <div class="panel-body">
        @foreach($sections as $section)
            <div class="panel-section">
                @if(!empty($section['label']))
                    <div class="panel-label">{{ $section['label'] }}</div>
                @endif
                @foreach($section['items'] as $item)
                    <a class="panel-item"
                       data-active="{{ !empty($item['active']) ? 'true' : 'false' }}"
                       href="{{ \Illuminate\Support\Facades\Route::has($item['route']) ? route($item['route'], $item['route_params'] ?? []) : '#' }}">
                        <x-shared.icon :name="$item['icon']" size="13"/>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </div>
        @endforeach
    </div>
</aside>
@endif
