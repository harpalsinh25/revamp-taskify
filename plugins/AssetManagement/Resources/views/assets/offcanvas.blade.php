@if (isset($categories) && isset($users))
    <div class="offcanvas offcanvas-end offcanvas-responsive" tabindex="-1" id="createAssetOffcanvas"
        aria-labelledby="CreateAssetOffcanvasLabel">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="CreateAssetOffcanvasLabel">
                {{ get_label('create_asset', 'Create Asset') }}
            </h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                aria-label="{{ get_label('close', 'Close') }}"></button>
        </div>
        <div class="offcanvas-body p-4">
            <form id="assetForm" class="asset-form-submit-event" action="{{ route('assets.store') }}" method="POST"
                enctype="multipart/form-data">
                <input type="hidden" name="dnr" />
                <input type="hidden" name="table" value="table" />
                <input type="hidden" id="editStatusId" />

                {{-- Asset Picture Card (top, full-width) --}}
                <div class="card border shadow-none mb-4">
                    <div class="card-body p-3">
                        <h6 class="fw-semibold mb-3">
                            <i class="bx bx-image me-2"></i>{{ get_label('asset_picture', 'Asset Picture') }}
                        </h6>
                        <div id="create-no-image-placeholder" class="mb-3 text-center">
                            <div class="text-muted rounded border border-2 border-dashed p-4">
                                <i class="bx bx-image fs-1 d-block mb-2"></i>
                                <small>{{ get_label('no_image_uploaded', 'No image uploaded') }}</small>
                            </div>
                        </div>
                        <div id="create-current-picture-preview" class="mb-3 text-center" style="display: none;">
                            <img src="" alt="{{ get_label('current_asset_picture', 'Current Asset Picture') }}"
                                id="create-preview-image" class="img-fluid rounded border"
                                style="max-width: 100%; max-height: 200px; object-fit: cover; cursor: pointer;">
                        </div>
                        <div id="create-image-actions" class="d-grid gap-2 mb-3" style="display: none;">
                            <button type="button" class="btn btn-outline-primary btn-sm open-full-image-btn"
                                data-target="create-preview-image">
                                <i class="bx bx-show me-1"></i>{{ get_label('view_full_image', 'View Full Image') }}
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm remove-image-btn"
                                data-modal="create">
                                <i class="bx bx-trash me-1"></i>{{ get_label('remove_image', 'Remove Image') }}
                            </button>
                        </div>
                        <input type="file" class="form-control form-control-sm asset-picture-input"
                            id="create-asset-picture" name="picture" data-modal="create"
                            accept=".jpg,.jpeg,.png,.gif,.webp">
                        <div class="form-text mt-1">
                            <small class="text-muted">{{ get_label('supported_formats', 'Supported: JPG, JPEG, PNG, GIF, WebP') }}</small>
                        </div>
                    </div>
                </div>

                {{-- Asset Information --}}
                <div class="mb-4">
                    <h6 class="fw-semibold mb-3"><i class="bx bx-info-circle me-2"></i>{{ get_label('asset_information', 'Asset Information') }}</h6>
                    <div class="row g-3">
                        <!-- Asset Name -->
                        <div class="col-md-6">
                            <label for="create-asset-name" class="form-label">{{ get_label('asset_name', 'Asset Name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="create-asset-name" name="name" maxlength="255" placeholder="{{ get_label('enter_asset_name', 'Enter asset name') }}">
                        </div>

                        <!-- Asset Tag -->
                        <div class="col-md-6">
                            <label for="create-asset-tag" class="form-label">{{ get_label('asset_tag', 'Asset Tag') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="create-asset-tag" name="asset_tag" maxlength="255" placeholder="{{ get_label('enter_asset_tag', 'Enter asset tag') }}">
                        </div>

                        <!-- Category -->
                        <div class="col-md-6">
                            <label for="create-asset-category" class="form-label">{{ get_label('category', 'Category') }} <span class="text-danger">*</span></label>
                            <select class="form-select tom_static_select" id="create-asset-category" name="category_id" required data-placeholder="{{ get_label('select_category', 'Select Category') }}">
                                <option value=""></option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <div class="d-flex mt-2 gap-2">
                                <a href="javascript:void(0);" id="createCategoryModalBtn">
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-original-title="{{ get_label('create_category', 'Create Category') }}"><i class="bx bx-plus"></i></button>
                                </a>
                                <a href="{{ route('assets.category.index') }}">
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-original-title="{{ get_label('manage_category', 'Manage Category') }}"><i class="bx bx-list-ul"></i></button>
                                </a>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="col-md-6">
                            <label for="create-asset-status" class="form-label">{{ get_label('status', 'Status') }} <span class="text-danger">*</span></label>
                            <select class="form-select tom_static_select" id="create-asset-status" name="status" data-placeholder="{{ get_label('select_status', 'Select Status') }}">
                                <option value="available">{{ get_label('available', 'Available') }}</option>
                                <option value="non-functional">{{ get_label('non_functional', 'Non-Functional') }}</option>
                                <option value="lost">{{ get_label('lost', 'Lost') }}</option>
                                <option value="damaged">{{ get_label('damaged', 'Damaged') }}</option>
                                <option value="under-maintenance">{{ get_label('under_maintenance', 'Under Maintenance') }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Purchase Details --}}
                <div class="mb-4">
                    <h6 class="fw-semibold mb-3"><i class="bx bx-purchase-tag me-2"></i>{{ get_label('purchase_details', 'Purchase Details') }}</h6>
                    <div class="row g-3">
                        <!-- Purchase Date -->
                        <div class="col-md-6">
                            <label for="create-asset-purchase-date" class="form-label">{{ get_label('purchase_date', 'Purchase Date') }}</label>
                            <input type="date" class="form-control" id="create-asset-purchase-date" name="purchase_date">
                        </div>
                        <!-- Purchase Cost -->
                        <div class="col-md-6">
                            <label for="create-asset-purchase-cost" class="form-label">{{ get_label('purchase_cost', 'Purchase Cost') }}</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">{{ $general_settings['currency_symbol'] }}</span>
                                <input type="number" step="0.01" class="form-control" id="create-asset-purchase-cost" name="purchase_cost">
                            </div>
                        </div>
                        <!-- Description -->
                        <div class="col-12">
                            <label for="create-asset-description" class="form-label">{{ get_label('description', 'Description') }}</label>
                            <textarea class="form-control" id="create-asset-description" name="description" rows="4" placeholder="{{ get_label('enter_description', 'Enter description') }}"></textarea>
                        </div>
                    </div>
                </div>


                {{-- Footer --}}
                <div class="d-flex justify-content-end mt-3 gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">{{ get_label('close', 'Close') }}</button>
                    <button type="submit" id="create-submit-btn" class="btn btn-primary">{{ get_label('create', 'Create') }}</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Update Asset Offcanvas -->
    <div class="offcanvas offcanvas-end offcanvas-responsive" tabindex="-1" id="updateAssetOffcanvas"
        aria-labelledby="UpdateAssetOffcanvasLabel">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="UpdateAssetOffcanvasLabel">
                {{ get_label('update_asset', 'Update Asset') }}
            </h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                aria-label="{{ get_label('close', 'Close') }}"></button>
        </div>
        <div class="offcanvas-body p-4">
            <form id="updateAssetForm" class="asset-form-submit-event" action="" method="POST"
                enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="dnr" />
                <input type="hidden" name="table" value="table" />
                <input type="hidden" id="editStatusId" />

                {{-- Asset Picture Card (top, full-width) --}}
                <div class="card border shadow-none mb-4">
                    <div class="card-body p-3">
                        <h6 class="fw-semibold mb-3">
                            <i class="bx bx-image me-2"></i>{{ get_label('asset_picture', 'Asset Picture') }}
                        </h6>
                        <div id="update-no-image-placeholder" class="mb-3 text-center">
                            <div class="text-muted rounded border border-2 border-dashed p-4">
                                <i class="bx bx-image fs-1 d-block mb-2"></i>
                                <small>{{ get_label('no_image_uploaded', 'No image uploaded') }}</small>
                            </div>
                        </div>
                        <div id="update-current-picture-preview" class="mb-3 text-center" style="display: none;">
                            <img src="" alt="{{ get_label('current_asset_picture', 'Current Asset Picture') }}"
                                id="update-preview-image" class="img-fluid rounded border"
                                style="max-width: 100%; max-height: 200px; object-fit: cover; cursor: pointer;">
                        </div>
                        <div id="update-image-actions" class="d-grid gap-2 mb-3" style="display: none;">
                            <button type="button" class="btn btn-outline-primary btn-sm open-full-image-btn"
                                data-target="update-preview-image">
                                <i class="bx bx-show me-1"></i>{{ get_label('view_full_image', 'View Full Image') }}
                            </button>
                            <input type="hidden" name="remove_picture" id="update_remove_picture" value="0" />
                            <button type="button" value="remove_picture"
                                class="btn btn-outline-danger btn-sm remove-image-btn" data-modal="update">
                                <i class="bx bx-trash me-1"></i>{{ get_label('remove_image', 'Remove Image') }}
                            </button>
                        </div>
                        <input type="file" class="form-control form-control-sm asset-picture-input"
                            id="update-asset-picture" name="picture" data-modal="update"
                            accept=".jpg,.jpeg,.png,.gif,.webp">
                        <div class="form-text mt-1">
                            <small class="text-muted">{{ get_label('supported_formats', 'Supported: JPG, JPEG, PNG, GIF, WebP') }}</small>
                        </div>
                    </div>
                </div>

                {{-- Asset Information --}}
                <div class="mb-4">
                    <h6 class="fw-semibold mb-3"><i class="bx bx-info-circle me-2"></i>{{ get_label('asset_information', 'Asset Information') }}</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="update-asset-name" class="form-label">{{ get_label('asset_name', 'Asset Name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="update-asset-name" name="name" required maxlength="255">
                        </div>
                        <div class="col-md-6">
                            <label for="update-asset-tag" class="form-label">{{ get_label('asset_tag', 'Asset Tag') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="update-asset-tag" name="asset_tag" required maxlength="255">
                        </div>
                        <div class="col-md-6">
                            <label for="update-asset-category" class="form-label">{{ get_label('category', 'Category') }} <span class="text-danger">*</span></label>
                            <select class="form-select tom_static_select" id="update-asset-category" name="category_id" required
                                data-placeholder="{{ get_label('select_category', 'Select Category') }}">
                                <option value=""></option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <div class="d-flex mt-2 gap-2">
                                <a href="javascript:void(0);" id="createCategoryModalBtn">
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-original-title="{{ get_label('create_category', 'Create Category') }}"><i class="bx bx-plus"></i></button>
                                </a>
                                <a href="{{ route('assets.category.index') }}">
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-original-title="{{ get_label('manage_category', 'Manage Category') }}"><i class="bx bx-list-ul"></i></button>
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6" id="update_asset_status_field">
                            <label for="update-asset-status" class="form-label">{{ get_label('status', 'Status') }} <span class="text-danger">*</span></label>
                            <select class="form-select tom_static_select" id="update-asset-status" name="status"
                                data-placeholder="{{ get_label('select_status', 'Select Status') }}">
                                <option value="available">{{ get_label('available', 'Available') }}</option>
                                <option value="non-functional">{{ get_label('non_functional', 'Non-Functional') }}</option>
                                <option value="lost">{{ get_label('lost', 'Lost') }}</option>
                                <option value="damaged">{{ get_label('damaged', 'Damaged') }}</option>
                                <option value="under-maintenance">{{ get_label('under_maintenance', 'Under Maintenance') }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Purchase Details --}}
                <div class="mb-4">
                    <h6 class="fw-semibold mb-3"><i class="bx bx-purchase-tag me-2"></i>{{ get_label('purchase_details', 'Purchase Details') }}</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="update-asset-purchase-date" class="form-label">{{ get_label('purchase_date', 'Purchase Date') }}</label>
                            <input type="date" class="form-control" id="update-asset-purchase-date" name="purchase_date">
                        </div>
                        <div class="col-md-6">
                            <label for="update-asset-purchase-cost" class="form-label">{{ get_label('purchase_cost', 'Purchase Cost') }}</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">{{ $general_settings['currency_symbol'] }}</span>
                                <input type="number" step="0.01" class="form-control" id="update-asset-purchase-cost" name="purchase_cost">
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="update-asset-description" class="form-label">{{ get_label('description', 'Description') }}</label>
                            <textarea class="form-control" id="update-asset-description" name="description" rows="4"></textarea>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="d-flex justify-content-end mt-3 gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">{{ get_label('close', 'Close') }}</button>
                    <button type="submit" id="update-submit-btn" class="btn btn-primary">{{ get_label('update', 'Update') }}</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Lightbox Modal -->
    <div class="modal fade" id="imageLightboxModal" tabindex="-1" aria-labelledby="imageLightboxModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-dark">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-white" id="imageLightboxModalLabel">
                        {{ get_label('asset_image', 'Asset Image') }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="{{ get_label('close', 'Close') }}"></button>
                </div>
                <div class="modal-body p-0 text-center">
                    <img src="" alt="{{ get_label('full_size_image', 'Full Size Image') }}"
                        id="lightboxImage" class="img-fluid rounded">
                </div>
            </div>
        </div>
    </div>
@endif


<!-- Bulk Assign Assets Offcanvas -->
<div class="offcanvas offcanvas-end offcanvas-responsive" tabindex="-1" id="bulkAssignOffcanvas"
    aria-labelledby="bulkAssignOffcanvasLabel">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="bulkAssignOffcanvasLabel">
            <i class="bx bx-user-check me-2"></i>{{ get_label('bulk_assign_asset', 'Bulk Assign Assets') }}
        </h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
            aria-label="{{ get_label('close', 'Close') }}"></button>
    </div>
    <div class="offcanvas-body p-4">
        <form id="bulkAssignForm" class="asset-form-submit-event" method="POST"
            action="{{ route('assets.bulk-assign') }}">
            @csrf
            <input type="hidden" name="dnr" />
            <input type="hidden" name="table" value="table" />

            {{-- Assignment Details --}}
            <div class="mb-4">
                <h6 class="fw-semibold mb-3">
                    <i class="bx bx-detail me-2"></i>{{ get_label('assignment_details', 'Assignment Details') }}
                </h6>
                <div class="row g-3">
                    {{-- Assign To User --}}
                    <div class="col-12">
                        <label for="create-asset-assign-to" class="form-label">
                            {{ get_label('assign_to_user', 'Assign To User') }} <span class="text-danger">*</span>
                        </label>
                        <select class="form-select tom_static_select" id="create-asset-assign-to"
                            name="assigned_to"
                            data-placeholder="{{ get_label('select_user', 'Select User') }}" required>
                            <option value=""></option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->first_name }} {{ $user->last_name }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">
                            <small class="text-muted">{{ get_label('select_user_to_assign_assets', 'Select the user who will receive the assets') }}</small>
                        </div>
                    </div>

                    {{-- Select Assets --}}
                    <div class="col-12">
                        <label class="form-label">
                            {{ get_label('select_assets', 'Select Assets') }} <span class="text-danger">*</span>
                        </label>
                        <div id="available-assets-list">
                            <select class="form-select tom_static_select" name="asset_ids[]"
                                data-placeholder="{{ get_label('select_assets', 'Select Assets') }}"
                                multiple required>
                                @foreach ($assets as $asset)
                                    @if ($asset->status === 'available')
                                        <option value="{{ $asset->id }}">{{ $asset->name }} ({{ $asset->asset_tag }})</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="form-text">
                            <small class="text-muted">{{ get_label('only_available_assets_are_shown', 'Only available assets are shown') }}</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Notes --}}
            <div class="mb-4">
                <h6 class="fw-semibold mb-3">
                    <i class="bx bx-note me-2"></i>{{ get_label('notes', 'Notes') }}
                </h6>
                <textarea class="form-control" id="bulk-assign-notes" name="notes" rows="4"
                    placeholder="{{ get_label('enter_assignment_notes', 'Enter assignment notes...') }}"></textarea>
                <div class="form-text">
                    <small class="text-muted">{{ get_label('optional_assignment_notes', 'Optional notes about this assignment') }}</small>
                </div>
            </div>

            {{-- Footer --}}
            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">
                    {{ get_label('cancel', 'Cancel') }}
                </button>
                <button type="submit" id="bulk-assign-submit" class="btn btn-primary">
                    <i class="bx bx-check me-1"></i>{{ get_label('assign_assets', 'Assign Assets') }}
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Create Category Modal -->
<div class="modal fade" id="createCategoryModal" tabindex="-1" aria-labelledby="createCategoryModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="createCategoryForm" class="form-submit-event" method="POST"
                action="{{ route('assets.category.store') }}">
                @csrf
                <input type="hidden" name="dnr">
                <div class="modal-header">
                    <h5 class="modal-title" id="createCategoryModalLabel">
                        {{ get_label('create_asset_category', 'Create Asset Category') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="category-name"
                            class="form-label">{{ get_label('category_name', 'Category Name') }}</label><span
                            class="text-danger">*</span>
                        <input type="text" name="name" class="form-control" id="category-name" required>
                    </div>
                    <div class="mb-3">
                        <label for="nameBasic" class="form-label"><?= get_label('color', 'Color') ?> <span
                                class="asterisk">*</span></label>
                        <select class="form-select select-bg-label-primary" id="color" name="color">
                            <option class="badge bg-label-primary" value="primary"
                                {{ old('color') == 'primary' ? 'selected' : '' }}>
                                <?= get_label('primary', 'Primary') ?>
                            </option>
                            <option class="badge bg-label-secondary" value="secondary"
                                {{ old('color') == 'secondary' ? 'selected' : '' }}>
                                <?= get_label('secondary', 'Secondary') ?></option>
                            <option class="badge bg-label-success" value="success"
                                {{ old('color') == 'success' ? 'selected' : '' }}>
                                <?= get_label('success', 'Success') ?></option>
                            <option class="badge bg-label-danger" value="danger"
                                {{ old('color') == 'danger' ? 'selected' : '' }}>
                                <?= get_label('danger', 'Danger') ?></option>
                            <option class="badge bg-label-warning" value="warning"
                                {{ old('color') == 'warning' ? 'selected' : '' }}>
                                <?= get_label('warning', 'Warning') ?></option>
                            <option class="badge bg-label-info" value="info"
                                {{ old('color') == 'info' ? 'selected' : '' }}><?= get_label('info', 'Info') ?>
                            </option>
                            <option class="badge bg-label-dark" value="dark"
                                {{ old('color') == 'dark' ? 'selected' : '' }}><?= get_label('dark', 'Dark') ?>
                            </option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="category-description"
                            class="form-label">{{ get_label('description', 'Description') }} (optional)</label>
                        <textarea name="description" class="form-control" id="category-description" rows="3"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">{{ get_label('close', 'Close') }}</button>
                    <button type="submit" id="submit_btn"
                        class="btn btn-primary">{{ get_label('create', 'Create') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Create Category Offcanvas -->
<div class="offcanvas offcanvas-end w-50" tabindex="-1" id="createCategoryOffcanvas"
    aria-labelledby="createCategoryOffcanvasLabel" style="max-width: calc(100% - 250px);">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="createCategoryOffcanvasLabel">
            {{ get_label('create_asset_category', 'Create Asset Category') }}
        </h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
            aria-label="{{ get_label('close', 'Close') }}"></button>
    </div>
    <div class="offcanvas-body p-4">
        <form id="createCategoryForm" class="asset-form-submit-event" method="POST"
            action="{{ route('assets.category.store') }}">
            @csrf
            <input type="hidden" name="dnr">
            <div class="row g-4">
                <div class="col-12">
                    <div class="mb-3">
                        <label for="category-name" class="form-label fw-semibold">
                            {{ get_label('category_name', 'Category Name') }} <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" class="form-control" id="category-name" required>
                    </div>
                    <div class="mb-3">
                        <label for="color" class="form-label fw-semibold">
                            {{ get_label('color', 'Color') }} <span class="text-danger">*</span>
                        </label>
                        <select class="form-select tom_static_select" id="color" name="color">
                            <option value="primary" {{ old('color') == 'primary' ? 'selected' : '' }}>
                                {{ get_label('primary', 'Primary') }}
                            </option>
                            <option value="secondary" {{ old('color') == 'secondary' ? 'selected' : '' }}>
                                {{ get_label('secondary', 'Secondary') }}
                            </option>
                            <option value="success" {{ old('color') == 'success' ? 'selected' : '' }}>
                                {{ get_label('success', 'Success') }}
                            </option>
                            <option value="danger" {{ old('color') == 'danger' ? 'selected' : '' }}>
                                {{ get_label('danger', 'Danger') }}
                            </option>
                            <option value="warning" {{ old('color') == 'warning' ? 'selected' : '' }}>
                                {{ get_label('warning', 'Warning') }}
                            </option>
                            <option value="info" {{ old('color') == 'info' ? 'selected' : '' }}>
                                {{ get_label('info', 'Info') }}
                            </option>
                            <option value="dark" {{ old('color') == 'dark' ? 'selected' : '' }}>
                                {{ get_label('dark', 'Dark') }}
                            </option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="category-description" class="form-label fw-semibold">
                            {{ get_label('description', 'Description') }} (optional)
                        </label>
                        <textarea name="description" class="form-control" id="category-description" rows="3"></textarea>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-end mt-4 gap-2">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">
                    {{ get_label('close', 'Close') }}
                </button>
                <button type="submit" id="submit_btn" class="btn btn-primary">
                    {{ get_label('create', 'Create') }}
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Update Category Offcanvas -->
<div class="offcanvas offcanvas-end w-50" tabindex="-1" id="updateCategoryOffcanvas"
    aria-labelledby="updateCategoryOffcanvasLabel" style="max-width: calc(100% - 250px);">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="updateCategoryOffcanvasLabel">
            {{ get_label('update_asset_category', 'Update Asset Category') }}
        </h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
            aria-label="{{ get_label('close', 'Close') }}"></button>
    </div>
    <div class="offcanvas-body p-4">
        <form id="updateCategoryForm" class="asset-form-submit-event" method="POST" action="">
            @csrf
            <input type="hidden" name="dnr">
            <div class="row g-4">
                <div class="col-12">
                    <div class="mb-3">
                        <label for="categoryName" class="form-label fw-semibold">
                            {{ get_label('category_name', 'Category Name') }} <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" class="form-control" id="categoryName" required>
                    </div>
                    <div class="mb-3">
                        <label for="category_color" class="form-label fw-semibold">
                            {{ get_label('color', 'Color') }} <span class="text-danger">*</span>
                        </label>
                        <select class="form-select tom_static_select" id="category_color" name="color">
                            <option value="primary" {{ old('color') == 'primary' ? 'selected' : '' }}>
                                {{ get_label('primary', 'Primary') }}
                            </option>
                            <option value="secondary" {{ old('color') == 'secondary' ? 'selected' : '' }}>
                                {{ get_label('secondary', 'Secondary') }}
                            </option>
                            <option value="success" {{ old('color') == 'success' ? 'selected' : '' }}>
                                {{ get_label('success', 'Success') }}
                            </option>
                            <option value="danger" {{ old('color') == 'danger' ? 'selected' : '' }}>
                                {{ get_label('danger', 'Danger') }}
                            </option>
                            <option value="warning" {{ old('color') == 'warning' ? 'selected' : '' }}>
                                {{ get_label('warning', 'Warning') }}
                            </option>
                            <option value="info" {{ old('color') == 'info' ? 'selected' : '' }}>
                                {{ get_label('info', 'Info') }}
                            </option>
                            <option value="dark" {{ old('color') == 'dark' ? 'selected' : '' }}>
                                {{ get_label('dark', 'Dark') }}
                            </option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="categoryDescription" class="form-label fw-semibold">
                            {{ get_label('description', 'Description') }} (optional)
                        </label>
                        <textarea name="description" class="form-control" id="categoryDescription" rows="3"></textarea>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-end mt-4 gap-2">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">
                    {{ get_label('close', 'Close') }}
                </button>
                <button type="submit" id="submit_btn" class="btn btn-primary">
                    {{ get_label('update', 'Update') }}
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="duplicateAssetModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="exampleModalLabel2"><?= get_label('warning', 'Warning!') ?></h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="duplicateForm" class="form-submit-event" method="POST">
                @csrf
                <input type="hidden" name="dnr" />
                <input type="hidden" name="table" value="table" />
                <input type="hidden" id="editStatusId" />
                <div class="modal-body">
                    <p><?= get_label('duplicate_warning', 'Are you sure you want to duplicate?') ?></p>
                    <div id="titleDiv">
                        <label class="form-label"><?= get_label('update_asset_tag', 'Update Asset Tag') ?></label>
                        <input type="text" class="form-control" id="updateTitle" name="asset_tag"
                            placeholder="<?= get_label('enter_asset_tag_duplicate', 'Enter Asset Tag For Item Being Duplicated') ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <?= get_label('close', 'Close') ?>
                    </button>
                    <button type="submit" id="submit_btn" class="btn btn-primary"
                        id="confirmDuplicate"><?= get_label('yes', 'Yes') ?></button>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- Bulk Upload Assets Offcanvas -->
<div class="offcanvas offcanvas-end offcanvas-responsive" tabindex="-1" id="bulkAssetsUploadOffcanvas"
    aria-labelledby="bulkAssetsUploadOffcanvasLabel">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="bulkAssetsUploadOffcanvasLabel">
            <i class="bx bx-upload me-2"></i>{{ get_label('import_assets_via_excel', 'Import Assets via Excel') }}
        </h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
            aria-label="{{ get_label('close', 'Close') }}"></button>
    </div>
    <div class="offcanvas-body p-4">
        <form action="{{ route('assets.import') }}" class="form-submit-event" method="POST"
            enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="dnr" />
            <input type="hidden" name="table" value="table" />
            <input type="hidden" id="editStatusId" />

            {{-- File Upload Section --}}
            <div class="mb-4">
                <h6 class="fw-semibold mb-3">
                    <i class="bx bx-file-import me-2"></i>{{ get_label('upload_excel_file', 'Upload Excel File') }}
                </h6>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bx bx-file"></i>
                    </span>
                    <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                </div>
                <div class="form-text mt-1">
                    <small class="text-muted">{{ get_label('accepted_formats', 'Accepted formats: XLSX, XLS, CSV') }}</small>
                </div>
            </div>

            {{-- Resources Section --}}
            <div class="mb-4">
                <h6 class="fw-semibold mb-3">
                    <i class="bx bx-download me-2"></i>{{ get_label('resources', 'Resources') }}
                </h6>
                <div class="d-grid gap-2">
                    <a href="{{ asset('storage/files/sample_assets.xlsx') }}" target="_blank"
                        class="btn btn-outline-secondary">
                        <i class="bx bx-download me-2"></i>{{ get_label('download_sample_file', 'Download Sample File') }}
                    </a>
                    <a href="{{ asset('storage/files/instructions_assets.pdf') }}" target="_blank"
                        class="btn btn-outline-secondary">
                        <i class="bx bx-file-pdf me-2"></i>{{ get_label('download_instruction_file', 'Download Instruction File') }}
                    </a>
                </div>
                <div class="form-text mt-2">
                    <small class="text-muted">{{ get_label('download_the_sample_file_to_see_the_required_format_and_the_instruction_file_for_detailed_guidance', 'Download the sample file to see the required format, and the instruction file for detailed guidance.') }}</small>
                </div>
            </div>

            {{-- Import Instructions Card --}}
            <div class="card border shadow-none mb-4">
                <div class="card-body p-3">
                    <h6 class="fw-semibold mb-2">
                        <i class="bx bx-info-circle me-2 text-info"></i>{{ get_label('import_instruction', 'Import Instructions') }}
                    </h6>
                    <ul class="small text-muted mb-0 ps-3">
                        <li>{{ get_label('use_the_sample_file_as_a_template_for_your_data', 'Use the sample file as a template for your data') }}</li>
                        <li>{{ get_label('ensure_all_required_fields_are_filled', 'Ensure all required fields are filled') }}</li>
                        <li>{{ get_label('category_names_should_match_existing_categories', 'Category names should match existing categories') }}</li>
                        <li>{{ get_label('asset_tags_must_be_unique', 'Asset tags must be unique') }}</li>
                    </ul>
                </div>
            </div>

            {{-- AJAX Validation Errors Container --}}
            <div id="uploadErrors" class="alert alert-danger d-none mt-3">
                <h6 class="alert-heading">{{ get_label('upload_errors', 'Upload Errors:') }}</h6>
                <ul class="mb-0" id="uploadErrorsList"></ul>
            </div>

            {{-- Server-side Validation Errors --}}
            @if ($errors->any())
                <div class="alert alert-danger mt-3">
                    <h6 class="alert-heading">{{ get_label('validation_errors', 'Validation Errors:') }}</h6>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Footer --}}
            <div class="d-flex justify-content-end gap-2 mt-3">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">
                    {{ get_label('cancel', 'Cancel') }}
                </button>
                <button type="submit" id="submit_btn" class="btn btn-primary">
                    <i class="bx bx-upload me-1"></i>{{ get_label('import', 'Import') }}
                </button>
            </div>
        </form>
    </div>
</div>
