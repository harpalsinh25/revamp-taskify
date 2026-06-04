@props(['size' => 'md'])
@php $cls = ['spinner', 'spinner-sm' => $size === 'sm', 'spinner-lg' => $size === 'lg']; @endphp
<span {{ $attributes->merge(['class' => \Illuminate\Support\Arr::toCssClasses($cls), 'role' => 'status', 'aria-label' => 'Loading']) }}></span>
