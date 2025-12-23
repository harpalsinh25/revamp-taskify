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
     * Adds critical leave management features:
     * 1. leave_balance_adjustments table - tracks payslip overrides
     * 2. company_year field - supports company year vs calendar year
     * 3. leave_overlap_logs table - audit trail for overlap detection
     * 4. carry_forward and expired leaves fields - future features
     */
    public function up(): void
    {
        // 1. CRITICAL: Create leave_balance_adjustments table
        // This table is used extensively by LeaveBalanceSyncService, LeaveBalanceEngine, etc.
        if (!Schema::hasTable('leave_balance_adjustments')) {
            Schema::create('leave_balance_adjustments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('payslip_id')->nullable();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('workspace_id');
                $table->integer('year')->comment('Company year for the adjustment');
                $table->decimal('delta_paid', 8, 2)->default(0)
                    ->comment('Change in paid leaves (positive = granted, negative = reduced)');
                $table->decimal('delta_advance', 8, 2)->default(0)
                    ->comment('Change in advance leaves (positive = advance granted)');
                $table->text('notes')->nullable()
                    ->comment('Optional notes about the adjustment');
                $table->timestamps();

                // Foreign key constraints
                $table->foreign('payslip_id')->references('id')->on('payslips')->onDelete('set null');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');

                // Indexes for performance
                $table->index(['user_id', 'workspace_id', 'year']);
                $table->index('payslip_id');
            });
        }

        // 2. CRITICAL: Add company_year field to user_leave_balances
        // Used 80+ times across the application for company year tracking
        if (Schema::hasTable('user_leave_balances')) {
            Schema::table('user_leave_balances', function (Blueprint $table) {
                if (!Schema::hasColumn('user_leave_balances', 'company_year')) {
                    $table->integer('company_year')->nullable()->after('year')
                        ->comment('Company year identifier (e.g., 2024 for Apr 2024 - Mar 2025)');
                }
            });

            // Populate company_year from existing year values
            DB::statement("
                UPDATE user_leave_balances
                SET company_year = year
                WHERE company_year IS NULL
            ");

            // Add index for company_year queries
            Schema::table('user_leave_balances', function (Blueprint $table) {
                try {
                    if (!Schema::hasIndex('user_leave_balances', 'user_leave_balances_company_year_index')) {
                        $table->index('company_year');
                    }
                } catch (\Exception $e) {
                    // Index might already exist, ignore
                }
            });
        }

        // 3. OPTIONAL: Create leave_overlap_logs table
        // Model exists for future overlap detection audit trail
        if (!Schema::hasTable('leave_overlap_logs')) {
            Schema::create('leave_overlap_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('leave_request_id');
                $table->unsignedBigInteger('overlapping_with_id')
                    ->comment('ID of the leave request that overlaps');
                $table->date('overlap_start_date')
                    ->comment('Start date of the overlapping period');
                $table->date('overlap_end_date')
                    ->comment('End date of the overlapping period');
                $table->decimal('overlap_days', 8, 2)
                    ->comment('Number of days overlapping');
                $table->enum('action_taken', ['blocked', 'warned', 'allowed'])
                    ->default('warned')
                    ->comment('What action was taken when overlap was detected');
                $table->unsignedBigInteger('detected_by')->nullable()
                    ->comment('User who detected/processed the overlap');
                $table->timestamp('detected_at')->useCurrent();
                $table->text('notes')->nullable();

                // Foreign key constraints
                $table->foreign('leave_request_id')->references('id')->on('leave_requests')->onDelete('cascade');
                $table->foreign('overlapping_with_id')->references('id')->on('leave_requests')->onDelete('cascade');
                $table->foreign('detected_by')->references('id')->on('users')->onDelete('set null');

                // Indexes for performance
                $table->index('leave_request_id');
                $table->index('overlapping_with_id');
                $table->index('detected_at');
            });
        }

        // 4. OPTIONAL: Add carry_forward and expired leaves fields
        // Fields defined with defaults (0) for future carry-forward feature
        if (Schema::hasTable('user_leave_balances')) {
            Schema::table('user_leave_balances', function (Blueprint $table) {
                if (!Schema::hasColumn('user_leave_balances', 'carry_forward_leaves')) {
                    $table->decimal('carry_forward_leaves', 8, 2)->default(0)->after('advanced_paid_leaves')
                        ->comment('Leaves carried forward from previous year');
                }
                if (!Schema::hasColumn('user_leave_balances', 'expired_leaves')) {
                    $table->decimal('expired_leaves', 8, 2)->default(0)->after('carry_forward_leaves')
                        ->comment('Leaves that expired at year end (not used)');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop tables
        Schema::dropIfExists('leave_overlap_logs');
        Schema::dropIfExists('leave_balance_adjustments');

        // Remove fields from user_leave_balances
        if (Schema::hasTable('user_leave_balances')) {
            Schema::table('user_leave_balances', function (Blueprint $table) {
                try {
                    $table->dropIndex(['company_year']);
                } catch (\Exception $e) {
                    // Index might not exist
                }

                $table->dropColumn([
                    'company_year',
                    'carry_forward_leaves',
                    'expired_leaves'
                ]);
            });
        }
    }
};







