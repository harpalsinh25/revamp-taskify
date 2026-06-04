<!DOCTYPE html>
<html lang="en" data-theme="{{ session('theme', 'light') }}">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>{{ $title ?? 'Sign in · Taskify' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="auth-shell">
        {{-- Left side: form --}}
        <main class="auth-form">
            <a href="{{ route('home') }}" class="auth-brand">
                <span class="rail-brand">T</span>
                <span class="auth-brand-name">Taskify</span>
            </a>
            <div class="auth-card">
                {{ $slot }}
            </div>
            <p class="auth-foot txt-mute txt-sm">
                {{ $footer ?? '' }}
            </p>
        </main>

        {{-- Right side: aside (illustration / quote / metrics) --}}
        <aside class="auth-aside">
            {{ $aside ?? '' }}
        </aside>
    </div>

    @stack('scripts')

    <style>
        .auth-shell { display: grid; grid-template-columns: 1fr 1fr; min-height: 100vh; }
        .auth-form  { display: flex; flex-direction: column; justify-content: center; padding: 48px; max-width: 480px; width: 100%; margin: 0 auto; }
        .auth-brand { display: inline-flex; align-items: center; gap: 10px; margin-bottom: 36px; font-weight: 700; }
        .auth-brand-name { font-size: 16px; }
        .auth-card  { display: flex; flex-direction: column; gap: 18px; }
        .auth-foot  { margin-top: 32px; }
        .auth-aside { background: var(--bg-2); border-left: 1px solid var(--line); display: flex; align-items: center; justify-content: center; padding: 48px; }
        @media (max-width: 880px) {
            .auth-shell { grid-template-columns: 1fr; }
            .auth-aside { display: none; }
        }
    </style>
</body>
</html>
