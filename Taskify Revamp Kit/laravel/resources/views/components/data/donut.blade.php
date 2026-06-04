@props([
    'data'      => [],     // [['value'=>28,'color'=>'…','label'=>'In progress'], …]
    'size'      => 132,
    'thickness' => 14,
    'center'    => null,   // ['value'=>'86','label'=>'TASKS']
])

@php
    $total = array_sum(array_map(fn($d) => $d['value'], $data));
    $r = $size / 2 - $thickness / 2;
    $cx = $cy = $size / 2;
    $dash = 2 * M_PI * $r;
    $acc = 0;
    $segments = [];
    foreach ($data as $d) {
        $frac = $total ? $d['value'] / $total : 0;
        $len  = $frac * $dash;
        $offset = -$acc * $dash;
        $pct = $total ? round($d['value'] / $total * 100) : 0;
        $segments[] = [
            'color'  => $d['color'],
            'label'  => $d['label'] ?? '',
            'value'  => $d['value'],
            'pct'    => $pct,
            'len'    => $len,
            'offset' => $offset,
        ];
        $acc += $frac;
    }
@endphp

<svg width="{{ $size }}" height="{{ $size }}" data-chart-tooltip="true">
    <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r }}" fill="none" stroke="var(--bg-3)" stroke-width="{{ $thickness }}"/>
    @foreach($segments as $s)
        @php
            $tt = json_encode([
                'rows' => [[
                    'label' => $s['label'],
                    'value' => $s['value'] . '  (' . $s['pct'] . '%)',
                    'color' => $s['color'],
                ]],
            ]);
        @endphp
        <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r }}"
                fill="none" stroke="{{ $s['color'] }}" stroke-width="{{ $thickness }}"
                stroke-dasharray="{{ $s['len'] - 2 }} {{ $dash }}"
                stroke-dashoffset="{{ $s['offset'] }}"
                transform="rotate(-90 {{ $cx }} {{ $cy }})"
                class="donut-segment"
                data-tt='{{ $tt }}'/>
    @endforeach
    <text x="{{ $cx }}" y="{{ $cy - 1 }}" text-anchor="middle" font-size="22" font-weight="700"
          fill="var(--fg-0)" letter-spacing="-0.03em" font-family="var(--font-sans)">
        {{ $center['value'] ?? $total }}
    </text>
    <text x="{{ $cx }}" y="{{ $cy + 14 }}" text-anchor="middle" font-size="9"
          fill="var(--fg-3)" font-family="var(--font-mono)" letter-spacing="0.06em">
        {{ $center['label'] ?? 'TOTAL' }}
    </text>
</svg>
