<?php

use App\Models\User;
use App\Models\Workspace;
use App\Models\UserLeaveBalance;
use App\Models\LeaveRequest;
use App\Models\Setting;
use App\Services\LeaveBalanceService;
use Carbon\Carbon;

beforeEach(function () {
    $this->service = new LeaveBalanceService();
    $this->user = User::factory()->create();
    $this->workspace = Workspace::factory()->create();
    $this->workspace->users()->attach($this->user->id);

    // Set default general settings
    Setting::updateOrCreate(
        ['variable' => 'general_settings'],
        ['value' => json_encode([
            'total_paid_leaves_per_year' => 15,
            'leave_accrual_type' => 'lump_sum',
            'company_year_start_month' => 1,
            'company_year_start_day' => 1,
        ])]
    );
});

test('get or create balance creates new balance if not exists', function () {
    $balance = $this->service->getOrCreateBalance($this->user->id, $this->workspace->id, 2025);

    expect($balance)->toBeInstanceOf(UserLeaveBalance::class)
        ->and($balance->user_id)->toBe($this->user->id)
        ->and($balance->workspace_id)->toBe($this->workspace->id)
        ->and($balance->year)->toBe(2025)
        ->and($balance->total_annual_leaves)->toEqual(15.0);
});

test('get or create balance returns existing balance', function () {
    $existing = UserLeaveBalance::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'year' => 2025,
        'total_annual_leaves' => 12,
    ]);

    $balance = $this->service->getOrCreateBalance($this->user->id, $this->workspace->id, 2025);

    expect($balance->id)->toBe($existing->id)
        ->and($balance->total_annual_leaves)->toEqual(12.0); // Existing value, not settings value
});

test('calculate used paid leaves sums approved paid leaves correctly', function () {
    $balance = UserLeaveBalance::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'year' => 2025,
    ]);

    // Create approved paid leaves
    LeaveRequest::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'from_date' => '2025-01-15',
        'to_date' => '2025-01-16',
        'status' => 'approved',
        'is_paid' => true,
        'paid_days' => 2,
        'unpaid_days' => 0,
    ]);

    LeaveRequest::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'from_date' => '2025-02-10',
        'to_date' => '2025-02-12',
        'status' => 'approved',
        'is_paid' => true,
        'paid_days' => 3,
        'unpaid_days' => 0,
    ]);

    $usedLeaves = $this->service->calculateUsedPaidLeaves($this->user->id, $this->workspace->id, 2025);

    expect($usedLeaves)->toEqual(5.0);
});

test('calculate used paid leaves excludes pending and rejected leaves', function () {
    $balance = UserLeaveBalance::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'year' => 2025,
    ]);

    LeaveRequest::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'from_date' => '2025-01-15',
        'status' => 'approved',
        'is_paid' => true,
        'paid_days' => 2,
    ]);

    LeaveRequest::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'from_date' => '2025-02-10',
        'status' => 'pending', // Not approved
        'is_paid' => true,
        'paid_days' => 3,
    ]);

    LeaveRequest::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'from_date' => '2025-03-10',
        'status' => 'rejected', // Rejected
        'is_paid' => true,
        'paid_days' => 1,
    ]);

    $usedLeaves = $this->service->calculateUsedPaidLeaves($this->user->id, $this->workspace->id, 2025);

    expect($usedLeaves)->toEqual(2.0); // Only approved leave
});

test('can approve as paid returns true when sufficient balance', function () {
    UserLeaveBalance::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'year' => 2025,
        'total_annual_leaves' => 15,
        'used_paid_leaves' => 5,
        'remaining_paid_leaves' => 10,
    ]);

    expect($this->service->canApproveAsPaid($this->user->id, $this->workspace->id, 5, 2025))->toBeTrue()
        ->and($this->service->canApproveAsPaid($this->user->id, $this->workspace->id, 10, 2025))->toBeTrue()
        ->and($this->service->canApproveAsPaid($this->user->id, $this->workspace->id, 11, 2025))->toBeFalse();
});

test('calculate paid unpaid days splits correctly when partial balance', function () {
    UserLeaveBalance::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'year' => 2025,
        'total_annual_leaves' => 15,
        'used_paid_leaves' => 10,
        'remaining_paid_leaves' => 5,
    ]);

    $result = $this->service->calculatePaidUnpaidDays($this->user->id, $this->workspace->id, 7, 2025);

    expect($result['paid_days'])->toEqual(5.0)
        ->and($result['unpaid_days'])->toEqual(2.0);
});

test('calculate paid unpaid days marks all paid when sufficient balance', function () {
    UserLeaveBalance::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'year' => 2025,
        'remaining_paid_leaves' => 10,
    ]);

    $result = $this->service->calculatePaidUnpaidDays($this->user->id, $this->workspace->id, 5, 2025);

    expect($result['paid_days'])->toEqual(5.0)
        ->and($result['unpaid_days'])->toBe(0.0);
});

test('calculate paid unpaid days marks all unpaid when no balance', function () {
    UserLeaveBalance::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'year' => 2025,
        'remaining_paid_leaves' => 0,
    ]);

    $result = $this->service->calculatePaidUnpaidDays($this->user->id, $this->workspace->id, 5, 2025);

    expect($result['paid_days'])->toBe(0.0)
        ->and($result['unpaid_days'])->toEqual(5.0);
});

test('update balance recalculates used and remaining from database', function () {
    $balance = UserLeaveBalance::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'year' => 2025,
        'total_annual_leaves' => 15,
        'used_paid_leaves' => 0,
        'remaining_paid_leaves' => 15,
    ]);

    // Create approved leave
    $leave = LeaveRequest::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'from_date' => '2025-01-15',
        'status' => 'approved',
        'is_paid' => true,
        'paid_days' => 3,
    ]);

    $this->service->updateBalance($this->user->id, $this->workspace->id, $leave);

    $balance->refresh();
    expect($balance->used_paid_leaves)->toBe(3.0)
        ->and($balance->remaining_paid_leaves)->toEqual(12.0);
});

test('restore balance recalculates after leave deletion', function () {
    $balance = UserLeaveBalance::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'year' => 2025,
        'total_annual_leaves' => 15,
        'used_paid_leaves' => 3,
        'remaining_paid_leaves' => 12,
    ]);

    $leave = LeaveRequest::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'from_date' => '2025-01-15',
        'status' => 'approved',
        'is_paid' => true,
        'paid_days' => 3,
    ]);

    // Now delete the leave
    $this->service->restoreBalance($this->user->id, $this->workspace->id, $leave);

    $balance->refresh();
    expect($balance->used_paid_leaves)->toBe(0.0)
        ->and($balance->remaining_paid_leaves)->toEqual(15.0);
});

test('restore balance does nothing for unpaid leaves', function () {
    $balance = UserLeaveBalance::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'year' => 2025,
        'used_paid_leaves' => 5,
        'remaining_paid_leaves' => 10,
    ]);

    $leave = LeaveRequest::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'from_date' => '2025-01-15',
        'status' => 'approved',
        'is_paid' => false,
        'paid_days' => 0,
        'unpaid_days' => 3,
    ]);

    $result = $this->service->restoreBalance($this->user->id, $this->workspace->id, $leave);

    expect($result)->toBeNull();
    $balance->refresh();
    expect($balance->used_paid_leaves)->toEqual(5.0); // Unchanged
});

test('get balance summary returns correct data', function () {
    UserLeaveBalance::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'year' => 2025,
        'total_annual_leaves' => 15,
        'used_paid_leaves' => 5,
        'remaining_paid_leaves' => 10,
    ]);

    // Create unpaid leaves
    LeaveRequest::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'from_date' => '2025-01-15',
        'status' => 'approved',
        'is_paid' => false,
        'unpaid_days' => 2,
    ]);

    $summary = $this->service->getBalanceSummary($this->user->id, $this->workspace->id, 2025);

    expect($summary)->toHaveKeys(['total_annual_leaves', 'used_paid_leaves', 'remaining_paid_leaves', 'unpaid_leaves_taken', 'utilization_percentage'])
        ->and($summary['total_annual_leaves'])->toEqual(15.0)
        ->and($summary['used_paid_leaves'])->toEqual(5.0)
        ->and($summary['remaining_paid_leaves'])->toEqual(10.0)
        ->and($summary['unpaid_leaves_taken'])->toEqual(2.0)
        ->and($summary['utilization_percentage'])->toEqual(33.33);
});

test('get balance summary excludes specified leave when provided', function () {
    UserLeaveBalance::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'year' => 2025,
        'total_annual_leaves' => 15,
        'used_paid_leaves' => 5,
        'remaining_paid_leaves' => 10,
    ]);

    $leaveToExclude = LeaveRequest::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'from_date' => '2025-01-15',
        'status' => 'approved',
        'is_paid' => true,
        'paid_days' => 3,
        'unpaid_days' => 2,
    ]);

    // Without exclusion
    $summaryWithLeave = $this->service->getBalanceSummary($this->user->id, $this->workspace->id, 2025);

    // With exclusion
    $summaryWithoutLeave = $this->service->getBalanceSummary($this->user->id, $this->workspace->id, 2025, $leaveToExclude->id);

    expect($summaryWithLeave['unpaid_leaves_taken'])->toEqual(2.0)
        ->and($summaryWithoutLeave['unpaid_leaves_taken'])->toBe(0.0); // Excluded
});
