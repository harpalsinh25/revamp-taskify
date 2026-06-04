@props([
    'name' => 'avatar',
    'currentName' => 'User',
    'currentSrc' => null,
])

<div class="avatar-upload">
    <x-shared.avatar :name="$currentName" :src="$currentSrc" :size="56"/>
    <div style="display:flex;flex-direction:column;gap:6px;">
        <x-buttons.button variant="secondary" size="sm" type="button">
            <input type="file" name="{{ $name }}" accept="image/*" class="sr-only" onchange="this.form.requestSubmit && this.form.requestSubmit()"/>
            Change photo
        </x-buttons.button>
        <span class="hint">JPG/PNG, max 2MB</span>
    </div>
</div>
