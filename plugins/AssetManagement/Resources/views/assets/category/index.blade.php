@extends('layout')

@section('title')
    {{ get_label('asset_category', 'Asset Category') }}
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
                        <li class="breadcrumb-item">
                            <a href="{{ route('assets.index') }}">{{ get_label('assets', 'Assets') }}</a>
                        </li>
                        <li class="breadcrumb-item active">
                            {{ get_label('asset_category', 'Asset Category') }}
                        </li>
                    </ol>
                </nav>
            </div>
            <div>
                <button type="button" class="btn btn-sm btn-primary action_create_template" data-bs-toggle="offcanvas"
                    data-bs-target="#createCategoryOffcanvas" data-bs-toggle="tooltip" data-bs-placement="left"
                    data-bs-original-title="{{ get_label('create_asset_category', 'Create Asset Category') }}">
                    <i class='bx bx-plus'></i>
                </button>
            </div>
        </div>

        @if ($category->count() > 0)
            @php
                $visibleColumns = getUserPreferences('asset_category');
            @endphp
            @php
                $columns = [
                    ['checkbox' => true],
                    ['field' => 'id', 'label' => get_label('id', 'ID'), 'sortable' => true, 'visible' => !empty($visibleColumns) && !in_array('id', $visibleColumns) ? false : true],
                    ['field' => 'name', 'label' => get_label('name', 'Name'), 'sortable' => true, 'visible' => !empty($visibleColumns) && !in_array('name', $visibleColumns) ? false : true],
                    ['field' => 'color', 'label' => get_label('color', 'Color'), 'sortable' => true, 'visible' => !empty($visibleColumns) && !in_array('color', $visibleColumns) ? false : true],
                    ['field' => 'description', 'label' => get_label('description', 'Description'), 'sortable' => true, 'visible' => !empty($visibleColumns) && !in_array('description', $visibleColumns) ? false : true],
                    ['field' => 'created_at', 'label' => get_label('created_at', 'Created At'), 'sortable' => true, 'visible' => !empty($visibleColumns) && !in_array('created_at', $visibleColumns) ? false : true],
                    ['field' => 'updated_at', 'label' => get_label('updated_at', 'Updated At'), 'sortable' => true, 'visible' => !empty($visibleColumns) && !in_array('updated_at', $visibleColumns) ? false : true],
                    ['field' => 'actions', 'label' => get_label('actions', 'Actions'), 'visible' => !empty($visibleColumns) && !in_array('actions', $visibleColumns) ? false : true]
                ];
            @endphp
            <div class="card border shadow-none">
                <div class="card-body p-0">
                    <x-tk-table
                        id="table"
                        url="{{ route('assets.category.list') }}"
                        :columns="$columns"
                        data-sort-name="id"
                        data-sort-order="desc"
                        data-query-params="queryParams"
                    >
                        <x-slot name="before">
                            <input type="hidden" id="data_type" value="assets/category">
                            <input type="hidden" id="save_column_visibility">
                        </x-slot>
                    </x-tk-table>
                </div>
            </div>
        @else
            <div class="card empty-state text-center">
                <div class="card-body">
                    <div class="misc-wrapper">
                        <h2 class="mx-2 mb-2">
                            <span>{{ get_label('assets_categories_not_found', 'Assets Categories Not Found') }}</span>
                        </h2>
                        <p class="mx-2 mb-4">
                            {{ get_label('no_data_available', 'Oops! No data available yet.') }}
                        </p>

                        <button type="button" class="btn btn-md btn-primary action_create_template m-1"
                            data-bs-toggle="offcanvas" data-bs-target="#createCategoryOffcanvas" data-bs-toggle="tooltip"
                            data-bs-placement="left"
                            data-bs-original-title="{{ get_label('create_asset_category', 'Create Asset Category') }}">
                            {{ get_label('create_now', 'Create now') }}
                        </button>

                        <div class="mt-3">
                            <img src="{{ asset('/storage/no-result.png') }}" alt="No result" width="500"
                                class="img-fluid" />
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @include('assets::assets.offcanvas')

    <script src="{{ asset('assets/js/asset-plugin/assets.js') }}"></script>
@endsection

