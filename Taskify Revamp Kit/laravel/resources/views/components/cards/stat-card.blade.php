@props([
    'label',
    'value',
    'delta' => null,
    'trend' => 'up',   // up | down
])
<div class="metric">
    <div class="metric-row">
        <span class="metric-label mono">{{ $label }}</span>
        @if($delta)
            <span class="metric-delta {{ $trend }}">
                <x-shared.icon :name="$trend === 'up' ? 'arrowUp' : 'arrowDown'" size="10"/>
                {{ $delta }}
            </span>
        @endif
    </div>
    <div class="metric-value mono">{{ $value }}</div>
    @isset($footer)
        <div style="margin-top:6px;">{{ $footer }}</div>
    @endisset
</div>
