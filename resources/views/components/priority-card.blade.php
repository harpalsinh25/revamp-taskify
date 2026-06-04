<!-- meetings -->
{{$slot}}
@if (is_countable($priorities) && count($priorities) > 0)
<x-tk-table id="table" :url="url('/priority/list')"
    data-sort-name="id" data-sort-order="desc" data-query-params="queryParams"
    :columns="[
        ['checkbox' => true],
        ['field' => 'id', 'label' => get_label('id', 'ID'), 'sortable' => true],
        ['field' => 'title', 'label' => get_label('title', 'Title'), 'sortable' => true],
        ['field' => 'color', 'label' => get_label('preview', 'Preview'), 'sortable' => true],
        ['field' => 'created_at', 'label' => get_label('created_at', 'Created at'), 'sortable' => true, 'visible' => false],
        ['field' => 'updated_at', 'label' => get_label('updated_at', 'Updated at'), 'sortable' => true, 'visible' => false],
        ['field' => 'actions', 'label' => get_label('actions', 'Actions')]
    ]">
    <x-slot:before>
        <input type="hidden" id="data_type" value="priority">
    </x-slot:before>
</x-tk-table>
@else
<?php
$type = 'Priorities'; ?>
<x-empty-state-card :type="$type" />
@endif