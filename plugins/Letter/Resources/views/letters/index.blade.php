@extends('layout')

@section('title')
<?= get_label('letters', 'Letters') ?>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between mb-2 mt-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1">
                    <li class="breadcrumb-item">
                        <a href="{{ url('home') }}">{{ get_label('home', 'Home') }}</a>
                    </li>
                    <li class="breadcrumb-item active">
                        {{ get_label('letters', 'Letters') }}
                    </li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('letters.create') }}">
                <button type="button" class="btn btn-sm btn-primary action_create_letters" data-bs-toggle="tooltip" data-bs-placement="right" title="{{ get_label('create_letter', 'Create Letter') }}">
                    <i class="bx bx-plus"></i>
                </button>
            </a>
        </div>
    </div>

    @if ($letters > 0)
    @php
        $visibleColumns = getUserPreferences('letters');
    @endphp
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <select class="form-select js-example-basic-multiple" id="category_filter" aria-label="Default select example" data-placeholder="{{ get_label('select_categories', 'Select Categories') }}" data-allow-clear="true" multiple>
                        @foreach ($categories as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="table-responsive text-nowrap">
                <input type="hidden" id="data_type" value="letters">
                <input type="hidden" id="save_column_visibility">
                <table id="table"
                    data-toggle="table"
                    data-loading-template="loadingTemplate"
                    data-url="{{ url('/letters/list') }}"
                    data-icons-prefix="bx"
                    data-icons="icons"
                    data-show-refresh="true"
                    data-total-field="total"
                    data-trim-on-search="false"
                    data-data-field="rows"
                    data-page-list="[5, 10, 20, 50, 100, 200]"
                    data-search="true"
                    data-side-pagination="server"
                    data-show-columns="true"
                    data-pagination="true"
                    data-sort-name="id"
                    data-sort-order="desc"
                    data-mobile-responsive="true"
                    data-query-params="queryParams"
                >
                    <thead>
                        <tr>
                            <th data-checkbox="true"></th>
                            <th data-field="id" data-visible="{{ (in_array('id', $visibleColumns) || empty($visibleColumns)) ? 'true' : 'false' }}" data-sortable="true">{{ get_label('id', 'ID') }}</th>
                            <th data-field="title" data-visible="{{ (in_array('title', $visibleColumns) || empty($visibleColumns)) ? 'true' : 'false' }}" data-sortable="true">{{ get_label('title', 'Title') }}</th>
                            <th data-field="category" data-visible="{{ (in_array('category', $visibleColumns) || empty($visibleColumns)) ? 'true' : 'false' }}" data-sortable="true">{{ get_label('category', 'Category') }}</th>
                            <th data-field="user_name" data-visible="{{ (in_array('user_name', $visibleColumns) || empty($visibleColumns)) ? 'true' : 'false' }}" data-sortable="true">{{ get_label('user', 'User') }}</th>
                            <th data-field="status" data-visible="{{ (in_array('status', $visibleColumns) || empty($visibleColumns)) ? 'true' : 'false' }}" data-sortable="true">{{ get_label('status', 'Status') }}</th>
                            <th data-field="created_at" data-visible="{{ (in_array('created_at', $visibleColumns) || empty($visibleColumns)) ? 'true' : 'false' }}" data-sortable="true">{{ get_label('created_at', 'Created At') }}</th>
                            <th data-field="updated_at" data-visible="{{ (in_array('updated_at', $visibleColumns) || empty($visibleColumns)) ? 'true' : 'false' }}" data-sortable="true">{{ get_label('updated_at', 'Updated At') }}</th>
                            <th data-field="actions" data-visible="{{ (in_array('actions', $visibleColumns) || empty($visibleColumns)) ? 'true' : 'false' }}">{{ get_label('actions', 'Actions') }}</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
    @else
    @php $type = 'Letters'; @endphp
    <x-empty-state-card :type="$type" />
    @endif
</div>

<script>
    var label_update = '{{ get_label('update', 'Update') }}';
    var label_delete = '{{ get_label('delete', 'Delete') }}';
</script>
<script src="{{ asset('modules/letters/js/letters.js') }}"></script>
@endsection
