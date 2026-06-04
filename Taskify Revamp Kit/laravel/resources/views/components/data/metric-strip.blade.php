@props([
    'metrics' => [],   // array of ['label','value','delta','trend']
])

<div class="metric-strip">
    @foreach($metrics as $m)
        <x-cards.stat-card
            :label="$m['label']"
            :value="$m['value']"
            :delta="$m['delta'] ?? null"
            :trend="$m['trend'] ?? 'up'"/>
    @endforeach
</div>
