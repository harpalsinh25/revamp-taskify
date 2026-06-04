<x-layouts.app active="settings" pageTitle="Profile" pageSubtitle="Settings">
    <div style="max-width: 720px;">
        <x-navigation.breadcrumb :items="[
            ['label' => 'Settings', 'href' => '/settings'],
            ['label' => 'Profile'],
        ]"/>

        <h1 style="margin-top: 8px;">Profile</h1>
        <p class="txt-mute" style="margin-top: 6px;">Update your name, photo, and personal info.</p>

        @if(session('saved'))
            <div style="margin-top:16px;">
                <x-feedback.alert type="success" title="Saved" closable>Your profile has been updated.</x-feedback.alert>
            </div>
        @endif

        <form method="POST" action="{{ route('settings.profile.update') }}" style="margin-top: 24px; display: flex; flex-direction: column; gap: 18px;">
            @csrf @method('PUT')

            <x-cards.card title="Photo">
                <x-forms.avatar-upload name="avatar" :currentName="auth()->user()?->name ?? 'Alex K'"/>
            </x-cards.card>

            <x-cards.card title="Personal info">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <x-forms.field label="First name" name="first_name" required>
                        <x-forms.input name="first_name" value="{{ auth()->user()?->first_name ?? 'Alex' }}"/>
                    </x-forms.field>
                    <x-forms.field label="Last name" name="last_name" required>
                        <x-forms.input name="last_name" value="{{ auth()->user()?->last_name ?? 'Kim' }}"/>
                    </x-forms.field>
                    <x-forms.field label="Email" name="email" required style="grid-column: span 2;">
                        <x-forms.input type="email" name="email" icon="mail" value="{{ auth()->user()?->email }}"/>
                    </x-forms.field>
                    <x-forms.field label="Bio" name="bio" style="grid-column: span 2;" hint="A short intro shown on your profile.">
                        <x-forms.textarea name="bio" rows="3" placeholder="What do you do?"/>
                    </x-forms.field>
                </div>
            </x-cards.card>

            <x-cards.card title="Notifications">
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    <x-forms.switch name="notif_mentions"   :checked="true"  label="Mentions"       hint="Get notified when someone @mentions you."/>
                    <x-forms.switch name="notif_assignments" :checked="true" label="Assignments"   hint="Tasks assigned to you."/>
                    <x-forms.switch name="notif_digest"     :checked="false" label="Weekly digest" hint="Summary every Monday."/>
                </div>
            </x-cards.card>

            <div style="display: flex; justify-content: flex-end; gap: 8px;">
                <x-buttons.button variant="ghost" href="{{ url()->previous() }}">Cancel</x-buttons.button>
                <x-buttons.button variant="primary" type="submit">Save changes</x-buttons.button>
            </div>
        </form>
    </div>
</x-layouts.app>
