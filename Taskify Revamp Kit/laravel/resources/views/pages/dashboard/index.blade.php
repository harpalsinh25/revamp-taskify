<x-layouts.app
    active="dashboard"
    pageTitle="Today"
    pageSubtitle="Workspace">

    <x-slot:headerActions>
        <x-buttons.button variant="secondary" size="sm" icon="ai">Ask AI</x-buttons.button>
        <x-buttons.button variant="primary"   size="sm" icon="plus">Create</x-buttons.button>
    </x-slot:headerActions>

    {{-- Hero banner --}}
    <section class="d-banner card" style="padding: 22px 24px; margin-bottom: 16px;">
        <div>
            <div class="eyebrow">09:14 · MON 16 MAR · WK 12</div>
            <h1 style="margin-top: 6px;">Welcome back, {{ auth()->user()?->name ?? 'Alex' }}.</h1>
            <p style="margin-top: 8px;">
                3 deadlines this week. 7 tasks ready to ship.
                <span style="color: var(--ok)">+18% velocity</span> over last week.
            </p>
            <div style="display: flex; gap: 8px; margin-top: 14px;">
                <x-buttons.button variant="primary" icon="ai">Plan my day</x-buttons.button>
                <x-buttons.button variant="ghost">Skip standup</x-buttons.button>
            </div>
        </div>
    </section>

    {{-- KPI strip --}}
    <x-data.metric-strip :metrics="[
        ['label' => 'Active projects',    'value' => '24',    'delta' => '+3',     'trend' => 'up'],
        ['label' => 'Tasks shipped · 7d', 'value' => '128',   'delta' => '+18.2%', 'trend' => 'up'],
        ['label' => 'Pipeline value',     'value' => '$252k', 'delta' => '+$48k', 'trend' => 'up'],
        ['label' => 'Avg. cycle time',    'value' => '3.2d',  'delta' => '−0.4d',  'trend' => 'up'],
    ]"/>

    {{-- Main grid --}}
    <div class="d-grid">
        {{-- Velocity --}}
        <x-cards.card span="span-2" eyebrow="Velocity · last 12 weeks">
            <x-slot:titleSlot>128 <span class="card-title-sub">tasks shipped</span></x-slot:titleSlot>
            <x-slot:header>
                <x-buttons.segmented :options="[
                    ['value' => 'day',   'label' => 'Day'],
                    ['value' => 'week',  'label' => 'Week'],
                    ['value' => 'month', 'label' => 'Month'],
                ]" value="week"/>
            </x-slot:header>

            <x-data.area-chart :data="[
                ['label'=>'W1','v'=>42],['label'=>'W2','v'=>51],['label'=>'W3','v'=>48],['label'=>'W4','v'=>62],
                ['label'=>'W5','v'=>74],['label'=>'W6','v'=>68],['label'=>'W7','v'=>81],['label'=>'W8','v'=>92],
                ['label'=>'W9','v'=>88],['label'=>'W10','v'=>104],['label'=>'W11','v'=>118],['label'=>'W12','v'=>132],
            ]"/>
        </x-cards.card>

        {{-- Distribution donut --}}
        <x-cards.card eyebrow="Task distribution">
            <x-slot:titleSlot>86 <span class="card-title-sub">in flight</span></x-slot:titleSlot>
            <div style="display:flex; gap:18px; align-items:center;">
                <x-data.donut :data="[
                    ['value' => 28, 'color' => 'var(--signal)'],
                    ['value' => 14, 'color' => 'var(--warn)'],
                    ['value' => 38, 'color' => 'var(--ok)'],
                    ['value' => 6,  'color' => 'var(--err)'],
                ]" :center="['value' => '86', 'label' => 'TASKS']"/>
                <div style="flex:1;display:flex;flex-direction:column;gap:8px;">
                    @foreach([
                        ['l' => 'In progress', 'v' => 28, 'c' => 'var(--signal)'],
                        ['l' => 'In review',   'v' => 14, 'c' => 'var(--warn)'],
                        ['l' => 'Done',        'v' => 38, 'c' => 'var(--ok)'],
                        ['l' => 'Blocked',     'v' => 6,  'c' => 'var(--err)'],
                    ] as $row)
                        <div style="display:flex;align-items:center;gap:8px;font-size:12.5px;">
                            <x-shared.dot :color="$row['c']"/>
                            <span style="flex:1;">{{ $row['l'] }}</span>
                            <span class="mono" style="font-weight: 600;">{{ $row['v'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </x-cards.card>

        {{-- Today's schedule --}}
        <x-cards.card eyebrow="Today · 4 events" title="Schedule">
            <x-slot:header>
                <x-buttons.button variant="ghost" size="sm" href="{{ route('calendar.index') }}" iconAfter="chevRight">
                    Open
                </x-buttons.button>
            </x-slot:header>
            <div style="margin:-14px -14px 0;">
                <x-data.schedule-row time="09:30" duration="0:30" name="Sprint planning"   tag="Internal" :soon="true" countdown="in 18m"/>
                <x-data.schedule-row time="12:00" duration="1:00" name="Lunch w/ Northwind" tag="Client"/>
                <x-data.schedule-row time="14:00" duration="0:45" name="Design crit · Mobile" tag="Review"/>
                <x-data.schedule-row time="16:30" duration="0:30" name="1:1 with Priya"    tag="HR"/>
            </div>
        </x-cards.card>

        {{-- Recent activity --}}
        <x-cards.card span="span-2" eyebrow="Activity feed" title="Recent">
            <x-feedback.empty-state icon="activity"
                                    title="Activity feed coming soon"
                                    description="Wire this up to your /api/activity endpoint."/>
        </x-cards.card>
    </div>
</x-layouts.app>
