<?php

use App\Models\LeaveRequest;
use App\Models\UserLeaveBalance;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * TRANSITION YEAR STRATEGY:
     * - All existing approved leaves BEFORE system implementation are marked as paid (for records)
     * - BUT they don't count against user's 2025 balance
     * - Users get fresh full allocation for 2025
     * - From 2026 onwards, normal rules apply
     *
     * This is the fairest approach for mid-year implementation!
     */
    public function up(): void
    {
        // Define the system implementation date (when v2.0 went live)
        $systemImplementationDate = Carbon::create(2025, 10, 28); // Adjust this to your actual date

        Log::info("Starting transition year leave balance adjustment...");
        Log::info("System implementation date: {$systemImplementationDate->toDateString()}");

        // Get all leave requests that were APPROVED before implementation date
        $preImplementationLeaves = LeaveRequest::where('status', 'approved')
            ->where('from_date', '<', $systemImplementationDate)
            ->get();

        Log::info("Found {$preImplementationLeaves->count()} pre-implementation approved leaves");

        // Mark them as paid (for historical accuracy) but flag them as "transition"
        foreach ($preImplementationLeaves as $leave) {
            // Ensure the leave has the new fields populated
            if ($leave->total_days === null) {
                $fromDate = Carbon::parse($leave->from_date);
                $toDate = Carbon::parse($leave->to_date);
                $totalDays = $fromDate->diffInDays($toDate) + 1;

                $leave->update([
                    'total_days' => $totalDays,
                    'paid_days' => $totalDays, // Mark as paid (company did pay them)
                    'unpaid_days' => 0,
                    'is_paid' => true,
                ]);
            } else {
                // Already has values, just ensure it's marked as paid
                $leave->update([
                    'is_paid' => true,
                    'paid_days' => $leave->total_days,
                    'unpaid_days' => 0,
                ]);
            }

            Log::info("Marked leave ID {$leave->id} as paid (pre-implementation)");
        }

        // NOW: Reset all 2025 balances to give users fresh allocation
        // The key: We'll adjust the balance calculation to EXCLUDE pre-implementation leaves
        $balances = UserLeaveBalance::where('year', 2025)->get();

        Log::info("Resetting {$balances->count()} user balances for transition year 2025");

        foreach ($balances as $balance) {
            // Calculate ONLY post-implementation approved leaves for 2025
            $postImplementationUsedLeaves = LeaveRequest::where('user_id', $balance->user_id)
                ->where('workspace_id', $balance->workspace_id)
                ->where('status', 'approved')
                ->where('from_date', '>=', $systemImplementationDate) // Only count leaves AFTER implementation
                ->whereYear('from_date', 2025)
                ->sum('paid_days');

            // Update balance to reflect only post-implementation usage
            $balance->used_paid_leaves = $postImplementationUsedLeaves ?? 0;
            $balance->remaining_paid_leaves = $balance->total_annual_leaves - $balance->used_paid_leaves;
            $balance->save();

            Log::info("User {$balance->user_id}: Reset balance - Used: {$balance->used_paid_leaves}, Remaining: {$balance->remaining_paid_leaves}");
        }

        Log::info("✅ Transition year adjustment complete!");
        Log::info("📝 NOTE: Pre-implementation leaves are marked as paid (for records) but don't count against 2025 balance");
        Log::info("📅 From 2026 onwards, all leaves will count normally");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is a data adjustment migration
        // Rollback would recalculate including all leaves
        Log::info('Rolling back transition year adjustment - recalculating all 2025 leaves');

        $balances = UserLeaveBalance::where('year', 2025)->get();

        foreach ($balances as $balance) {
            // Recalculate including ALL approved leaves
            $allUsedLeaves = LeaveRequest::where('user_id', $balance->user_id)
                ->where('workspace_id', $balance->workspace_id)
                ->where('status', 'approved')
                ->whereYear('from_date', 2025)
                ->sum('paid_days');

            $balance->used_paid_leaves = $allUsedLeaves ?? 0;
            $balance->remaining_paid_leaves = $balance->total_annual_leaves - $balance->used_paid_leaves;
            $balance->save();
        }
    }
};























