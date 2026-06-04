@props([
    'name',
    'accept' => null,
    'multiple' => false,
    'hint' => 'PNG, JPG up to 10MB',
])

<label class="file-drop" data-file-drop>
    <x-shared.icon name="paperclip" size="18"/>
    <div class="file-drop-text" style="margin-top:8px;">
        <strong style="color:var(--fg-0)">Click to upload</strong>
        <span> or drag & drop</span>
    </div>
    <div class="hint" style="margin-top:4px;">{{ $hint }}</div>
    <input type="file" name="{{ $name }}{{ $multiple ? '[]' : '' }}"
           @if($accept) accept="{{ $accept }}" @endif
           @if($multiple) multiple @endif
           {{ $attributes->merge(['class' => 'sr-only']) }}/>
</label>
