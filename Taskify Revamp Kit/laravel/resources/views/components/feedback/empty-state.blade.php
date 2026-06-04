@props([
    'icon' => 'inbox',
    'title' => 'Nothing here yet',
    'description' => null,
])

<div class="empty">
    <div class="empty-icon"><x-shared.icon :name="$icon" size="22"/></div>
    <div class="empty-title">{{ $title }}</div>
    @if($description)<div class="empty-sub">{{ $description }}</div>@endif
    @isset($actions)
        <div class="empty-actions">{{ $actions }}</div>
    @endisset
</div>
