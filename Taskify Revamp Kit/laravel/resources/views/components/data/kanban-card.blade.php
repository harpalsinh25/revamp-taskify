@props(['task'])

@php
    $pcolor = match($task['priority'] ?? '') {
        'P1' => 'var(--err)',
        'P2' => 'var(--warn)',
        default => 'var(--fg-3)',
    };
@endphp

<article class="tcard" draggable="true" data-task="{{ $task['id'] ?? '' }}">
    <div class="tcard-meta">
        <span class="mono tcard-code">{{ $task['code'] ?? '' }}</span>
        <span class="mono tcard-priority" style="color: {{ $pcolor }}">● {{ $task['priority'] ?? '' }}</span>
    </div>
    <h4 class="tcard-title">{{ $task['title'] }}</h4>
    @if(!empty($task['tags']))
        <div class="tcard-tags">
            @foreach($task['tags'] as $tag) <x-badges.tag>{{ $tag }}</x-badges.tag> @endforeach
        </div>
    @endif
    <div class="tcard-foot">
        <x-shared.avatar-stack :names="$task['assignees'] ?? []" :max="3" :size="18"/>
        <div class="tcard-stats mono">
            @if(!empty($task['branch'])) <span title="{{ $task['branch'] }}"><x-shared.icon name="branch" size="10"/></span> @endif
            @if(!empty($task['subtasks']))
                <span><x-shared.icon name="check" size="10"/>{{ $task['subtasks'][0] }}/{{ $task['subtasks'][1] }}</span>
            @endif
            @if(!empty($task['comments']))
                <span><x-shared.icon name="msg" size="10"/>{{ $task['comments'] }}</span>
            @endif
            @if(!empty($task['due']))
                <span>{{ $task['due'] }}</span>
            @endif
        </div>
    </div>
</article>
