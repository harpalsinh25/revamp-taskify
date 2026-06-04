@props([
    'data'   => [],
    'labels' => null,    // optional same-length array of labels
    'color'  => 'var(--fg-0)',
    'width'  => 120,
    'height' => 24,
    'fill'   => false,
    'label'  => 'Value',
])

@php
    $min = min($data); $max = max($data);
    $range = max($max - $min, 0.0001);
    $stepX = $width / max(count($data) - 1, 1);
    $pts = [];
    foreach ($data as $i => $v) {
        $x = round($i * $stepX, 1);
        $y = round($height - (($v - $min) / $range) * ($height - 4) - 2, 1);
        $pts[] = [$x, $y, $v, $labels[$i] ?? null];
    }
    $d = '';
    foreach ($pts as $i => $p) $d .= ($i ? ' L ' : 'M ') . $p[0] . ' ' . $p[1];
@endphp

<svg width="100%" height="{{ $height }}" viewBox="0 0 {{ $width }} {{ $height }}"
     preserveAspectRatio="none" class="metric-spark" style="display:block;"
     data-chart-tooltip="true">
    <path d="{{ $d }}" class="spark-line" stroke="{{ $color }}" fill="none"/>
    @if($fill)
        <path d="{{ $d }} L {{ $width }} {{ $height }} L 0 {{ $height }} Z" fill="{{ $color }}" opacity="0.10"/>
    @endif

    {{-- Invisible hit zones, one per data point --}}
    @foreach($pts as $i => $p)
        @php
            $ttLabel = $p[3] ?? '#' . ($i + 1);
            $tt = json_encode(['rows' => [[
                'label' => $ttLabel,
                'value' => $p[2],
                'color' => $color,
            ]]]);
            $hitW = $stepX;
        @endphp
        <rect x="{{ max(0, $p[0] - $hitW/2) }}" y="0" width="{{ $hitW }}" height="{{ $height }}"
              fill="transparent" class="chart-hit"
              data-tt='{{ $tt }}'
              vector-effect="non-scaling-stroke"/>
    @endforeach
</svg>
