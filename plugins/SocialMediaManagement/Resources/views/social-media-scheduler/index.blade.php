@extends('layout')

@section('title')
    {{ get_label('social_media_scheduler', 'Social Media Scheduler') }}
@endsection

@section('content')
<link rel="stylesheet" href="{{ asset('assets/css/social/social.css') }}">
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb-2 mt-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ url('home') }}">{{ get_label('home', 'Home') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            {{ get_label('social_media', 'Social Media') }}
                        </li>
                        <li class="breadcrumb-item active">
                            {{ get_label('posts', 'Posts') }}
                        </li>
                    </ol>
                </nav>
            </div>
            <div>
                @php
                    $socialsDefaultView = getUserPreferences('socials', 'default_view');
                @endphp
                @if ($socialsDefaultView === 'socials')
                    <span class="badge bg-primary">{{ get_label('default_view', 'Default View') }}</span>
                @else
                    <a href="javascript:void(0);">
                        <span class="badge bg-secondary" id="set-default-view" data-type="socials" data-view="socials">
                            {{ get_label('set_as_default_view', 'Set as Default View') }}
                        </span>
                    </a>
                @endif
            </div>
            <div>
                <a href="{{ route('social.create') }}">
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="right"
                        data-bs-original-title="{{ get_label('create_post', 'Create Post') }}">
                        <i class="bx bx-plus"></i>
                    </button>
                </a>
                <a href="{{ route('social.calendar') }}"><button type="button" class="btn btn-sm btn-primary"
                        data-bs-toggle="tooltip" data-bs-placement="left"
                        data-bs-original-title="<?= get_label('calendar_view', 'Calendar view') ?>"><i
                            class='bx bx-calendar'></i></button></a>

                <a href="{{ route('social.analytics') }}">
                    <button type="button" id="" class="btn btn-sm btn-primary action_create_template"
                        data-bs-toggle="tooltip" data-bs-placement="left"
                        data-bs-original-title="{{ get_label('analytics', 'Analytics') }}">
                        <i class='bx bx-chart'></i>
                    </button>
                </a>
                </li>
            </div>
        </div>

        @if ($posts->count() > 0)
            @php
                $visibleColumns = getUserPreferences('socials');
            @endphp
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-3 mb-3 mb-md-0">
                            <select class="form-select tom_static_select" id="select_social_platforms"
                                name="platform" aria-label="Default select example"
                                data-placeholder="<?= get_label('filter_by_platform', 'Filter By Platform') ?>"
                                data-allow-clear="true">
                                <option></option>
                                <option value="facebook"
                                    <?= request()->sort && request()->sort == 'facebook' ? 'selected' : '' ?>>
                                    <?= get_label('facebook', 'Facebook') ?></option>
                                <option value="instagram"
                                    <?= request()->sort && request()->sort == 'instagram' ? 'selected' : '' ?>>
                                    <?= get_label('instagram', 'Instagram') ?></option>

                                <option value="linkedin"
                                    <?= request()->sort && request()->sort == 'linkedin' ? 'selected' : '' ?>>
                                    <?= get_label('linkedin', 'Linkedin') ?></option>

                                <option value="pinterest"
                                    <?= request()->sort && request()->sort == 'pinterest' ? 'selected' : '' ?>>
                                    <?= get_label('pinterest', 'Pinterest') ?></option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <select class="form-select tom_static_select" id="select_social_stastuses"
                                name="status" aria-label="Default select example"
                                data-placeholder="<?= get_label('select_by_status', 'Select By Status') ?>"
                                data-allow-clear="true">
                                <option></option>
                                <option value="pending"
                                    <?= request()->sort && request()->sort == 'pending' ? 'selected' : '' ?>>
                                    <?= get_label('pending', 'Pending') ?></option>
                                <option value="scheduled"
                                    <?= request()->sort && request()->sort == 'scheduled' ? 'selected' : '' ?>>
                                    <?= get_label('scheduled', 'Scheduled') ?></option>

                                <option value="published"
                                    <?= request()->sort && request()->sort == 'published' ? 'selected' : '' ?>>
                                    <?= get_label('published', 'Published') ?></option>

                                <option value="failed"
                                    <?= request()->sort && request()->sort == 'failed' ? 'selected' : '' ?>>
                                    <?= get_label('failed', 'Failed') ?></option>
                                <option value="partially_published"
                                    <?= request()->sort && request()->sort == 'partially_published' ? 'selected' : '' ?>>
                                    <?= get_label('partially_published', 'Partially Published') ?></option>
                            </select>
                        </div>
                        <div class="col-md-6 d-flex justify-content-md-end">
                            <button type="button" class="btn btn-sm btn-secondary" id="clear_filters"
                                style="height: 38px;"><i class='bx bx-refresh'></i> <?= get_label('clear_filters', 'Clear filters') ?></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card border shadow-none">
                <div class="card-body p-0">
                    @php
                    $columns = [
                        ['checkbox' => true],
                        ['field' => 'id', 'label' => get_label('id', 'ID'), 'sortable' => true, 'visible' => (in_array('id', $visibleColumns) || empty($visibleColumns))],
                        ['field' => 'caption', 'label' => get_label('caption', 'Caption'), 'sortable' => true, 'visible' => (in_array('caption', $visibleColumns) || empty($visibleColumns))],
                        ['field' => 'platforms', 'label' => get_label('platforms', 'Platforms'), 'sortable' => false, 'visible' => (in_array('platforms', $visibleColumns) || empty($visibleColumns))],
                        ['field' => 'status', 'label' => get_label('status', 'Status'), 'sortable' => true, 'visible' => (in_array('status', $visibleColumns) || empty($visibleColumns))],
                        ['field' => 'scheduled_at', 'label' => get_label('scheduled_at', 'Scheduled At'), 'sortable' => true, 'visible' => (in_array('scheduled_at', $visibleColumns) || empty($visibleColumns))],
                        ['field' => 'created_at', 'label' => get_label('created_at', 'Created At'), 'sortable' => true, 'visible' => (in_array('created_at', $visibleColumns) || empty($visibleColumns))],
                        ['field' => 'updated_at', 'label' => get_label('updated_at', 'Updated At'), 'sortable' => true, 'visible' => (in_array('updated_at', $visibleColumns) || empty($visibleColumns))],
                        ['field' => 'actions', 'label' => get_label('actions', 'Actions'), 'visible' => (in_array('actions', $visibleColumns) || empty($visibleColumns))]
                    ];
                    @endphp
                    <x-tk-table 
                        id="table"
                        url="{{ route('social.list') }}"
                        :columns="$columns"
                        data-sort-name="id"
                        data-sort-order="desc"
                        data-query-params="queryParams"
                    >
                        <x-slot name="before">
                            <input type="hidden" id="data_type" value="social-media-scheduler">
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
                            <span>{{ get_label('posts_not_found', 'Posts Not Found') }}</span>
                        </h2>
                        <p class="mx-2 mb-4">
                            {{ get_label('no_posts_available', 'Oops! No posts available yet.') }}
                        </p>

                        <a href="{{ route('social.create') }}" class="btn btn-md btn-primary m-1">
                            {{ get_label('create_now', 'Create now') }}
                        </a>

                        <div class="mt-3">
                            <img src="{{ asset('/storage/no-result.png') }}" alt="No result" width="500"
                                class="img-fluid" />
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Quick View Modal HTML -->
    <div class="modal fade" id="quickViewModal" tabindex="-1" aria-labelledby="quickViewModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="quickViewModalLabel">
                        <i class="bx bx-show-alt me-2"></i>Post Publishing Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="quickViewContent">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        var label_update = '{{ get_label('update', 'Update') }}';
        var label_delete = '{{ get_label('delete', 'Delete') }}';
    </script>
    <script src="{{ asset('assets/js/social/social.js') }}"></script>
@endsection
