<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds advanced_paid_leaves field to track overridden/advance leaves from payslip adjustments
     * Migrates existing negative remaining_paid_leaves to advanced_paid_leaves
     */
    public function up(): void
    {
        Schema::table('user_leave_balances', function (Blueprint $table) {
            if (!Schema::hasColumn('user_leave_balances', 'advanced_paid_leaves')) {
                $table->decimal('advanced_paid_leaves', 8, 2)->default(0)->after('remaining_paid_leaves')
                    ->comment('Advance/overridden paid leaves granted via payslip adjustments');
            }
        });

        // Migrate existing data: convert negative remaining_paid_leaves to advanced_paid_leaves
        DB::statement("
            UPDATE user_leave_balances
            SET
                advanced_paid_leaves = ABS(LEAST(remaining_paid_leaves, 0)),
                remaining_paid_leaves = GREATEST(remaining_paid_leaves, 0)
            WHERE remaining_paid_leaves < 0
        ");

        // Recalculate used_paid_leaves from LeaveRequests to ensure accuracy
        // This ensures used_paid_leaves only contains leaves from LeaveRequests
        $balances = DB::table('user_leave_balances')->get();

        foreach ($balances as $balance) {
            $usedPaidLeaves = DB::table('leave_requests')
                ->where('user_id', $balance->user_id)
                ->where('workspace_id', $balance->workspace_id)
                ->where('status', 'approved')
                ->where('is_paid', true)
                ->whereYear('from_date', $balance->year)
                ->sum('paid_days');

            $usedPaidLeaves = (float) ($usedPaidLeaves ?? 0);

            // Get effective total (accrued if exists, otherwise annual total)
            $effectiveTotal = $balance->accrued_leaves ?? $balance->total_annual_leaves;
            $effectiveTotal = (float) ($effectiveTotal ?? 0);

            // Recalculate remaining (always non-negative)
            $remainingPaidLeaves = max(0, $effectiveTotal - $usedPaidLeaves);

            DB::table('user_leave_balances')
                ->where('id', $balance->id)
                ->update([
                    'used_paid_leaves' => $usedPaidLeaves,
                    'remaining_paid_leaves' => $remainingPaidLeaves,
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Before dropping, convert advanced_paid_leaves back to negative remaining_paid_leaves
        DB::statement("
            UPDATE user_leave_balances
            SET
                remaining_paid_leaves = remaining_paid_leaves - advanced_paid_leaves
            WHERE advanced_paid_leaves > 0
        ");

        Schema::table('user_leave_balances', function (Blueprint $table) {
            if (Schema::hasColumn('user_leave_balances', 'advanced_paid_leaves')) {
                $table->dropColumn('advanced_paid_leaves');
            }
        });
    }
};
