@props([
    'columns'    => [],   // [['key'=>'name','label'=>'Name','sortable'=>true], …]
    'rows'       => [],   // array of associative arrays (or models toArray())
    'selectable' => false,
    'rowKey'     => 'id',
    'empty'      => 'No records found',
])

<div {{ $attributes->merge(['class' => 'table-wrap']) }}>
    @isset($toolbar)
        <div class="table-toolbar">{{ $toolbar }}</div>
    @endisset

    <table class="table">
        <thead>
            <tr>
                @if($selectable)
                    <th class="table-checkbox">
                        <input type="checkbox" class="check" data-select-all aria-label="Select all"/>
                    </th>
                @endif
                @foreach($columns as $col)
                    <th @if(!empty($col['sortable'])) data-sort="" data-col="{{ $col['key'] }}" @endif>
                        {{ $col['label'] }}
                    </th>
                @endforeach
                @isset($actions)
                    <th class="table-actions">Actions</th>
                @endisset
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    @if($selectable)
                        <td class="table-checkbox">
                            <input type="checkbox" class="check" data-select-row value="{{ $row[$rowKey] ?? '' }}"/>
                        </td>
                    @endif
                    @foreach($columns as $col)
                        <td>{!! $row[$col['key']] ?? '—' !!}</td>
                    @endforeach
                    @isset($actions)
                        <td class="table-actions">{{ $actions($row) }}</td>
                    @endisset
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($columns) + ($selectable ? 1 : 0) + (isset($actions) ? 1 : 0) }}">
                        <x-feedback.empty-state :title="$empty"/>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @isset($footer)
        <div class="table-footer">{{ $footer }}</div>
    @endisset
</div>
