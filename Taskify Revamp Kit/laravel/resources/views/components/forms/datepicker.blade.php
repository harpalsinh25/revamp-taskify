@props([
    'name' => null,
    'value' => null,
    'placeholder' => 'Select date',
    'min' => null,
    'max' => null,
])
{{-- Lightweight wrapper around native <input type="date"> — swap with Flatpickr/Pikaday in JS module for fancier UI. --}}
<x-forms.input :name="$name" type="date" :value="$value" :placeholder="$placeholder" icon="calendar"
    :min="$min" :max="$max" {{ $attributes }}/>
