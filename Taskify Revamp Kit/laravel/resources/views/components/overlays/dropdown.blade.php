@props([
    'align' => 'left',   // left | right
])

<div class="dropdown" data-dropdown data-open="false" {{ $attributes }}>
    <div data-dropdown-trigger>
        {{ $trigger }}
    </div>
    <div class="dropdown-menu" data-align="{{ $align }}" role="menu">
        {{ $slot }}
    </div>
</div>
