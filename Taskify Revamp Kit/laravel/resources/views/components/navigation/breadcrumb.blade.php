@props([
    'items' => [],  // [['label' => 'Projects', 'href' => '/projects'], ['label' => 'Brand Refresh']]
])

<nav class="breadcrumb" aria-label="Breadcrumb">
    @foreach($items as $i => $crumb)
        @if(!empty($crumb['href']) && $i < count($items) - 1)
            <a href="{{ $crumb['href'] }}">{{ $crumb['label'] }}</a>
            <span class="breadcrumb-sep">/</span>
        @else
            <span class="breadcrumb-current">{{ $crumb['label'] }}</span>
        @endif
    @endforeach
</nav>
