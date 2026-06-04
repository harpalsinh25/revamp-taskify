@props([
    'names',       // array of strings
    'max' => 3,
    'size' => 20,
])

@php
    $shown = array_slice($names, 0, $max);
    $rest  = count($names) - $max;
@endphp

<span class="av-stack">
    @foreach($shown as $n)
        <x-shared.avatar :name="$n" :size="$size"/>
    @endforeach
    @if($rest > 0)
        <span class="av" style="width:{{$size}}px;height:{{$size}}px;background:var(--bg-3);color:var(--fg-1);font-size:{{round($size*0.4)}}px;border:1.5px solid var(--bg-0);">+{{ $rest }}</span>
    @endif
</span>
