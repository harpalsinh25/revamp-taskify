<?php

use App\Models\User;
use App\Models\Workspace;
use App\Models\UserLeaveBalance;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->workspace = Workspace::factory()->create();
});

test('user leave balance can be created', function () {
    $balance = UserLeaveBalance::create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'year' => 2025,
        'total_annual_leaves' => 15,
        'used_paid_leaves' => 0,
        'remaining_paid_leaves' => 15,
    ]);

    expect($balance)->toBeInstanceOf(UserLeaveBalance::class)
        ->and((float)$balance->total_annual_leaves)->toEqual(15.0)
        ->and((float)$balance->remaining_paid_leaves)->toEqual(15.0);
});

test('balance belongs to user', function () {
    $balance = UserLeaveBalance::factory()->create([
        'user_id' => $this->user->id,
    ]);

    expect($balance->user)->toBeInstanceOf(User::class)
        ->and($balance->user->id)->toBe($this->user->id);
});

test('balance belongs to workspace', function () {
    $balance = UserLeaveBalance::factory()->create([
        'workspace_id' => $this->workspace->id,
    ]);

    expect($balance->workspace)->toBeInstanceOf(Workspace::class)
        ->and($balance->workspace->id)->toBe($this->workspace->id);
});

test('update remaining balance recalculates correctly', function () {
    $balance = UserLeaveBalance::factory()->create([
        'total_annual_leaves' => 15,
        'used_paid_leaves' => 5,
        'remaining_paid_leaves' => 10,
    ]);

    $balance->used_paid_leaves = 8;
    $balance->updateRemainingBalance();

    expect((float)$balance->remaining_paid_leaves)->toEqual(7.0);
});

test('has sufficient balance returns true when balance available', function () {
    $balance = UserLeaveBalance::factory()->create([
        'remaining_paid_leaves' => 10,
    ]);

    expect($balance->hasSufficientBalance(5))->toBeTrue()
        ->and($balance->hasSufficientBalance(10))->toBeTrue()
        ->and($balance->hasSufficientBalance(11))->toBeFalse();
});

test('deduct leaves reduces balance correctly', function () {
    $balance = UserLeaveBalance::factory()->create([
        'total_annual_leaves' => 15,
        'used_paid_leaves' => 0,
        'remaining_paid_leaves' => 15,
    ]);

    $balance->deductLeaves(3);

    expect($balance->used_paid_leaves)->toEqual(3.0)
        ->and($balance->remaining_paid_leaves)->toEqual(12.0);
});

test('restore leaves increases balance correctly', function () {
    $balance = UserLeaveBalance::factory()->create([
        'total_annual_leaves' => 15,
        'used_paid_leaves' => 5,
        'remaining_paid_leaves' => 10,
    ]);

    $balance->restoreLeaves(2);

    expect($balance->used_paid_leaves)->toEqual(3.0)
        ->and($balance->remaining_paid_leaves)->toEqual(12.0);
});

test('restore leaves cannot go below zero', function () {
    $balance = UserLeaveBalance::factory()->create([
        'total_annual_leaves' => 15,
        'used_paid_leaves' => 2,
        'remaining_paid_leaves' => 13,
    ]);

    $balance->restoreLeaves(5); // Try to restore more than used

    expect($balance->used_paid_leaves)->toEqual(0.0)
        ->and($balance->remaining_paid_leaves)->toEqual(15.0);
});

test('unique constraint prevents duplicate balances for same user workspace year', function () {
    UserLeaveBalance::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'year' => 2025,
    ]);

    expect(fn() => UserLeaveBalance::create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'year' => 2025,
        'total_annual_leaves' => 15,
        'used_paid_leaves' => 0,
        'remaining_paid_leaves' => 15,
    ]))->toThrow(\Illuminate\Database\QueryException::class);
});

test('different years allow separate balance records', function () {
    $balance2024 = UserLeaveBalance::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'year' => 2024,
        'total_annual_leaves' => 12,
    ]);

    $balance2025 = UserLeaveBalance::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'year' => 2025,
        'total_annual_leaves' => 15,
    ]);

    expect($balance2024->total_annual_leaves)->toEqual(12.0)
        ->and($balance2025->total_annual_leaves)->toEqual(15.0);
});
