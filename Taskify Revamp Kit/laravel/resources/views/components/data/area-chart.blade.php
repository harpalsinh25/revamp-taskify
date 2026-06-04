@props([
    'data'   => [],     // [['label'=>'W1','v'=>42], …]
    'height' => 220,
    'color'  => 'var(--fg-0)',
    'accent' => 'var(--signal)',
    'label'  => 'Tasks',   // tooltip series name
    'format' => null,      // optional callable: fn($value) => string
])

@php
    $w = 700; $padX = 36; $padY = 24;
    $max = max(array_map(fn($d) => $d['v'], $data)) * 1.15;
    $stepX = ($w - $padX * 2) / max(count($data) - 1, 1);
    $pts = [];
    foreach ($data as $i => $d) {
        $pts[] = [
            round($padX + $i * $stepX, 1),
            round($height - $padY - ($d['v'] / $max) * ($height - $padY * 2), 1),
            $d,
        ];
    }
    $line = '';
    foreach ($pts as $i => $p) $line .= ($i ? ' L ' : 'M ') . $p[0] . ' ' . $p[1];
    $area = $line . ' L ' . ($w - $padX) . ' ' . ($height - $padY) . ' L ' . $padX . ' ' . ($height - $padY) . ' Z';
    $gid = 'ac-' . uniqid();
@endphp

<svg width="100%" height="{{ $height }}" viewBox="0 0 {{ $w }} {{ $height }}"
     preserveAspectRatio="none" style="display:block;"
     data-chart-tooltip="true">
    <defs>
        <linearGradient id="{{ $gid }}" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="{{ $accent }}" stop-opacity="0.30"/>
            <stop offset="100%" stop-color="{{ $accent }}" stop-opacity="0"/>
        </linearGradient>
    </defs>
    <g class="chart-grid">
        @foreach([0, 0.25, 0.5, 0.75, 1] as $t)
            <line x1="{{ $padX }}" x2="{{ $w - $padX }}"
                  y1="{{ $padY + $t * ($height - $padY * 2) }}"
                  y2="{{ $padY + $t * ($height - $padY * 2) }}"/>
        @endforeach
    </g>
    <g class="chart-axis">
        @foreach([0, 0.25, 0.5, 0.75, 1] as $t)
            <text x="{{ $padX - 6 }}" y="{{ $padY + $t * ($height - $padY * 2) + 3 }}" text-anchor="end">
                {{ round($max * (1 - $t)) }}
            </text>
        @endforeach
        @foreach($data as $i => $d)
            @if($i % max(1, intdiv(count($data), 8)) === 0)
                <text x="{{ $padX + $i * $stepX }}" y="{{ $height - 6 }}" text-anchor="middle">{{ $d['label'] }}</text>
            @endif
        @endforeach
    </g>
    <path d="{{ $area }}" fill="url(#{{ $gid }})"/>
    <path d="{{ $line }}" fill="none" stroke="{{ $color }}" stroke-width="1.6"/>

    {{-- Visible data points --}}
    @foreach($pts as $p)
        @php
            $display = $format ? $format($p[2]['v']) : $p[2]['v'];
            $tt = json_encode(['label' => $p[2]['label'], 'value' => $display, 'rows' => [
                ['label' => $label, 'value' => $display, 'color' => $accent],
            ]]);
        @endphp
        <circle cx="{{ $p[0] }}" cy="{{ $p[1] }}" r="2.5"
                fill="var(--bg-0)" stroke="{{ $color }}" stroke-width="1.5"
                class="chart-point chart-hit"
                data-tt='{{ $tt }}'/>
    @endforeach

    {{-- Invisible larger hit circles so hover targets are usable --}}
    @foreach($pts as $p)
        @php
            $display = $format ? $format($p[2]['v']) : $p[2]['v'];
            $tt = json_encode(['rows' => [
                ['label' => $p[2]['label'], 'value' => $display, 'color' => $accent],
            ]]);
        @endphp
        <circle cx="{{ $p[0] }}" cy="{{ $p[1] }}" r="14"
                fill="transparent" class="chart-hit"
                data-tt='{{ $tt }}'/>
    @endforeach
</svg>
