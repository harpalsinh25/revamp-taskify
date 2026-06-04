@props(['status' => 'progress'])

@php
    $map = config('ui.status', ['progress' => 'progress', 'review' => 'review', 'done' => 'done', 'blocked' => 'blocked']);
    $cls = 'status status-' . ($map[$status] ?? 'progress');
    $dotColor = match($status) {
        'progress' => 'var(--signal)',
        'review'   => 'var(--warn)',
        'done'     => 'var(--ok)',
        'blocked'  => 'var(--err)',
        default    => 'var(--fg-3)',
    };
@endphp

<span class="{{ $cls }}">
    <span class="dot" style="background: {{ $dotColor }}"></span>
    {{ $slot }}
</span>
