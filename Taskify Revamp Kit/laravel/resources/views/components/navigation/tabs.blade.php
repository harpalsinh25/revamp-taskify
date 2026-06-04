@props([
    'items' => [],   // [['key'=>'overview','label'=>'Overview','href'=>'…'], …]
    'active' => null,
])

<div class="tabs" role="tablist">
    @foreach($items as $tab)
        <a href="{{ $tab['href'] ?? '#' }}"
           class="tab"
           role="tab"
           data-active="{{ $active === $tab['key'] ? 'true' : 'false' }}">
            {{ $tab['label'] }}
        </a>
    @endforeach
</div>
