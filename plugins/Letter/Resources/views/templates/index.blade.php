@extends('layout')

@section('title')
    <?= get_label('letter_templates', 'Letter Templates') ?>
@endsection

@section('content')
<div class="container-fluid">

  <div class="d-flex justify-content-between mt-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('home.index') }}"><?= get_label('home', 'Home') ?></a>
                        </li>
                        <li class="breadcrumb-item">
                            <?= get_label('settings', 'Settings') ?></a>
                        </li>
                        <li class="breadcrumb-item active"><?= get_label('custom_fields', 'Custom Fields') ?></li>
                    </ol>
                </nav>
            </div>
             <div>
                   <a href="{{ route('letter-templates.create') }}" class="btn btn-primary btn-sm">
                <i class="bx bx-plus"></i> {{ get_label('create_letter_template', 'Create Template') }}
            </a>
            </div>
        </div>


    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="row gy-2">
                <div class="col-md-4">
                    <select class="form-select js-select2" id="category_filter" multiple data-placeholder="{{ get_label('select_categories', 'Select Categories') }}">
                        @foreach ($categories as $key => $category)
                            <option value="{{ $key }}">{{ $category }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="status_filter">
                        <option value="">{{ get_label('all_statuses', 'All Statuses') }}</option>
                        <option value="1">{{ get_label('active', 'Active') }}</option>
                        <option value="0">{{ get_label('inactive', 'Inactive') }}</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button id="reset_filters" class="btn btn-light w-100">
                        <i class="bx bx-refresh"></i> {{ get_label('reset_filters', 'Reset Filters') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="card-body">
            <table id="letter_templates_table"
                   class="table table-striped"
                   data-toggle="table"
                   data-search="true"
                   data-pagination="true"
                   data-side-pagination="server"
                   data-url="{{ route('letter-templates.list') }}"
                   data-query-params="queryParams"
                   data-page-list="[5, 10, 20, 50, 100]"
                   data-sort-name="id"
                   data-sort-order="desc">
                <thead>
                    <tr>
                        <th data-field="id" data-sortable="true">{{ get_label('id', 'ID') }}</th>
                        <th data-field="name" data-sortable="true">{{ get_label('name', 'Name') }}</th>
                        <th data-field="category" data-sortable="true">{{ get_label('category', 'Category') }}</th>
                        <th data-field="description">{{ get_label('description', 'Description') }}</th>
                        <th data-field="is_active" data-sortable="true">{{ get_label('status', 'Status') }}</th>
                        <th data-field="created_by">{{ get_label('created_by', 'Created By') }}</th>
                        <th data-field="created_at" data-sortable="true">{{ get_label('created_at', 'Created At') }}</th>
                        <th data-field="updated_at" data-sortable="true">{{ get_label('updated_at', 'Updated At') }}</th>
                        <th data-field="actions">{{ get_label('actions', 'Actions') }}</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/letter-plugin/letter-templates.js') }}"></script>
@endpush
