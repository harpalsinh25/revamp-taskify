<?php

use App\Models\User;
use App\Models\Workspace;
use App\Models\UserLeaveBalance;
use App\Models\LeaveRequest;
use App\Models\Setting;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');

    $this->user = User::factory()->create();

    $this->workspace = Workspace::factory()->create();
    $this->workspace->users()->attach([$this->admin->id, $this->user->id]);

    session(['workspace_id' => $this->workspace->id]);

    Setting::updateOrCreate(
        ['variable' => 'general_settings'],
        ['value' => json_encode([
            'total_paid_leaves_per_year' => 15,
            'leave_accrual_type' => 'lump_sum',
        ])]
    );
});

test('leave within balance is fully paid', function () {
    $this->actingAs($this->admin);

    UserLeaveBalance::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'year' => 2025,
        'total_annual_leaves' => 15,
        'used_paid_leaves' => 0,
        'remaining_paid_leaves' => 15,
    ]);

    $leave = LeaveRequest::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'from_date' => '2025-01-15',
        'to_date' => '2025-01-17', // 3 days
        'status' => 'pending',
    ]);

    $this->postJson('/leave-requests/update', [
        'id' => $leave->id,
        'from_date' => '15-01-2025',
        'to_date' => '17-01-2025',
        'reason' => 'Test',
        'status' => 'approved',
        'is_paid' => 1,
    ]);

    $leave->refresh();
    expect($leave->paid_days)->toEqual(3.0)
        ->and($leave->unpaid_days)->toEqual(0.0);
});

test('leave exceeding balance is split into paid and unpaid', function () {
    $this->actingAs($this->admin);

    UserLeaveBalance::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'year' => 2025,
        'total_annual_leaves' => 15,
        'used_paid_leaves' => 13,
        'remaining_paid_leaves' => 2,
    ]);

    $leave = LeaveRequest::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'from_date' => '2025-01-15',
        'to_date' => '2025-01-19', // 5 days
        'status' => 'pending',
    ]);

    $this->postJson('/leave-requests/update', [
        'id' => $leave->id,
        'from_date' => '15-01-2025',
        'to_date' => '19-01-2025',
        'reason' => 'Test',
        'status' => 'approved',
        'is_paid' => 1,
    ]);

    $leave->refresh();
    expect($leave->total_days)->toEqual(5.0)
        ->and($leave->paid_days)->toEqual(2.0) // Only 2 available
        ->and($leave->unpaid_days)->toEqual(3.0); // Remaining 3 unpaid
});

test('leave with zero balance is fully unpaid', function () {
    $this->actingAs($this->admin);

    UserLeaveBalance::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'year' => 2025,
        'total_annual_leaves' => 15,
        'used_paid_leaves' => 15,
        'remaining_paid_leaves' => 0,
    ]);

    $leave = LeaveRequest::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'from_date' => '2025-01-15',
        'to_date' => '2025-01-16', // 2 days
        'status' => 'pending',
    ]);

    $this->postJson('/leave-requests/update', [
        'id' => $leave->id,
        'from_date' => '15-01-2025',
        'to_date' => '16-01-2025',
        'reason' => 'Test',
        'status' => 'approved',
        'is_paid' => 1,
    ]);

    $leave->refresh();
    expect($leave->paid_days)->toEqual(0.0)
        ->and($leave->unpaid_days)->toEqual(2.0)
        ->and($leave->is_paid)->toBeFalse(); // No paid component
});

test('partial leave of 4 hours counts as 0.5 days', function () {
    $totalDays = calculate_leave_days('2025-01-15', '2025-01-15', '09:00', '13:00');

    expect($totalDays)->toEqual(0.5);
});

test('full day leave counts correctly', function () {
    $totalDays = calculate_leave_days('2025-01-15', '2025-01-17');

    expect($totalDays)->toEqual(3.0); // 15, 16, 17 = 3 days
});

test('multiple approved leaves sum up in balance', function () {
    $this->actingAs($this->admin);

    $balance = UserLeaveBalance::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'year' => 2025,
        'total_annual_leaves' => 15,
        'used_paid_leaves' => 0,
        'remaining_paid_leaves' => 15,
    ]);

    // Approve first leave
    $leave1 = LeaveRequest::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'from_date' => '2025-01-15',
        'to_date' => '2025-01-16',
        'status' => 'approved',
        'is_paid' => true,
        'paid_days' => 2,
    ]);

    // Approve second leave
    $leave2 = LeaveRequest::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'from_date' => '2025-02-10',
        'to_date' => '2025-02-12',
        'status' => 'approved',
        'is_paid' => true,
        'paid_days' => 3,
    ]);

    $service = new \App\Services\LeaveBalanceService();
    $usedLeaves = $service->calculateUsedPaidLeaves($this->user->id, $this->workspace->id, 2025);

    expect($usedLeaves)->toEqual(5.0);
});

test('bulk delete restores balance for all approved paid leaves', function () {
    $this->actingAs($this->admin);

    $leave1 = LeaveRequest::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'from_date' => '2025-01-15',
        'status' => 'approved',
        'is_paid' => true,
        'paid_days' => 2,
    ]);

    $leave2 = LeaveRequest::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'from_date' => '2025-02-10',
        'status' => 'approved',
        'is_paid' => true,
        'paid_days' => 3,
    ]);

    // Set balance
    UserLeaveBalance::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'year' => 2025,
        'total_annual_leaves' => 15,
        'used_paid_leaves' => 5,
        'remaining_paid_leaves' => 10,
    ]);

    $response = $this->postJson('/leave-requests/destroy_multiple', [
        'ids' => [$leave1->id, $leave2->id]
    ]);

    $response->assertStatus(200);

    // Balance should be fully restored
    $balance = UserLeaveBalance::where('user_id', $this->user->id)->first();
    expect($balance->used_paid_leaves)->toEqual(0.0)
        ->and($balance->remaining_paid_leaves)->toEqual(15.0);
});

test('helper function calculates company year correctly for jan to dec', function () {
    Setting::updateOrCreate(
        ['variable' => 'general_settings'],
        ['value' => json_encode([
            'company_year_start_month' => 1,
            'company_year_start_day' => 1,
        ])]
    );

    Carbon::setTestNow(Carbon::create(2025, 6, 15)); // June 15, 2025

    $companyYear = get_current_company_year();

    expect($companyYear)->toBe(2025);
});

test('helper function calculates company year correctly for apr to mar', function () {
    Setting::updateOrCreate(
        ['variable' => 'general_settings'],
        ['value' => json_encode([
            'company_year_start_month' => 4, // April
            'company_year_start_day' => 1,
        ])]
    );

    // Test before April 1
    Carbon::setTestNow(Carbon::create(2025, 2, 15)); // Feb 15, 2025
    expect(get_current_company_year())->toBe(2024); // Still in 2024 company year

    // Test after April 1
    Carbon::setTestNow(Carbon::create(2025, 5, 15)); // May 15, 2025
    expect(get_current_company_year())->toBe(2025); // Now in 2025 company year
});

test('company year dates helper returns correct period', function () {
    Setting::updateOrCreate(
        ['variable' => 'general_settings'],
        ['value' => json_encode([
            'company_year_start_month' => 4,
            'company_year_start_day' => 1,
        ])]
    );

    $dates = get_company_year_dates(2024);

    expect($dates['start']->format('Y-m-d'))->toBe('2024-04-01')
        ->and($dates['end']->format('Y-m-d'))->toBe('2025-03-31')
        ->and($dates['year'])->toBe(2024);
});

test('format company year displays correctly', function () {
    Setting::updateOrCreate(
        ['variable' => 'general_settings'],
        ['value' => json_encode([
            'company_year_start_month' => 4,
            'company_year_start_day' => 1,
        ])]
    );

    $formatted = format_company_year(2024, true);

    expect($formatted)->toBe('Apr 2024 - Mar 2025');
});
