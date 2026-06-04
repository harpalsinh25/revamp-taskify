<x-layouts.app active="projects" pageTitle="Brand Refresh" pageSubtitle="Projects">
    <x-slot:headerActions>
        <x-buttons.button variant="secondary" size="sm" icon="users">Invite</x-buttons.button>
        <x-buttons.button variant="primary"   size="sm" icon="plus">New task</x-buttons.button>
    </x-slot:headerActions>

    {{-- Project header --}}
    <header style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px;">
        <div>
            <div class="mono eyebrow"><x-shared.dot color="oklch(0.65 0.18 290)"/> BRAND-REFRESH · NORTHWIND</div>
            <h1 style="margin-top:6px;">Brand Refresh</h1>
            <div style="display:flex;gap:14px;margin-top:8px;color:var(--fg-2);font-size:12.5px;">
                <span><x-shared.icon name="check" size="12"/> 12/28 tasks</span>
                <span><x-shared.icon name="users" size="12"/> 5 members</span>
                <span><x-shared.icon name="calendar" size="12"/> Due Apr 12</span>
            </div>
        </div>
        <x-shared.avatar-stack :names="['Priya Mehta','Lin Tan','Devon Rios','Marcus Jin','Sara Kim']" :max="4" :size="24"/>
    </header>

    {{-- Toolbar --}}
    <div style="display: flex; gap: 8px; align-items: center; margin-bottom: 16px;">
        <x-buttons.segmented :options="[
            ['value' => 'board',    'label' => 'Board',    'icon' => 'columns'],
            ['value' => 'list',     'label' => 'List',     'icon' => 'list'],
            ['value' => 'timeline', 'label' => 'Timeline', 'icon' => 'timeline'],
        ]" value="board"/>

        <x-forms.input placeholder="Search tasks…" icon="search" :suffix="'/'" size="sm" style="max-width:280px;"/>

        <x-buttons.button variant="ghost" size="sm" icon="filter">Filter</x-buttons.button>
        <x-buttons.button variant="ghost" size="sm" icon="sort">Sort</x-buttons.button>
        <x-badges.chip active>Sprint 14</x-badges.chip>
        <x-badges.chip>Priority: P1, P2</x-badges.chip>

        <span style="flex:1;"></span>
        <x-buttons.button variant="ghost" size="sm" icon="ai" data-toggle="offcanvas" data-target="#task-drawer">
            Open task
        </x-buttons.button>
    </div>

    {{-- Kanban --}}
    <x-data.kanban-board :columns="[
        ['id' => 'backlog',  'name' => 'Backlog',     'color' => 'var(--fg-3)',  'tasks' => [
            ['id' => 't1', 'code' => 'NS-204', 'priority' => 'P1', 'title' => 'Design empty states for client portal', 'tags' => ['design','portal'], 'assignees' => ['Lin Tan','Devon Rios'], 'due' => 'Mar 22', 'subtasks' => [0, 4], 'comments' => 3],
        ]],
        ['id' => 'progress', 'name' => 'In Progress', 'color' => 'var(--signal)', 'tasks' => [
            ['id' => 't4', 'code' => 'NS-198', 'priority' => 'P1', 'title' => 'Onboarding redesign — flow v3', 'tags' => ['design','onboarding'], 'assignees' => ['Lin Tan','Priya Mehta'], 'due' => 'Mar 19', 'subtasks' => [4, 7], 'comments' => 12, 'branch' => 'lin/onboarding-v3'],
        ]],
        ['id' => 'review',   'name' => 'In Review',   'color' => 'var(--warn)', 'tasks' => [
            ['id' => 't7', 'code' => 'NS-189', 'priority' => 'P2', 'title' => 'Q2 marketing plan deck', 'tags' => ['marketing'], 'assignees' => ['Priya Mehta'], 'due' => 'Mar 18', 'subtasks' => [5, 5], 'comments' => 9],
        ]],
        ['id' => 'done',     'name' => 'Done',        'color' => 'var(--ok)',   'tasks' => [
            ['id' => 't9', 'code' => 'NS-176', 'priority' => 'P1', 'title' => 'Stripe → Wise migration', 'tags' => ['finance'], 'assignees' => ['Devon Rios','Marcus Jin'], 'due' => 'Mar 12', 'subtasks' => [8, 8], 'comments' => 14],
        ]],
    ]"/>

    {{-- Task drawer (offcanvas) --}}
    @push('overlays')
        <x-overlays.offcanvas id="task-drawer" position="right" size="420px">
            <x-slot:title>
                <span class="mono" style="font-size:11.5px;font-weight:600;">NS-198</span>
                <x-badges.status-pill status="progress">In Progress</x-badges.status-pill>
            </x-slot:title>

            <h2 style="margin: 0 0 12px;">Onboarding redesign — flow v3</h2>

            <div style="display:grid;grid-template-columns:90px 1fr;gap:8px 16px;">
                <div class="eyebrow">Priority</div><div><span class="mono" style="color:var(--err)">● P1 · High</span></div>
                <div class="eyebrow">Assignees</div><div><x-shared.avatar-stack :names="['Lin Tan','Priya Mehta','Alex K']" :max="4" :size="20"/></div>
                <div class="eyebrow">Due</div><div><span class="mono" style="color:var(--err)">Mar 19 · in 3d</span></div>
                <div class="eyebrow">Sprint</div><div><x-badges.chip active>Sprint 14</x-badges.chip></div>
                <div class="eyebrow">Branch</div><div><span class="mono"><x-shared.icon name="branch" size="11"/> lin/onboarding-v3</span></div>
            </div>

            <hr style="border:none;height:1px;background:var(--line);margin:18px 0;"/>

            <div>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                    <strong style="font-size:12px;">Subtasks</strong>
                    <span class="mono txt-xs txt-subtle">4/7</span>
                </div>
                <x-data.progress-bar :value="57"/>
            </div>

            <x-slot:footer>
                <x-forms.input placeholder="Comment, or type / for actions" icon="msg" style="flex:1;"/>
                <x-buttons.icon-button icon="paperclip"/>
            </x-slot:footer>
        </x-overlays.offcanvas>
    @endpush
</x-layouts.app>
