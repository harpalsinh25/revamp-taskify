<x-layouts.auth title="Sign in · Taskify">
    <header>
        <h1 style="font-size: 22px; margin: 0;">Welcome back.</h1>
        <p style="color: var(--fg-2); margin-top: 6px;">Sign in to continue to your workspace.</p>
    </header>

    <form method="POST" action="{{ route('login') }}" style="display:flex;flex-direction:column;gap:14px;">
        @csrf
        <x-forms.field label="Email" name="email">
            <x-forms.input type="email" name="email" placeholder="you@company.com" icon="mail" required/>
        </x-forms.field>

        <x-forms.field label="Password" name="password">
            <x-forms.input type="password" name="password" placeholder="••••••••" icon="key" required/>
        </x-forms.field>

        <div style="display:flex; align-items:center; justify-content:space-between;">
            <x-forms.checkbox name="remember" label="Remember me"/>
            <a href="{{ route('password.request') }}" class="txt-sm txt-mute">Forgot password?</a>
        </div>

        <x-buttons.button variant="primary" type="submit" block>Sign in</x-buttons.button>
    </form>

    <x-slot:footer>
        Don't have an account?
        <a href="{{ route('register') }}" style="color: var(--fg-0); font-weight: 500;">Create one</a>
    </x-slot:footer>

    <x-slot:aside>
        <blockquote style="font-size: 18px; max-width: 360px; line-height: 1.45;">
            "We replaced four tools with Taskify and shipped 30% more in the same quarter."
            <footer style="margin-top: 14px; font-size: 13px; color: var(--fg-2);">— Priya Mehta, Head of Ops</footer>
        </blockquote>
    </x-slot:aside>
</x-layouts.auth>
