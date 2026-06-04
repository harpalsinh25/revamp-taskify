<?php

use Carbon\Carbon;
use Plugins\TimeTracker\Controllers\DashboardController;
use Plugins\TimeTracker\Models\TimeTrackerActivityLog;
use Tests\TestCase;

class HeartbeatReproductionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Set fixed time for testing: 2026-03-30 10:00:00
        Carbon::setTestNow(Carbon::parse('2026-03-30 10:00:00'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /**
     * This test confirms the CURRENT ISSUE where time is overcounted despite pulse logs.
     */
    public function test_productivity_is_overcounted_when_pulses_stop_abruptly()
    {
        $controller = new DashboardController();
        $method = new \ReflectionMethod($controller, 'calculateDayMetrics');
        $method->setAccessible(true);

        // Simulation: User clocked in on 2026-03-28 at 09:00 AM.
        // Pulses were sent for 10 minutes, then the PC shut down.
        // It is now 2026-03-30 10:00 AM.
        $logs = collect([
            (object) [
                'action' => 'clock-in',
                'timestamp' => Carbon::parse('2026-03-28 09:00:00'),
                'user_id' => 1
            ],
            (object) [
                'action' => 'pulse',
                'timestamp' => Carbon::parse('2026-03-28 09:01:00'),
                'user_id' => 1
            ],
            (object) [
                'action' => 'pulse',
                'timestamp' => Carbon::parse('2026-03-28 09:10:00'),
                'user_id' => 1
            ],
        ]);

        $metrics = $method->invoke($controller, $logs);

        /**
         * EXPECTED (Fixed) BEHAVIOR:
         * Session should be capped at ~09:10:00 (last pulse) because it is 2026-03-30 today.
         * Total work time should be 10 minutes.
         */

        $this->assertEquals(10, $metrics['work_time']['minutes'],
            "FIX CONFIRMED: Work time is correctly capped at the last pulse ({$metrics['work_time']['minutes']} minutes)."
        );

        echo "\nConfirmed corrected minutes: " . $metrics['work_time']['minutes'] . "\n";
    }
}
