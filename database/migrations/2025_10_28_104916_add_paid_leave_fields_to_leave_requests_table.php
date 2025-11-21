<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            // Check if columns don't exist before adding them
            if (!Schema::hasColumn('leave_requests', 'total_days')) {
                $table->decimal('total_days', 8, 2)->nullable()->after('status');
            }
            if (!Schema::hasColumn('leave_requests', 'paid_days')) {
                $table->decimal('paid_days', 8, 2)->nullable()->after('total_days');
            }
            if (!Schema::hasColumn('leave_requests', 'unpaid_days')) {
                $table->decimal('unpaid_days', 8, 2)->nullable()->after('paid_days');
            }
            if (!Schema::hasColumn('leave_requests', 'is_paid')) {
                $table->boolean('is_paid')->nullable()->after('unpaid_days');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            if (Schema::hasColumn('leave_requests', 'total_days')) {
                $table->dropColumn('total_days');
            }
            if (Schema::hasColumn('leave_requests', 'paid_days')) {
                $table->dropColumn('paid_days');
            }
            if (Schema::hasColumn('leave_requests', 'unpaid_days')) {
                $table->dropColumn('unpaid_days');
            }
            if (Schema::hasColumn('leave_requests', 'is_paid')) {
                $table->dropColumn('is_paid');
            }
        });
    }
};
