@props([
    'id',
    'title' => 'Are you sure?',
    'description' => null,
    'confirmLabel' => 'Confirm',
    'cancelLabel'  => 'Cancel',
    'variant' => 'danger',
])

<x-overlays.modal :id="$id" size="sm">
    <x-slot:title>{{ $title }}</x-slot:title>
    @if($description)
        <p>{{ $description }}</p>
    @endif
    <x-slot:footer>
        <x-buttons.button variant="ghost" data-dismiss="modal">{{ $cancelLabel }}</x-buttons.button>
        <x-buttons.button :variant="$variant" data-confirm>{{ $confirmLabel }}</x-buttons.button>
    </x-slot:footer>
</x-overlays.modal>
