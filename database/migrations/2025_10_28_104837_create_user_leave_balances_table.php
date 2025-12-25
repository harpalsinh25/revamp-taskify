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
        Schema::create('user_leave_balances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('workspace_id');
            $table->year('year'); // e.g., 2024, 2025
            $table->decimal('total_annual_leaves', 8, 2)->default(0); // Total leaves allocated per year
            $table->decimal('used_paid_leaves', 8, 2)->default(0); // Paid leaves consumed
            $table->decimal('remaining_paid_leaves', 8, 2)->default(0); // Available balance
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');

            // Unique constraint: one record per user per workspace per year
            $table->unique(['user_id', 'workspace_id', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_leave_balances');
    }
};
