<?php

use App\Models\Workspace;
use App\Models\UserLeaveBalance;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This migration seeds initial leave balances for all existing users
     */
    public function up(): void
    {
        // Get current company year (respects fiscal year settings)
        $currentYear = function_exists('get_current_company_year') ? get_current_company_year() : date('Y');

        // Get total paid leaves from settings (default to 12 if not set)
        $settings = \App\Models\Setting::where('variable', 'general_settings')->first();
        $generalSettings = $settings ? json_decode($settings->value, true) : [];
        $totalAnnualLeaves = $generalSettings['total_paid_leaves_per_year'] ?? 12;

        // Get all workspaces
        $workspaces = Workspace::all();

        $initialized = 0;

        foreach ($workspaces as $workspace) {
            // Get all users in this workspace
            $users = $workspace->users;

            foreach ($users as $user) {
                // Check if balance already exists
                $existingBalance = UserLeaveBalance::where('user_id', $user->id)
                    ->where('workspace_id', $workspace->id)
                    ->where('year', $currentYear)
                    ->first();

                if (!$existingBalance) {
                    // Create new balance record
                    UserLeaveBalance::create([
                        'user_id' => $user->id,
                        'workspace_id' => $workspace->id,
                        'year' => $currentYear,
                        'total_annual_leaves' => $totalAnnualLeaves,
                        'used_paid_leaves' => 0,
                        'remaining_paid_leaves' => $totalAnnualLeaves,
                    ]);

                    $initialized++;
                }
            }
        }

        // Log the initialization
        Log::info("Leave balances initialized for {$initialized} users across " . $workspaces->count() . " workspaces");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally, you can keep this empty as we don't want to delete user balances on rollback
        // or you can add logic to remove only the balances created by this migration
    }
};
