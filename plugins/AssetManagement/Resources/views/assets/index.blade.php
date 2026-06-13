@extends('layout')

@section('title')
    {{ get_label('assets', 'Assets') }}
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
                            {{ get_label('assets', 'Assets') }}
                        </li>
                    </ol>
                </nav>
            </div>
            <div>
                @if (isAdminOrHasAllDataAccess())
                    <a href="javascript:void(0);" data-bs-toggle="offcanvas" data-bs-target="#createAssetOffcanvas">
                        <button type="button" id="createAssetModalBtn"
                            class="btn btn-sm btn-primary action_create_template" data-bs-toggle="tooltip"
                            data-bs-placement="left"
                            data-bs-original-title="{{ get_label('create_asset', 'Create Asset') }}">
                            <i class='bx bx-plus'></i>
                        </button>
                    </a>
                    <a href="javascript:void(0);" data-bs-toggle="offcanvas" data-bs-target="#bulkAssignOffcanvas">
                        <button type="button" id="bulkAssignModalBtn" class="btn btn-sm btn-primary action_create_template"
                            data-bs-toggle="tooltip" data-bs-placement="left"
                            data-bs-original-title="{{ get_label('bulk_assign', 'Bulk assign') }}">
                            <i class='bx bx-group'></i>
                        </button>
                    </a>
                    <a href="javascript:void(0);" data-bs-toggle="offcanvas" data-bs-target="#bulkAssetsUploadOffcanvas">
                        <button type="button" id="bulkAssetsUploadModalBtn"
                            class="btn btn-sm btn-primary action_create_template" data-bs-toggle="tooltip"
                            data-bs-placement="left" data-bs-original-title="{{ get_label('bulk_upload', 'Bulk Upload') }}">
                            <i class='bx bx-upload'></i>
                        </button>
                    </a>
                    <a href="{{ route('assets.global-analytics') }}">
                        <button type="button" class="btn btn-sm btn-primary action_create_template"
                            data-bs-toggle="tooltip" data-bs-placement="left"
                            data-bs-original-title="{{ get_label('analytics', 'Analytics') }}">
                            <i class='bx bx-chart'></i>
                        </button>
                    </a>
                    <a href="{{ route('assets.export') }}">
                        <button type="button" class="btn btn-sm btn-primary action_create_template"
                            data-bs-toggle="tooltip" data-bs-placement="left"
                            data-bs-original-title="{{ get_label('export_assets', 'Export All Assets') }}">
                            <i class='bx bx-export'></i>
                        </button>
                    </a>
                @endif
            </div>
        </div>

        @if ($assets->count() > 0)
            @php
                $visibleColumns = getUserPreferences('assets');
                $columns = [
                    ['checkbox' => true],
                    ['field' => 'id', 'label' => get_label('id', 'ID'), 'sortable' => true, 'visible' => !empty($visibleColumns) && !in_array('id', $visibleColumns) ? false : true],
                    ['field' => 'name', 'label' => get_label('name', 'Name'), 'sortable' => true, 'visible' => !empty($visibleColumns) && !in_array('name', $visibleColumns) ? false : true],
                    ['field' => 'lent_to', 'label' => get_label('lent_to', 'Lent To'), 'sortable' => true, 'visible' => !empty($visibleColumns) && !in_array('lent_to', $visibleColumns) ? false : true],
                    ['field' => 'category', 'label' => get_label('category', 'Category'), 'sortable' => true, 'visible' => !empty($visibleColumns) && !in_array('category', $visibleColumns) ? false : true],
                    ['field' => 'description', 'label' => get_label('description', 'Description'), 'sortable' => true, 'visible' => !empty($visibleColumns) && !in_array('description', $visibleColumns) ? false : true],
                    ['field' => 'status', 'label' => get_label('status', 'Status'), 'sortable' => true, 'visible' => !empty($visibleColumns) && !in_array('status', $visibleColumns) ? false : true],
                    ['field' => 'asset_tag', 'label' => get_label('asset_tag', 'Asset Tag'), 'sortable' => true, 'visible' => !empty($visibleColumns) && !in_array('asset_tag', $visibleColumns) ? false : true],
                    ['field' => 'purchase_cost', 'label' => get_label('purchase_cost', 'Purchase Cost'), 'sortable' => true, 'visible' => !empty($visibleColumns) && !in_array('purchase_cost', $visibleColumns) ? false : true],
                    ['field' => 'purchase_date', 'label' => get_label('purchase_date', 'Purchase Date'), 'sortable' => true, 'visible' => !empty($visibleColumns) && !in_array('purchase_date', $visibleColumns) ? false : true],
                    ['field' => 'created_at', 'label' => get_label('created_at', 'Created At'), 'sortable' => true, 'visible' => !empty($visibleColumns) && !in_array('created_at', $visibleColumns) ? false : true],
                    ['field' => 'updated_at', 'label' => get_label('updated_at', 'Updated At'), 'sortable' => true, 'visible' => !empty($visibleColumns) && !in_array('updated_at', $visibleColumns) ? false : true],
                    ['field' => 'actions', 'label' => get_label('actions', 'Actions'), 'visible' => !empty($visibleColumns) && !in_array('actions', $visibleColumns) ? false : true]
                ];
            @endphp
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row g-3 align-items-end tk-filter-row">
                        <div class="col-md-4 mb-3">
                            <select class="form-select select-asset-category_in_filter" id="select_categories"
                                name="categories[]" aria-label="Default select example"
                                data-placeholder="{{ get_label('filter_by_categorie', 'Filter by Categories') }}"
                                data-allow-clear="true" multiple></select>
                        </div>
                        @if (isAdminOrHasAllDataAccess())
                            <div class="col-md-4 mb-3">
                                <select class="form-select select-asset-assigned_to_in_filter" id="select_assigned_to"
                                    name="users[]" aria-label="Default select example"
                                    data-placeholder="{{ get_label('filter_by_assigned_users', 'Filter by Assigned Users ') }}"
                                    data-allow-clear="true" multiple></select>
                            </div>
                        @endif
                        <div class="col-md-4 mb-3">
                            <select class="form-select asset_status tom_static_select" id="asset_status" name="asset_status"
                                aria-label="Default select example"
                                data-placeholder="{{ get_label('filter_by_statuses', 'Filter by statuses') }}"
                                data-allow-clear="true" multiple>
                                <option value="available">{{ get_label('available', 'Available') }}</option>
                                <option value="non-functional">{{ get_label('non_functional', 'Non-Functional') }}</option>
                                <option value="lost">{{ get_label('lost', 'Lost') }}</option>
                                <option value="damaged">{{ get_label('damaged', 'Damaged') }}</option>
                                <option value="lent">{{ get_label('lent', 'Lent') }}</option>
                                <option value="under-maintenance">{{ get_label('under_maintenance', 'Under Maintenance') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border shadow-none">
                <div class="card-body p-0">
                    <x-tk-table
                        id="table"
                        url="{{ route('assets.list') }}"
                        :columns="$columns"
                        data-sort-name="id"
                        data-sort-order="desc"
                        data-query-params="queryParams"
                    >
                        <x-slot name="before">
                            <input type="hidden" id="data_type" value="assets">
                            <input type="hidden" id="save_column_visibility">
                        </x-slot>
                    </x-tk-table>
                </div>
            </div>
        @else
            @if (isAdminOrHasAllDataAccess())
                <div class="card empty-state text-center">
                    <div class="card-body">
                        <div class="misc-wrapper">
                            <h2 class="mx-2 mb-2">
                                <span>{{ get_label('assets_not_found', 'Assets Not Found') }}</span>
                            </h2>
                            <p class="mx-2 mb-4">{{get_label('no_data_available','Oops! No data available yet.')}}</p>
                            <a href="javascript:void(0);"
                                class="btn btn-md btn-primary action_create_template m-1"
                                id="createAssetModalBtn" data-bs-toggle="offcanvas"
                                data-bs-target="#createAssetOffcanvas"
                                title="{{ get_label('create_asset', 'Create Asset') }}">
                                {{ get_label('create_now', 'Create now') }}
                            </a>
                            <div class="mt-3">
                                <img src="{{ asset('/storage/no-result.png') }}" alt="No result"
                                    width="500" class="img-fluid" />
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="card empty-state text-center">
                    <div class="card-body">
                        <div class="misc-wrapper">
                            <h2 class="mx-2 mb-2">
                                <span>{{ get_label('you_dont_have_any_assets_assigned_to_you', 'You dont Have Any Assets Assigned To You.') }}</span>
                            </h2>
                            <p class="mx-2 mb-4">
                                {{ get_label('contact_admin', 'Contact admin if you think this is an error') }}
                            </p>
                            <div class="mt-3">
                                <img src="{{ asset('/storage/no-result.png') }}" alt="No result" width="500"
                                    class="img-fluid" />
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endif
    </div>

    @include('assets::assets.offcanvas')

    <script src="{{ asset('assets/js/asset-plugin/assets.js') }}"></script>
@endsection
