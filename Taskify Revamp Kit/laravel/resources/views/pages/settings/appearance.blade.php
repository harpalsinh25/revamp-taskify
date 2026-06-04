<x-layouts.app
    active="settings"
    pageTitle="Appearance"
    pageSubtitle="Settings">

    <div style="max-width: 720px;">
        <x-navigation.breadcrumb :items="[
            ['label' => 'Settings', 'href' => route('settings.index')],
            ['label' => 'Appearance'],
        ]"/>

        <h1 style="margin-top: 8px;">Appearance</h1>
        <p class="txt-mute" style="margin-top: 6px; max-width: 540px;">
            Personalize how Taskify looks for you. Changes apply instantly and
            sync across your devices.
        </p>

        <form style="margin-top: 24px; display: flex; flex-direction: column; gap: 18px;"
              onsubmit="event.preventDefault()">

            {{-- ============================= THEME ============================= --}}
            <x-cards.card title="Theme" eyebrow="MODE">
                <x-slot:header>
                    <button type="button" class="btn btn-ghost btn-sm" data-appearance-reset>Reset</button>
                </x-slot:header>

                <div class="theme-grid">
                    @foreach([
                        ['key' => 'light',  'label' => 'Light',  'preview' => '#fff'],
                        ['key' => 'dark',   'label' => 'Dark',   'preview' => '#1a1d2e'],
                        ['key' => 'system', 'label' => 'System', 'preview' => 'linear-gradient(135deg, #fff 50%, #1a1d2e 50%)'],
                    ] as $opt)
                        <button type="button"
                                class="theme-card"
                                data-set-theme="{{ $opt['key'] }}"
                                aria-label="{{ $opt['label'] }} theme">
                            <span class="theme-card-preview" style="background: {{ $opt['preview'] }};">
                                <span class="theme-card-mock">
                                    <span class="theme-card-bar"></span>
                                    <span class="theme-card-bar short"></span>
                                </span>
                            </span>
                            <span class="theme-card-name">{{ $opt['label'] }}</span>
                        </button>
                    @endforeach
                </div>
            </x-cards.card>

            {{-- ============================= ACCENT HUE ============================= --}}
            <x-cards.card title="Accent color" eyebrow="SIGNAL">
                <p class="txt-mute" style="font-size: 12.5px; margin: 0 0 14px;">
                    Choose the single accent color used for active states, primary buttons, and focus rings.
                </p>

                <div class="hue-swatches">
                    @foreach([
                        ['n' => 'Lime',    'h' => 130],
                        ['n' => 'Mint',    'h' => 165],
                        ['n' => 'Cyan',    'h' => 200],
                        ['n' => 'Blue',    'h' => 235],
                        ['n' => 'Violet',  'h' => 285],
                        ['n' => 'Magenta', 'h' => 330],
                        ['n' => 'Amber',   'h' => 75],
                    ] as $swatch)
                        <button type="button"
                                class="hue-swatch"
                                data-hue="{{ $swatch['h'] }}"
                                title="{{ $swatch['n'] }}"
                                style="background: oklch(0.78 0.20 {{ $swatch['h'] }});"></button>
                    @endforeach
                </div>

                <label class="field" style="margin-top: 18px;">
                    <span class="label">Custom hue</span>
                    <input type="range" min="0" max="360" step="5" value="130"
                           data-appearance="signalHue"
                           class="hue-range"/>
                    <span class="hint mono">Drag to pick any hue from the OKLCH wheel.</span>
                </label>
            </x-cards.card>

            {{-- ============================= ACTIVE MENU STYLE ============================= --}}
            <x-cards.card title="Active menu style" eyebrow="SIDEBAR">
                <p class="txt-mute" style="font-size: 12.5px; margin: 0 0 14px;">
                    How the active item is highlighted on the left rail.
                </p>

                <div class="active-style-grid">
                    @foreach([
                        ['key' => 'notch',     'label' => 'Notch',     'desc' => 'Magnetic tile + cursor'],
                        ['key' => 'aurora',    'label' => 'Aurora',    'desc' => 'Diagonal gradient'],
                        ['key' => 'bracket',   'label' => 'Bracket',   'desc' => 'Corner brackets + dot'],
                        ['key' => 'underglow', 'label' => 'Underglow', 'desc' => 'Soft floor halo'],
                        ['key' => 'inkwell',   'label' => 'Inkwell',   'desc' => 'Solid graphite chip'],
                        ['key' => 'spine',     'label' => 'Spine',     'desc' => 'Full-height left bar'],
                        ['key' => 'orbit',     'label' => 'Orbit',     'desc' => 'Rotating dashed ring'],
                        ['key' => 'crt',       'label' => 'CRT',       'desc' => 'Scanline tile'],
                        ['key' => 'diamond',   'label' => 'Diamond',   'desc' => 'Rotated chip'],
                        ['key' => 'stamp',     'label' => 'Stamp',     'desc' => 'Corner-cut card'],
                        ['key' => 'halo',      'label' => 'Halo',      'desc' => 'Soft outer ring'],
                        ['key' => 'duotone',   'label' => 'Duotone',   'desc' => 'Split fill'],
                        ['key' => 'soft',      'label' => 'Soft',      'desc' => 'Minimal tinted tile'],
                        ['key' => 'beacon',    'label' => 'Beacon',    'desc' => 'Radial spotlight'],
                        ['key' => 'frosted',   'label' => 'Frosted',   'desc' => 'Translucent glass'],
                        ['key' => 'marquee',   'label' => 'Marquee',   'desc' => 'Marching border'],
                        ['key' => 'slate',     'label' => 'Slate',     'desc' => 'Neutral underline'],
                        ['key' => 'pulse',     'label' => 'Pulse',     'desc' => 'Pulsing rings'],
                        ['key' => 'reticle',   'label' => 'Reticle',   'desc' => 'Corner ticks'],
                    ] as $style)
                        <button type="button"
                                class="active-style-card"
                                data-active-style-card="{{ $style['key'] }}">
                            <span class="active-style-preview">
                                <span class="active-style-mock" data-mock-style="{{ $style['key'] }}"></span>
                            </span>
                            <span class="active-style-info">
                                <span class="active-style-name">{{ $style['label'] }}</span>
                                <span class="active-style-desc">{{ $style['desc'] }}</span>
                            </span>
                        </button>
                    @endforeach
                </div>
            </x-cards.card>

            {{-- ============================= DENSITY ============================= --}}
            <x-cards.card title="Density" eyebrow="SPACING">
                <p class="txt-mute" style="font-size: 12.5px; margin: 0 0 14px;">
                    Tune the breathing room across rows, cards, and lists.
                </p>

                <div style="display: flex; gap: 8px;">
                    @foreach([
                        ['key' => 'compact',     'label' => 'Compact'],
                        ['key' => 'cozy',        'label' => 'Cozy'],
                        ['key' => 'comfortable', 'label' => 'Comfortable'],
                    ] as $d)
                        <button type="button"
                                class="btn btn-secondary"
                                data-appearance="density"
                                data-value="{{ $d['key'] }}"
                                onclick="this.dataset.value && (this.closest('form').dispatchEvent(new Event('input',{bubbles:true})))">
                            {{ $d['label'] }}
                        </button>
                    @endforeach
                </div>
            </x-cards.card>

        </form>
    </div>

    {{-- Page-local styles for the cards. Tokens are reused so it stays theme-aware. --}}
    @push('head')
    <style>
        /* Theme cards */
        .theme-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }
        .theme-card {
            display: flex; flex-direction: column; gap: 8px;
            padding: 8px;
            border: 1px solid var(--line);
            border-radius: 6px;
            background: var(--bg-1);
            cursor: pointer;
            transition: border-color var(--t-1), box-shadow var(--t-1);
        }
        .theme-card:hover  { border-color: var(--line-2); }
        .theme-card-preview {
            height: 84px; border-radius: 4px; position: relative; overflow: hidden;
            display: block;
            border: 1px solid var(--line);
        }
        .theme-card-mock {
            position: absolute; inset: 10px;
            display: flex; flex-direction: column; gap: 5px;
        }
        .theme-card-bar {
            height: 6px; width: 65%;
            border-radius: 3px; background: oklch(0.5 0.05 280 / 0.4);
        }
        .theme-card-bar.short { width: 35%; }
        .theme-card-name {
            font-size: 12.5px; color: var(--fg-0); font-weight: 500;
            text-align: center;
        }

        /* Hue swatches */
        .hue-swatches {
            display: flex; gap: 8px; flex-wrap: wrap;
        }
        .hue-swatch {
            width: 32px; height: 32px;
            border-radius: 50%;
            border: 2px solid var(--bg-0);
            box-shadow: inset 0 0 0 1px var(--line-2);
            cursor: pointer;
            transition: transform var(--t-1);
        }
        .hue-swatch:hover { transform: scale(1.08); }
        .hue-range {
            width: 100%; height: 22px;
            -webkit-appearance: none; appearance: none;
            background: linear-gradient(to right,
                oklch(0.78 0.20 0), oklch(0.78 0.20 60), oklch(0.78 0.20 120),
                oklch(0.78 0.20 180), oklch(0.78 0.20 240), oklch(0.78 0.20 300),
                oklch(0.78 0.20 360));
            border-radius: 99px;
            outline: none;
        }
        .hue-range::-webkit-slider-thumb {
            -webkit-appearance: none; width: 18px; height: 18px;
            background: var(--bg-0); border: 3px solid var(--fg-0);
            border-radius: 50%; cursor: pointer;
        }

        /* Active-style cards */
        .active-style-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 10px;
        }
        .active-style-card {
            display: flex; align-items: center; gap: 10px;
            padding: 10px;
            background: var(--bg-1);
            border: 1px solid var(--line);
            border-radius: 6px;
            text-align: left;
            cursor: pointer;
            transition: border-color var(--t-1), background var(--t-1);
        }
        .active-style-card:hover { border-color: var(--line-2); background: var(--bg-2); }
        [data-active-style] .active-style-card[data-active-style-card="notch"],
        [data-active-style="notch"] .active-style-card[data-active-style-card="notch"] {
            border-color: var(--signal);
        }
        .active-style-preview {
            position: relative;
            width: 40px; height: 40px;
            background: var(--bg-2);
            border-radius: 5px;
            flex-shrink: 0;
            display: grid; place-items: center;
        }
        .active-style-mock {
            position: relative;
            width: 28px; height: 28px;
            border-radius: 6px;
            background: var(--bg-3);
        }
        /* Each mock paints a tiny preview of its variant */
        [data-mock-style="notch"]   { background: linear-gradient(180deg, var(--bg-3), var(--bg-2)); box-shadow: inset 0 0 0 1px var(--line-2); }
        [data-mock-style="notch"]::after { content:''; position:absolute; right:-4px; top:50%; transform:translateY(-50%); width:2px; height:14px; background: var(--signal); border-radius: 1px; box-shadow: 0 0 6px var(--signal-glow); }
        [data-mock-style="aurora"]  { background: linear-gradient(135deg, var(--signal), oklch(from var(--signal) calc(l - 0.18) c h)); }
        [data-mock-style="bracket"] { background: transparent; box-shadow: inset 2px 2px 0 var(--signal), inset -2px -2px 0 var(--signal); border-radius: 0; width: 18px; height: 18px; }
        [data-mock-style="underglow"] { background: var(--bg-3); box-shadow: 0 8px 12px -4px var(--signal-glow); }
        [data-mock-style="inkwell"] { background: var(--fg-0); }
        [data-mock-style="spine"]   { background: var(--bg-3); border-left: 3px solid var(--signal); border-radius: 0 6px 6px 0; }
        [data-mock-style="orbit"]   { background: transparent; border: 1.5px dashed var(--signal); }
        [data-mock-style="crt"]     { background: var(--bg-3); position: relative; overflow: hidden; }
        [data-mock-style="crt"]::after { content:''; position:absolute; left:0; right:0; top:50%; height:2px; background: var(--signal); opacity: 0.6; }
        [data-mock-style="diamond"] { background: var(--signal); transform: rotate(45deg); width: 20px; height: 20px; border-radius: 3px; }
        [data-mock-style="stamp"]   { background: var(--signal); clip-path: polygon(6px 0, 100% 0, 100% calc(100% - 6px), calc(100% - 6px) 100%, 0 100%, 0 6px); }
        [data-mock-style="halo"]    { background: transparent; box-shadow: 0 0 0 1.5px var(--signal), 0 0 12px var(--signal-glow); }
        [data-mock-style="duotone"] { background: linear-gradient(to bottom, var(--signal) 50%, var(--bg-3) 50%); }
        [data-mock-style="soft"]    { background: var(--signal-soft); box-shadow: inset 0 0 0 1px oklch(from var(--signal) l c h / 0.3); }
        [data-mock-style="beacon"]  { background: radial-gradient(circle at center, oklch(from var(--signal) l c h / 0.5), transparent 70%); }
        [data-mock-style="frosted"] { background: oklch(from var(--signal) l c h / 0.25); backdrop-filter: blur(4px); box-shadow: inset 0 1px 0 oklch(1 0 0 / 0.15); }
        [data-mock-style="marquee"] { background: transparent; border: 1.5px dashed var(--signal); }
        [data-mock-style="slate"]   { background: var(--bg-3); position: relative; }
        [data-mock-style="slate"]::after { content:''; position:absolute; left:6px; right:6px; bottom: 4px; height: 2px; background: var(--signal); border-radius: 1px; }
        [data-mock-style="pulse"]   { background: oklch(from var(--signal) l c h / 0.25); border-radius: 50%; box-shadow: 0 0 0 1.5px var(--signal); }
        [data-mock-style="reticle"] {
            background: transparent;
            background:
                linear-gradient(to right, var(--signal) 5px, transparent 5px) top left/7px 1.5px no-repeat,
                linear-gradient(to bottom, var(--signal) 5px, transparent 5px) top left/1.5px 7px no-repeat,
                linear-gradient(to left, var(--signal) 5px, transparent 5px) top right/7px 1.5px no-repeat,
                linear-gradient(to bottom, var(--signal) 5px, transparent 5px) top right/1.5px 7px no-repeat,
                linear-gradient(to right, var(--signal) 5px, transparent 5px) bottom left/7px 1.5px no-repeat,
                linear-gradient(to top, var(--signal) 5px, transparent 5px) bottom left/1.5px 7px no-repeat,
                linear-gradient(to left, var(--signal) 5px, transparent 5px) bottom right/7px 1.5px no-repeat,
                linear-gradient(to top, var(--signal) 5px, transparent 5px) bottom right/1.5px 7px no-repeat;
        }
        .active-style-info { display: flex; flex-direction: column; gap: 2px; }
        .active-style-name { font-size: 12.5px; color: var(--fg-0); font-weight: 500; }
        .active-style-desc { font-size: 11px; color: var(--fg-3); }
    </style>
    @endpush
</x-layouts.app>
