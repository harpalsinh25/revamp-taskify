@props([
    'time',
    'duration' => null,
    'name',
    'tag' => null,
    'soon' => false,
    'countdown' => null,   // "in 18m"
])

<div class="sched-row" data-soon="{{ $soon ? 'true' : 'false' }}">
    <div class="sched-time">
        <span class="mono sched-t">{{ $time }}</span>
        @if($duration) <span class="mono sched-d">{{ $duration }}</span> @endif
    </div>
    <div class="sched-content">
        <div class="sched-name">{{ $name }}</div>
        @if($tag) <x-badges.chip>{{ $tag }}</x-badges.chip> @endif
    </div>
    @if($soon && $countdown)
        <span class="sched-soon mono">{{ $countdown }}</span>
    @endif
</div>
