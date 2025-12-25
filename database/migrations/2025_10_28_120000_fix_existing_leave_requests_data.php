<?php

use App\Models\LeaveRequest;
use App\Models\UserLeaveBalance;
use App\Services\LeaveBalanceService;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This migration fixes existing leave requests by populating paid_days, unpaid_days, and is_paid fields
     * Then recalculates all user leave balances based on actual data
     */
    public function up(): void
    {
        Log::info('Starting to fix existing leave requests data...');

        // Get all leave requests that don't have paid_days populated
        $leaveRequests = LeaveRequest::whereNull('paid_days')
            ->orWhereNull('total_days')
            ->orWhereNull('is_paid')
            ->get();

        $fixed = 0;

        foreach ($leaveRequests as $leaveRequest) {
            try {
                // Calculate total days
                $fromDate = Carbon::parse($leaveRequest->from_date);
                $toDate = Carbon::parse($leaveRequest->to_date);
                $totalDays = $fromDate->diffInDays($toDate) + 1; // +1 to include both start and end dates

                // Initialize the leave balance service
                $leaveBalanceService = app(LeaveBalanceService::class);
                $year = $fromDate->year;

                // Get or create balance for this user
                $balance = $leaveBalanceService->getOrCreateBalance(
                    $leaveRequest->user_id,
                    $leaveRequest->workspace_id,
                    $year
                );

                // Get remaining balance BEFORE this leave was approved
                // We need to recalculate as if this leave wasn't counted yet
                $otherApprovedLeaves = LeaveRequest::where('user_id', $leaveRequest->user_id)
                    ->where('workspace_id', $leaveRequest->workspace_id)
                    ->where('status', 'approved')
                    ->where('id', '!=', $leaveRequest->id)
                    ->whereNotNull('paid_days')
                    ->whereYear('from_date', $year)
                    ->sum('paid_days');

                $availableBalance = $balance->total_annual_leaves - $otherApprovedLeaves;

                // Determine paid/unpaid days based on status and available balance
                if ($leaveRequest->status === 'approved' && $availableBalance > 0) {
                    if ($availableBalance >= $totalDays) {
                        // All days can be paid
                        $paidDays = $totalDays;
                        $unpaidDays = 0;
                        $isPaid = true;
                    } else {
                        // Partial paid
                        $paidDays = $availableBalance;
                        $unpaidDays = $totalDays - $availableBalance;
                        $isPaid = false; // Mixed leave
                    }
                } else {
                    // Pending/Rejected or no balance
                    $paidDays = 0;
                    $unpaidDays = $totalDays;
                    $isPaid = false;
                }

                // Update the leave request
                $leaveRequest->update([
                    'total_days' => $totalDays,
                    'paid_days' => $paidDays,
                    'unpaid_days' => $unpaidDays,
                    'is_paid' => $isPaid,
                ]);

                $fixed++;
                Log::info("Fixed leave request ID {$leaveRequest->id} - Total: {$totalDays}, Paid: {$paidDays}, Unpaid: {$unpaidDays}");
            } catch (\Exception $e) {
                Log::error("Error fixing leave request ID {$leaveRequest->id}: " . $e->getMessage());
            }
        }

        Log::info("Fixed {$fixed} leave requests");

        // Now recalculate all leave balances based on actual approved leaves
        Log::info('Recalculating all leave balances...');

        $leaveBalanceService = app(LeaveBalanceService::class);
        $balances = UserLeaveBalance::all();
        $recalculated = 0;

        foreach ($balances as $balance) {
            try {
                // Calculate actual used paid leaves from database
                $usedPaidLeaves = LeaveRequest::where('user_id', $balance->user_id)
                    ->where('workspace_id', $balance->workspace_id)
                    ->where('status', 'approved')
                    ->whereYear('from_date', $balance->year)
                    ->sum('paid_days');

                // Update balance
                $balance->used_paid_leaves = $usedPaidLeaves ?? 0;
                $balance->remaining_paid_leaves = $balance->total_annual_leaves - $balance->used_paid_leaves;
                $balance->save();

                $recalculated++;
                Log::info("Recalculated balance for user {$balance->user_id} - Used: {$balance->used_paid_leaves}, Remaining: {$balance->remaining_paid_leaves}");
            } catch (\Exception $e) {
                Log::error("Error recalculating balance for user {$balance->user_id}: " . $e->getMessage());
            }
        }

        Log::info("Recalculated {$recalculated} leave balances");
        Log::info('Leave requests data fix completed!');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is a data fix, we don't reverse it
        Log::info('Rollback of leave requests data fix - no action taken');
    }
};






















