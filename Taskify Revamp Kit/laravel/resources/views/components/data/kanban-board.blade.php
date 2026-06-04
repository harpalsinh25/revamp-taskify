@props([
    'columns' => [],   // [['id'=>'progress','name'=>'In Progress','color'=>'var(--signal)','tasks'=>[…]]]
])

<div class="kanban">
    @foreach($columns as $col)
        <div class="kcol" data-col="{{ $col['id'] }}">
            <header class="kcol-head">
                <x-shared.dot :color="$col['color'] ?? null"/>
                <span class="kcol-name">{{ $col['name'] }}</span>
                <span class="kcol-count mono">{{ count($col['tasks'] ?? []) }}</span>
                <x-buttons.icon-button icon="plus" size="sm"/>
            </header>
            <div class="kcol-body">
                @foreach($col['tasks'] ?? [] as $task)
                    <x-data.kanban-card :task="$task"/>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
