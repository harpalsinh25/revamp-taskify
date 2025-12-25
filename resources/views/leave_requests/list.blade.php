@extends('layout')
@section('title')
    <?= get_label('leave_requests', 'Leave requests') ?>
@endsection
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb-2 mt-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ url('home') }}"><?= get_label('home', 'Home') ?></a>
                        </li>
                        <li class="breadcrumb-item active">
                            <?= get_label('leave_requests', 'Leave requests') ?>
                        </li>
                    </ol>
                </nav>
            </div>
            <div>
                @php
                    $meetingsDefaultView = getUserPreferences('leave_requests', 'default_view');
                @endphp
                @if ($meetingsDefaultView === 'list')
                    <span class="badge bg-primary"><?= get_label('default_view', 'Default View') ?></span>
                @else
                    <a href="javascript:void(0);"><span class="badge bg-secondary" id="set-default-view"
                            data-type="leave-requests"
                            data-view="list"><?= get_label('set_as_default_view', 'Set as Default View') ?></span></a>
                @endif
            </div>
            <div class="d-flex align-items-center gap-1">
                @if ($auth_user->hasRole('admin') || is_admin_or_leave_editor())
                    <a href="{{ url('leave-balances') }}" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip"
                        data-bs-placement="bottom" data-bs-original-title="<?= get_label('view_leave_balances', 'View leave balance dashboard') ?>">
                        <i class='bx bx-bar-chart'></i>
                    </a>
                @endif
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal"
                    data-bs-target="#paidLeaveWorkflowModal"
                    title="<?= get_label('view_paid_leave_flow', 'View paid leave flow') ?>">
                    <i class='bx bx-info-circle'></i>
                </button>
                <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#create_leave_request_modal"><button
                        type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="right"
                        data-bs-original-title=" <?= get_label('create_leave_request', 'Create leave request') ?>"><i
                            class="bx bx-plus"></i></button></a>
                <a href="{{ route('leave-requests.calendar') }}"><button type="button" class="btn btn-sm btn-primary"
                        data-bs-toggle="tooltip" data-bs-placement="left"
                        data-bs-original-title="<?= get_label('calendar_view', 'Calendar view') ?>"><i
                            class='bx bx-calendar'></i></button></a>
            </div>
        </div>
        @php
            $isLeaveEditor = \App\Models\LeaveEditor::where('user_id', $auth_user->id)->exists();
            $leaveBalanceService = new \App\Services\LeaveBalanceService();
            $leaveBalance = $leaveBalanceService->getBalanceSummary($auth_user->id, getWorkspaceId());
            $companyYearText = format_company_year(null, true); // e.g., "Apr 2024 - Mar 2025"
        @endphp

        @php
            $formatLeaveNumber = static function ($value) {
                return rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
            };
        @endphp

        <!-- Leave Balance Widget -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card leave-balance-card shadow-sm border-0">
                    <div class="card-body">
                        <div class="leave-balance-header mb-4">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <h5 class="card-title mb-0 d-flex align-items-center gap-2"><i class='bx bx-wallet'></i><?= get_label('my_leave_balance', 'My Leave Balance') ?></h5>
                                <span class="badge bg-label-info rounded-pill px-3 py-2 fw-semibold">{{ $companyYearText }}</span>
                            </div>
                            <p class="text-muted mb-0 small">{{ get_label('leave_balance_subheadline', 'Track your annual paid leave at a glance.') }}</p>
                        </div>

                        <div class="row g-3 leave-balance-metrics">
                            <div class="col-12 col-sm-6 col-xl">
                                <div class="border-2 card h-100 shadow-none">
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div class="flex-grow-1">
                                                <p class="text-muted text-uppercase small mb-2 fw-semibold"><?= get_label('total_annual_leaves', 'Total Annual Leaves') ?></p>
                                                <h3 class="mb-0 fw-bold">{{ $formatLeaveNumber($leaveBalance['total_annual_leaves']) }}</h3>
                                                @if(isset($leaveBalance['accrued_leaves']))
                                                    <p class="text-info small mb-0 mt-2"><?= get_label('accrued', 'Accrued') ?>: {{ $formatLeaveNumber($leaveBalance['accrued_leaves']) }}</p>
                                                @endif
                                            </div>
                                            <div class="avatar flex-shrink-0">
                                                <span class="avatar-initial rounded bg-label-primary">
                                                    <i class='bx bx-calendar-check fs-3 text-primary'></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-sm-6 col-xl">
                                <div class="border-2 card h-100 shadow-none">
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div class="flex-grow-1">
                                                <p class="text-muted text-uppercase small mb-2 fw-semibold"><?= get_label('used_paid_leaves', 'Used Paid Leaves') ?></p>
                                                <h3 class="mb-0 fw-bold d-flex align-items-baseline gap-1">
                                                    <span>{{ $formatLeaveNumber($leaveBalance['used_paid_leaves']) }}</span>
                                                    <small class="text-muted fs-5 fw-normal">/ {{ $formatLeaveNumber($leaveBalance['total_annual_leaves']) }}</small>
                                                </h3>
                                                @if(isset($leaveBalance['accrued_leaves']))
                                                    <p class="text-muted small mb-0 mt-2"><?= get_label('accrued_to_date', 'Accrued to date') ?>: {{ $formatLeaveNumber($leaveBalance['accrued_leaves']) }}</p>
                                                @endif
                                                @if(isset($leaveBalance['advanced_paid_leaves']) && $leaveBalance['advanced_paid_leaves'] > 0)
                                                    <p class="text-info small mb-0 mt-1">
                                                        <i class="bx bx-info-circle"></i>
                                                        <?= get_label('includes_advance', 'Includes') ?> {{ $formatLeaveNumber($leaveBalance['advanced_paid_leaves']) }} <?= get_label('advanced_paid_leaves', 'Advanced Paid Leaves') ?>
                                                    </p>
                                                @endif
                                            </div>
                                            <div class="avatar flex-shrink-0">
                                                <span class="avatar-initial rounded bg-label-warning">
                                                    <i class='bx bx-time-five fs-3 text-warning'></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-sm-6 col-xl">
                                @php
                                    $remainingPaidLeaves = $leaveBalance['remaining_paid_leaves'] ?? 0;
                                    $totalAnnualLeaves = $leaveBalance['total_annual_leaves'] ?? 0;
                                    $accruedLeaves = $leaveBalance['accrued_leaves'] ?? null;
                                    $advancedPaidLeaves = $leaveBalance['advanced_paid_leaves'] ?? 0;
                                    // Use display_remaining_paid_leaves if available (can be negative), otherwise calculate
                                    $displayRemaining = $leaveBalance['display_remaining_paid_leaves'] ?? ($remainingPaidLeaves - $advancedPaidLeaves);
                                    $annualRemaining = max($totalAnnualLeaves - ($leaveBalance['used_paid_leaves'] ?? 0), 0);
                                @endphp
                                <div class="border-2 card h-100 shadow-none">
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div class="flex-grow-1">
                                                <x-leave.remaining-leaves-pill
                                                    :remaining="$displayRemaining"
                                                    :total="$totalAnnualLeaves"
                                                    :accrued="$accruedLeaves"
                                                    :advanced_paid_leaves="$advancedPaidLeaves"
                                                    :annual="$totalAnnualLeaves"
                                                    :annual-remaining="$annualRemaining"
                                                    heading="{{ get_label('remaining_paid_leaves', 'Remaining Paid Leaves') }}"
                                                />
                                            </div>
                                            <div class="avatar flex-shrink-0">
                                                <span class="avatar-initial rounded bg-label-success">
                                                    <i class='bx bx-check-circle fs-3 text-success'></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-sm-6 col-xl">
                                <div class="border-2 card h-100 shadow-none">
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div class="flex-grow-1">
                                                <p class="text-muted text-uppercase small mb-2 fw-semibold"><?= get_label('unpaid_leaves_taken', 'Unpaid Leaves Taken') ?></p>
                                                <h3 class="mb-0 fw-bold">{{ $formatLeaveNumber($leaveBalance['unpaid_leaves_taken']) }}</h3>
                                            </div>
                                            <div class="avatar flex-shrink-0">
                                                <span class="avatar-initial rounded bg-label-danger">
                                                    <i class='bx bx-x-circle fs-3 text-danger'></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-sm-6 col-xl">
                                @php
                                    $totalLeavesTaken = ($leaveBalance['used_paid_leaves'] ?? 0) + ($leaveBalance['unpaid_leaves_taken'] ?? 0);
                                @endphp
                                <div class="border-2 card h-100 shadow-none">
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div class="flex-grow-1">
                                                <p class="text-muted text-uppercase small mb-2 fw-semibold"><?= get_label('total_leaves_taken', 'Total Leaves Taken') ?></p>
                                                <h3 class="mb-0 fw-bold d-flex align-items-baseline gap-1">
                                                    <span>{{ $formatLeaveNumber($totalLeavesTaken) }}</span>
                                                </h3>
                                                <p class="text-muted small mb-0 mt-2">
                                                    <?= get_label('paid', 'Paid') ?>: {{ $formatLeaveNumber($leaveBalance['used_paid_leaves'] ?? 0) }} ·
                                                    <?= get_label('unpaid', 'Unpaid') ?>: {{ $formatLeaveNumber($leaveBalance['unpaid_leaves_taken'] ?? 0) }}
                                                </p>
                                            </div>
                                            <div class="avatar flex-shrink-0">
                                                <span class="avatar-initial rounded bg-label-secondary">
                                                    <i class='bx bx-calendar fs-3 text-secondary'></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if(isset($leaveBalance['monthly_accrual_rate']))
                            <div class="alert alert-info leave-accrual-banner d-flex align-items-start gap-3 mt-4" role="alert">
                                <div class="leave-accrual-icon text-info">
                                    <i class='bx bx-info-circle'></i>
                                </div>
                                <div>
                                    <h6 class="alert-title mb-1"><?= get_label('monthly_accrual_info', 'Monthly Accrual System') ?></h6>
                                    <p class="mb-0 small text-info">
                                        <?= get_label('you_earn', 'You earn') ?> <strong>{{ $leaveBalance['monthly_accrual_rate'] }}</strong> <?= get_label('days_per_month', 'days per month') ?>.
                                        <?= get_label('worked_months', 'Worked') ?>: <strong>{{ $leaveBalance['months_worked'] }} <?= get_label('months', 'months') ?></strong>.
                                        <?= get_label('accrued_so_far', 'Accrued so far') ?>: <strong>{{ $formatLeaveNumber($leaveBalance['accrued_leaves']) }} <?= get_label('days', 'days') ?></strong>.
                                    </p>
                                </div>
                            </div>
                        @endif

                        <div class="mt-4">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-muted small">{{ get_label('leave_utilization', 'Leave utilization') }}</span>
                                <span class="text-muted small fw-semibold">{{ number_format($leaveBalance['utilization_percentage'], 1) }}%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-primary" role="progressbar"
                                    style="width: {{ number_format($leaveBalance['utilization_percentage'], 1) }}%;"
                                    aria-valuenow="{{ $leaveBalance['utilization_percentage'] }}"
                                    aria-valuemin="0"
                                    aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            @if ($auth_user->hasRole('admin'))
                <form action="{{ url('leave-requests/update-editors') }}" class="form-submit-event" method="POST">
                    <input type="hidden" name="redirect_url" value="{{ url('leave-requests') }}">
                    <div class="d-flex justify-content-center">
                        <div class="col-8 mx-auto mb-3">
                            <label class="form-label"
                                for="user_id"><?= get_label('select_leave_editors', 'Select leave editors') ?> <i
                                    class='bx bx-info-circle text-primary' data-bs-toggle="tooltip" data-bs-offset="0,4"
                                    data-bs-placement="top" title=""
                                    data-bs-original-title="{{ get_label('leave_editor_access_info', 'Like Admin, Selected Users Will Be Able to Update and Create Leaves for Other Members.') }}"></i></label>
                            <select id="" class="form-control users_select" name="user_ids[]" multiple="multiple"
                                data-placeholder="<?= get_label('type_to_search', 'Type to search') ?>"
                                data-ignore-admins="true">
                                @foreach ($leaveEditors as $leaveEditor)
                                    <option value="{{ $leaveEditor->id }}" selected>{{ $leaveEditor->first_name }}
                                        {{ $leaveEditor->last_name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="d-flex justify-content-center">
                                <button type="submit" id="submit_btn"
                                    class="btn btn-primary my-2"><?= get_label('update', 'Update') ?></button>
                            </div>
                        </div>
                    </div>
                </form>
            @endif
            @if ($isLeaveEditor)
                <div class="d-flex justify-content-center mb-3">
                    <span class="badge bg-primary"><?= get_label('leave_editor_info', 'You are leave editor') ?></span>
                </div>
            @endif
        </div>
        @if ($leave_requests > 0)
            @php
                $visibleColumns = getUserPreferences('leave_requests');
            @endphp
            <div class="card">
                <div class="card-body">
                    <div class="row">
<<<<<<< HEAD
                        <div class="col-md-4 mb-3 d-flex align-items-center gap-2">
                            <div class="input-group input-group-merge">
                                <input type="text" class="form-control" id="lr_date_between"
                                    placeholder="<?= get_label('date_between', 'Date Between') ?>" autocomplete="off">
                            </div>

                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="input-group input-group-merge">
                                <input type="text" id="lr_start_date_between" class="form-control"
                                    placeholder="<?= get_label('from_date_between', 'From date between') ?>"
                                    autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="input-group input-group-merge">
                                <input type="text" id="lr_end_date_between" class="form-control"
                                    placeholder="<?= get_label('to_date_between', 'To date between') ?>" autocomplete="off">
                            </div>
                        </div>
=======
                        <x-advanced-date-filters prefix="lr" />
>>>>>>> 144e56db9f7d21936e8433596f818ef2d9bfc72e
                        @if (is_admin_or_leave_editor())
                            <div class="col-md-4 mb-3">
                                <select class="form-select users_select" id="lr_user_filter"
                                    aria-label="Default select example"
                                    data-placeholder="<?= get_label('select_users', 'Select Users') ?>" multiple>
                                </select>
                            </div>
                        @endif
                        <div class="col-md-4 mb-3">
                            <select class="form-select users_select" id="lr_action_by_filter"
                                aria-label="Default select example"
                                data-placeholder="<?= get_label('select_actions_by', 'Select Actions By') ?>" multiple>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <select class="form-select js-example-basic-multiple" id="lr_status_filter"
                                aria-label="Default select example"
                                data-placeholder="<?= get_label('select_statuses', 'Select statuses') ?>"
                                data-allow-clear="true" multiple>
                                <option value="pending"><?= get_label('pending', 'Pending') ?></option>
                                <option value="approved"><?= get_label('approved', 'Approved') ?></option>
                                <option value="rejected"><?= get_label('rejected', 'Rejected') ?></option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <select class="form-select js-example-basic-multiple" id="lr_type_filter"
                                aria-label="Default select example"
                                data-placeholder="<?= get_label('select_types', 'Select Types') ?>"
                                data-allow-clear="true" multiple>
                                <option value="full"><?= get_label('full', 'Full') ?></option>
                                <option value="partial"><?= get_label('partial', 'Partial') ?></option>
                            </select>
                        </div>
                    </div>
<<<<<<< HEAD
                    <input type="hidden" name="lr_date_between_from" id="lr_date_between_from">
                    <input type="hidden" name="lr_date_between_to" id="lr_date_between_to">
                    <input type="hidden" name="lr_start_date_from" id="lr_start_date_from">
                    <input type="hidden" name="lr_start_date_to" id="lr_start_date_to">
                    <input type="hidden" name="lr_end_date_from" id="lr_end_date_from">
                    <input type="hidden" name="lr_end_date_to" id="lr_end_date_to">
=======
>>>>>>> 144e56db9f7d21936e8433596f818ef2d9bfc72e
                    <div class="table-responsive text-nowrap">
                        <input type="hidden" id="data_type" value="leave-requests">
                        <input type="hidden" id="data_table" value="lr_table">
                        <input type="hidden" id="save_column_visibility">
                        <input type="hidden" id="multi_select">
                        <table id="lr_table" data-toggle="table" data-loading-template="loadingTemplate"
                            data-url="{{ url('/leave-requests/list') }}" data-icons-prefix="bx" data-icons="icons"
                            data-show-refresh="true" data-total-field="total" data-trim-on-search="false"
                            data-data-field="rows" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true"
                            data-side-pagination="server" data-show-columns="true" data-pagination="true"
                            data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true"
                            data-query-params="queryParamsLr">
                            <thead>
                                <tr>
                                    <th data-checkbox="true"></th>
                                    <th data-field="id"
                                        data-visible="{{ in_array('id', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true"><?= get_label('id', 'ID') ?></th>
                                    <th data-field="user_name"
                                        data-visible="{{ in_array('user_name', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="false"><?= get_label('member', 'Member') ?></th>
                                    <th data-field="from_date"
                                        data-visible="{{ in_array('from_date', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true"><?= get_label('from', 'From') ?></th>
                                    <th data-field="to_date"
                                        data-visible="{{ in_array('to_date', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true"><?= get_label('to', 'To') ?></th>
                                    <th data-field="type"
                                        data-visible="{{ in_array('type', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}">
                                        <?= get_label('type', 'Type') ?>
                                    </th>
                                    <th data-field="duration"
                                        data-visible="{{ in_array('duration', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="false"><?= get_label('duration', 'Duration') ?></th>
                                    <th data-field="reason"
                                        data-visible="{{ in_array('reason', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true"><?= get_label('reason', 'Reason') ?></th>
                                    <th data-field="visible_to"
                                        data-visible="{{ in_array('visible_to', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}">
                                        <?= get_label('visible_to', 'Visible To') ?><i
                                            class='bx bx-info-circle text-primary'
                                            title="{{ get_label('leave_visible_to_info_1', 'Including the requestee, admin, and leave editors, users who will be able to know when the requestee is on leave (not applicable if visible to all).') }}"></i>
                                    </th>
                                    <th data-field="status"
                                        data-visible="{{ in_array('status', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true"><?= get_label('status', 'Status') ?></th>
                                    <th data-field="action_by"
                                        data-visible="{{ in_array('action_by', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true"><?= get_label('action_by', 'Action by') ?></th>
                                    @if (is_admin_or_leave_editor())
                                        <th data-field="comment"
                                            data-visible="{{ in_array('comment', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}">
                                            <?= get_label('comment', 'Comment') ?>
                                        </th>
                                    @endif
                                    <th data-field="created_at"
                                        data-visible="{{ in_array('created_at', $visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true"><?= get_label('created_at', 'Created at') ?></th>
                                    <th data-field="updated_at"
                                        data-visible="{{ in_array('updated_at', $visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true"><?= get_label('updated_at', 'Updated at') ?></th>
                                    <th data-field="actions"
                                        data-visible="{{ in_array('actions', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}">
                                        <?= get_label('actions', 'Actions') ?>
                                    </th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <?php
            $type = 'Leave requests'; ?>
            <x-empty-state-card :type="$type" />
        @endif
    </div>
@endsection

@section('page_scripts')
    <script>
        var label_update = '<?= get_label('update', 'Update') ?>';
        var label_delete = '<?= get_label('delete', 'Delete') ?>';
        var isAdminOrLe = '<?= is_admin_or_leave_editor() ?>';
        var authUserId = {{ $auth_user->id }}; // Logged-in user ID for balance fetching
    </script>
    <script src="{{ asset('assets/js/pages/leave-requests.js') }}"></script>
@endsection
