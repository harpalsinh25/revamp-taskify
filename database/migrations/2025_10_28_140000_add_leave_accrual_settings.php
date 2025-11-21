<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds support for monthly leave accrual system
     */
    public function up(): void
    {
        // Add accrual-related columns to user_leave_balances table
        Schema::table('user_leave_balances', function (Blueprint $table) {
            if (!Schema::hasColumn('user_leave_balances', 'accrued_leaves')) {
                $table->decimal('accrued_leaves', 8, 2)->default(0)->after('total_annual_leaves')
                    ->comment('Total leaves accrued based on months worked');
            }
            if (!Schema::hasColumn('user_leave_balances', 'months_worked')) {
                $table->integer('months_worked')->default(12)->after('accrued_leaves')
                    ->comment('Number of months worked in this year');
            }
            if (!Schema::hasColumn('user_leave_balances', 'accrual_start_date')) {
                $table->date('accrual_start_date')->nullable()->after('months_worked')
                    ->comment('Date from which leave accrual starts (joining date or Jan 1)');
            }
        });

        // Update settings to enable monthly accrual
        $settings = \App\Models\Setting::where('variable', 'general_settings')->first();
        if ($settings) {
            $generalSettings = json_decode($settings->value, true);

            // Add monthly accrual settings if not exists
            if (!isset($generalSettings['leave_accrual_type'])) {
                $generalSettings['leave_accrual_type'] = 'monthly'; // 'lump_sum' or 'monthly'
            }
            if (!isset($generalSettings['monthly_accrual_rate'])) {
                $totalLeaves = $generalSettings['total_paid_leaves_per_year'] ?? 15;
                $generalSettings['monthly_accrual_rate'] = round($totalLeaves / 12, 2); // 1.25
            }

            $settings->update(['value' => json_encode($generalSettings)]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_leave_balances', function (Blueprint $table) {
            if (Schema::hasColumn('user_leave_balances', 'accrued_leaves')) {
                $table->dropColumn('accrued_leaves');
            }
            if (Schema::hasColumn('user_leave_balances', 'months_worked')) {
                $table->dropColumn('months_worked');
            }
            if (Schema::hasColumn('user_leave_balances', 'accrual_start_date')) {
                $table->dropColumn('accrual_start_date');
            }
        });
    }
};























