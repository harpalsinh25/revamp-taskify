<?php

namespace Tests\Unit;

use App\Models\LeaveRequest;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserLeaveBalance;
use App\Models\Workspace;
use App\Services\LeaveBalanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveBalanceServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::create([
            'variable' => 'general_settings',
            'value' => json_encode([
                'company_year_start_month' => 1,
                'company_year_start_day' => 1,
                'total_paid_leaves_per_year' => 15,
            ]),
        ]);
    }

    public function test_workspace_summary_calculates_totals_and_counts(): void
    {
        $service = app(LeaveBalanceService::class);
        $workspace = Workspace::factory()->create();
        $year = 2025;

        $users = User::factory()->count(3)->create();

        UserLeaveBalance::factory()->create([
            'user_id' => $users[0]->id,
            'workspace_id' => $workspace->id,
            'year' => $year,
            'total_annual_leaves' => 15,
            'used_paid_leaves' => 5,
            'remaining_paid_leaves' => 10,
        ]);

        UserLeaveBalance::factory()->create([
            'user_id' => $users[1]->id,
            'workspace_id' => $workspace->id,
            'year' => $year,
            'total_annual_leaves' => 15,
            'used_paid_leaves' => 14,
            'remaining_paid_leaves' => 1,
        ]);

        UserLeaveBalance::factory()->create([
            'user_id' => $users[2]->id,
            'workspace_id' => $workspace->id,
            'year' => $year,
            'total_annual_leaves' => 20,
            'used_paid_leaves' => 20,
            'remaining_paid_leaves' => 0,
        ]);

        $summary = $service->getWorkspaceSummary($workspace->id, $year);

        $this->assertSame(3, $summary['member_count']);
        $this->assertEquals(50.0, $summary['total_allocation']);
        $this->assertEquals(50.0, $summary['total_annual_allocation']);
        $this->assertEquals(50.0, $summary['total_accrued_allocation']);
        $this->assertEquals(39.0, $summary['total_used']);
        $this->assertEquals(11.0, $summary['total_remaining']);
        $this->assertEquals(11.0, $summary['total_accrued_remaining']);
        $this->assertEquals(11.0, $summary['total_annual_remaining']);
        $this->assertEquals(78.0, $summary['overall_utilization']);
        $this->assertEquals(78.0, $summary['overall_utilization_accrued']);
        $this->assertTrue($summary['accrual_enabled']);
        $this->assertSame(1, $summary['low_balance_count']);
        $this->assertSame(1, $summary['exhausted_count']);
    }

    public function test_recent_workspace_requests_include_latest_entries(): void
    {
        $service = app(LeaveBalanceService::class);
        $workspace = Workspace::factory()->create();
        $year = 2025;

        $user = User::factory()->create([
            'first_name' => 'Alex',
            'last_name' => 'Morgan',
        ]);

        // Ensure a balance record exists for context
        UserLeaveBalance::factory()->create([
            'user_id' => $user->id,
            'workspace_id' => $workspace->id,
            'year' => $year,
            'total_annual_leaves' => 15,
            'used_paid_leaves' => 5,
            'remaining_paid_leaves' => 10,
        ]);

        // Older request
        LeaveRequest::factory()->create([
            'user_id' => $user->id,
            'workspace_id' => $workspace->id,
            'status' => 'approved',
            'is_paid' => true,
            'paid_days' => 1,
            'unpaid_days' => 0,
            'from_date' => '2025-01-10',
            'to_date' => '2025-01-10',
            'updated_at' => now()->subDays(3),
        ]);

        // Most recent request
        LeaveRequest::factory()->create([
            'user_id' => $user->id,
            'workspace_id' => $workspace->id,
            'status' => 'approved',
            'is_paid' => true,
            'paid_days' => 2.5,
            'unpaid_days' => 0,
            'from_date' => '2025-02-01',
            'to_date' => '2025-02-03',
            'updated_at' => now(),
        ]);

        $recent = $service->getRecentWorkspaceRequests($workspace->id, $year);

        $this->assertNotEmpty($recent);
        $this->assertSame('Alex Morgan', $recent[0]['user_name']);
        $this->assertStringContainsString('Paid', $recent[0]['balance_impact']);
        $this->assertStringContainsString('2025', $recent[0]['date_range']);
    }
}


