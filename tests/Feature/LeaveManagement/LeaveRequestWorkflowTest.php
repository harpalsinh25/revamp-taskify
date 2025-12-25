<?php

use App\Models\User;
use App\Models\Workspace;
use App\Models\UserLeaveBalance;
use App\Models\LeaveRequest;
use App\Models\Setting;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Create roles
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');

    $this->user = User::factory()->create();
    $this->user->assignRole('member');

    $this->workspace = Workspace::factory()->create();
    $this->workspace->users()->attach([$this->admin->id, $this->user->id]);

    // Set workspace in session
    session(['workspace_id' => $this->workspace->id]);

    // Set general settings
    Setting::updateOrCreate(
        ['variable' => 'general_settings'],
        ['value' => json_encode([
            'total_paid_leaves_per_year' => 15,
            'leave_accrual_type' => 'lump_sum',
            'company_year_start_month' => 1,
            'company_year_start_day' => 1,
        ])]
    );

    // Create initial balance
    UserLeaveBalance::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'year' => 2025,
        'total_annual_leaves' => 15,
        'used_paid_leaves' => 0,
        'remaining_paid_leaves' => 15,
    ]);
});

test('user can create leave request in pending status', function () {
    $this->actingAs($this->user);

    $response = $this->postJson('/leave-requests/store', [
        'from_date' => '2025-01-15',
        'to_date' => '2025-01-16',
        'reason' => 'Personal work',
        'status' => 'pending',
    ]);

    $response->assertStatus(200)
        ->assertJson(['error' => false]);

    $this->assertDatabaseHas('leave_requests', [
        'user_id' => $this->user->id,
        'status' => 'pending',
        'reason' => 'Personal work',
    ]);
});

test('admin can approve leave and balance is deducted', function () {
    $this->actingAs($this->admin);

    $leave = LeaveRequest::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'from_date' => '2025-01-15',
        'to_date' => '2025-01-17',
        'status' => 'pending',
    ]);

    $response = $this->postJson('/leave-requests/update', [
        'id' => $leave->id,
        'from_date' => '15-01-2025',
        'to_date' => '17-01-2025',
        'reason' => 'Personal work',
        'status' => 'approved',
        'is_paid' => 1,
    ]);

    $response->assertStatus(200);

    $leave->refresh();
    expect($leave->status)->toBe('approved')
        ->and($leave->total_days)->toBe(3.0)
        ->and($leave->paid_days)->toBe(3.0)
        ->and($leave->unpaid_days)->toBe(0.0)
        ->and($leave->is_paid)->toBeTrue();

    // Check balance updated
    $balance = UserLeaveBalance::where('user_id', $this->user->id)->first();
    expect($balance->used_paid_leaves)->toBe(3.0)
        ->and($balance->remaining_paid_leaves)->toBe(12.0);
});

test('admin can mark leave as unpaid explicitly', function () {
    $this->actingAs($this->admin);

    $leave = LeaveRequest::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'from_date' => '2025-01-15',
        'to_date' => '2025-01-16',
        'status' => 'pending',
    ]);

    $response = $this->postJson('/leave-requests/update', [
        'id' => $leave->id,
        'from_date' => '15-01-2025',
        'to_date' => '16-01-2025',
        'reason' => 'Personal work',
        'status' => 'approved',
        'is_paid' => 0, // Explicitly unpaid
    ]);

    $leave->refresh();
    expect($leave->status)->toBe('approved')
        ->and($leave->paid_days)->toBe(0.0)
        ->and($leave->unpaid_days)->toBe(2.0)
        ->and($leave->is_paid)->toBeFalse();

    // Balance should not change
    $balance = UserLeaveBalance::where('user_id', $this->user->id)->first();
    expect($balance->used_paid_leaves)->toBe(0.0);
});

test('leave exceeding balance is split into paid and unpaid', function () {
    $this->actingAs($this->admin);

    // Set low balance
    UserLeaveBalance::where('user_id', $this->user->id)->update([
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

    $response = $this->postJson('/leave-requests/update', [
        'id' => $leave->id,
        'from_date' => '15-01-2025',
        'to_date' => '19-01-2025',
        'reason' => 'Medical',
        'status' => 'approved',
        'is_paid' => 1,
    ]);

    $leave->refresh();
    expect($leave->paid_days)->toBe(2.0) // Only 2 days available
        ->and($leave->unpaid_days)->toBe(3.0) // Remaining 3 days unpaid
        ->and($leave->is_paid)->toBeTrue();
});

test('rejecting approved leave restores balance', function () {
    $this->actingAs($this->admin);

    // Create approved leave
    $leave = LeaveRequest::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'from_date' => '2025-01-15',
        'to_date' => '2025-01-17',
        'status' => 'approved',
        'is_paid' => true,
        'paid_days' => 3,
        'unpaid_days' => 0,
    ]);

    // Update balance
    UserLeaveBalance::where('user_id', $this->user->id)->update([
        'used_paid_leaves' => 3,
        'remaining_paid_leaves' => 12,
    ]);

    // Reject the leave
    $response = $this->postJson('/leave-requests/update', [
        'id' => $leave->id,
        'from_date' => '15-01-2025',
        'to_date' => '17-01-2025',
        'reason' => 'Personal work',
        'status' => 'rejected',
    ]);

    // Balance should be restored
    $balance = UserLeaveBalance::where('user_id', $this->user->id)->first();
    expect($balance->used_paid_leaves)->toBe(0.0)
        ->and($balance->remaining_paid_leaves)->toBe(15.0);
});

test('deleting approved paid leave restores balance', function () {
    $this->actingAs($this->admin);

    $leave = LeaveRequest::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'from_date' => '2025-01-15',
        'to_date' => '2025-01-16',
        'status' => 'approved',
        'is_paid' => true,
        'paid_days' => 2,
    ]);

    // Update balance to reflect used leaves
    UserLeaveBalance::where('user_id', $this->user->id)->update([
        'used_paid_leaves' => 2,
        'remaining_paid_leaves' => 13,
    ]);

    $response = $this->deleteJson("/leave-requests/destroy/{$leave->id}");

    $response->assertStatus(200);

    // Balance should be restored
    $balance = UserLeaveBalance::where('user_id', $this->user->id)->first();
    expect($balance->used_paid_leaves)->toBe(0.0)
        ->and($balance->remaining_paid_leaves)->toBe(15.0);
});

test('user cannot approve their own leave request', function () {
    $this->actingAs($this->user);

    $leave = LeaveRequest::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'from_date' => '2025-01-15',
        'to_date' => '2025-01-16',
        'status' => 'pending',
    ]);

    $response = $this->postJson('/leave-requests/update', [
        'id' => $leave->id,
        'from_date' => '15-01-2025',
        'to_date' => '16-01-2025',
        'reason' => 'Test',
        'status' => 'approved',
    ]);

    $response->assertJson(['error' => true])
        ->assertJsonFragment(['message' => 'You can not approve own leave request.']);
});

test('partial leave calculates as 0.5 days', function () {
    $this->actingAs($this->admin);

    $leave = LeaveRequest::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'from_date' => '2025-01-15',
        'to_date' => '2025-01-15',
        'from_time' => '09:00',
        'to_time' => '13:00', // 4 hours = 0.5 day
        'status' => 'pending',
    ]);

    $response = $this->postJson('/leave-requests/update', [
        'id' => $leave->id,
        'from_date' => '15-01-2025',
        'to_date' => '15-01-2025',
        'from_time' => '09:00',
        'to_time' => '13:00',
        'partialLeave' => 'on',
        'reason' => 'Half day',
        'status' => 'approved',
        'is_paid' => 1,
    ]);

    $leave->refresh();
    expect($leave->total_days)->toBe(0.5)
        ->and($leave->paid_days)->toBe(0.5)
        ->and($leave->unpaid_days)->toBe(0.0);
});

test('get user balance API endpoint requires authentication', function () {
    $response = $this->getJson('/leave-requests/get-user-balance');

    $response->assertStatus(401); // Unauthorized
});

test('get user balance API returns correct data', function () {
    $this->actingAs($this->user);

    UserLeaveBalance::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'year' => 2025,
        'total_annual_leaves' => 15,
        'used_paid_leaves' => 5,
        'remaining_paid_leaves' => 10,
    ]);

    $response = $this->getJson('/leave-requests/get-user-balance?user_id=' . $this->user->id);

    $response->assertStatus(200)
        ->assertJson([
            'error' => false,
            'balance' => [
                'total_annual_leaves' => 15.0,
                'used_paid_leaves' => 5.0,
                'remaining_paid_leaves' => 10.0,
            ]
        ]);
});

test('user cannot access another users balance without permission', function () {
    $this->actingAs($this->user);

    $otherUser = User::factory()->create();

    $response = $this->getJson('/leave-requests/get-user-balance?user_id=' . $otherUser->id);

    $response->assertStatus(403); // Forbidden
});

test('admin can access any users balance', function () {
    $this->actingAs($this->admin);

    UserLeaveBalance::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'year' => 2025,
        'total_annual_leaves' => 15,
    ]);

    $response = $this->getJson('/leave-requests/get-user-balance?user_id=' . $this->user->id);

    $response->assertStatus(200)
        ->assertJson(['error' => false]);
});
