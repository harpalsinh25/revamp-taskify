<!DOCTYPE html>
<html lang="en" data-theme="{{ session('theme', 'light') }}">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>{{ $title ?? 'Taskify' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body>
    <div class="app">
        {{-- Left icon rail --}}
        <x-navigation.sidebar :active="$active ?? null" />

        {{-- Context panel (per-module submenu) — hidden on routes that opt out --}}
        @unless($noPanel ?? false)
            <x-navigation.context-panel :active="$active ?? null" />
        @endunless

        <div class="main">
            {{-- Top command bar --}}
            <x-navigation.header
                :title="$pageTitle ?? null"
                :subtitle="$pageSubtitle ?? null">
                <x-slot:actions>
                    {{ $headerActions ?? '' }}
                </x-slot:actions>
            </x-navigation.header>

            {{-- Page content --}}
            <main class="content fade-in">
                {{ $slot }}
            </main>
        </div>
    </div>

    {{-- Global command palette --}}
    <x-overlays.command-palette />

    {{-- Toast host gets created lazily by JS --}}
    @stack('overlays')
    @stack('scripts')
</body>
</html>
