@props([
    'calendarId' => 'calendarDiv',
    'createButtonText' => null,
    'createModalTarget' => '#create_modal',
    'createOffcanvasTarget' => null,
    'entityType' => 'items',
    'showMiniCalendar' => false,
    'sidebarTitle' => null,
    'showStatusFilters' => false,
    'showPriorityFilters' => false,
    'sidebarContent' => null,
])

<div class="calendar-wrapper">
    <!-- Enhanced Sidebar -->
    <div class="calendar-sidebar">
        <!-- Sidebar Title -->
        <div class="align-items-center border-bottom d-flex justify-content-between p-1_5">
            <h5 class="fw-bold  mb-0">
                <i class="bx bx-calendar me-2"></i>
                {{ $sidebarTitle ?? get_label(ucfirst($entityType) . '_calendar', ucfirst($entityType) . ' Calendar') }}
            </h5>
        </div>
        <hr class="my-0">
        <div class="p-3">
            @if ($createButtonText)
                <!-- Add Button -->
                <button class="btn btn-primary w-100 mb-4"
                    @if ($createOffcanvasTarget) data-bs-toggle="offcanvas" data-bs-target="{{ $createOffcanvasTarget }}"
            @else
            data-bs-toggle="modal" data-bs-target="{{ $createModalTarget }}" @endif>
                    <i class="bx bx-plus me-1"></i> {{ $createButtonText }}
                </button>
            @endif

            <!-- Mini Calendar (if enabled) -->
            @if ($showMiniCalendar)
                <div class="mb-4">
                    <input type="text" id="miniCalendar" class="d-none">
                </div>
            @endif

            <!-- Status Filters (Dynamic, shown only if enabled) -->
            @if ($showStatusFilters)
                <div class="filter-section">
                    <h6><i class="bx bx-flag me-1"></i>
                        {{ $sidebarTitle ? $sidebarTitle . ' Status' : get_label('status', 'Status') }}
                    </h6>
                    <div id="status-filters-container">
                        <div class="skeleton-loader"></div>
                        <div class="skeleton-loader"></div>
                        <div class="skeleton-loader"></div>
                    </div>
                </div>
            @endif

            <!-- Priority Filters (Dynamic, shown only if enabled) -->
            @if ($showPriorityFilters)
                <div class="filter-section">
                    <h6><i class="bx bx-star me-1"></i> {{ get_label('priority', 'Priority') }}</h6>
                    <div id="priority-filters-container">
                        <div class="skeleton-loader"></div>
                        <div class="skeleton-loader"></div>
                        <div class="skeleton-loader"></div>
                    </div>
                </div>
            @endif

            {{-- @dd($sidebarContent) --}}
            <!-- Custom Sidebar Content Slot -->
            {!! $sidebarContent ?? '' !!}

            <!-- Calendar Statistics -->
            <div class="filter-section">
                <h6><i class="bx bx-bar-chart me-1"></i> {{ get_label('statistics', 'Statistics') }}</h6>
                <div class="small text-muted">
                    <div class="d-flex justify-content-between mb-1">
                        <span>{{ get_label('total_' . $entityType, 'Total') }}:</span>
                        <span id="total-{{ $entityType }}">0</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span>{{ get_label('visible', 'Visible') }}:</span>
                        <span id="visible-{{ $entityType }}">0</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>{{ get_label('filtered', 'Filtered') }}:</span>
                        <span id="filtered-{{ $entityType }}">0</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar -->
    <div class="calendar-main card p-3">
        <div id="{{ $calendarId }}"></div>
    </div>
</div>
