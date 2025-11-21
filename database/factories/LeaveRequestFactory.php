<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Workspace;
use App\Models\LeaveRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveRequestFactory extends Factory
{
    protected $model = LeaveRequest::class;

    public function definition(): array
    {
        $fromDate = $this->faker->dateTimeBetween('now', '+30 days');
        $toDate = (clone $fromDate)->modify('+' . $this->faker->numberBetween(0, 5) . ' days');

        // Database always stores dates in Y-m-d format (MySQL DATE type requirement)
        return [
            'user_id' => User::factory(),
            'workspace_id' => Workspace::factory(),
            'from_date' => $fromDate->format('Y-m-d'),
            'to_date' => $toDate->format('Y-m-d'),
            'from_time' => null,
            'to_time' => null,
            'reason' => $this->faker->sentence(),
            'comment' => null,
            'status' => 'pending',
            'visible_to_all' => 1,
            'action_by' => 0,
            'total_days' => null,
            'paid_days' => null,
            'unpaid_days' => null,
            'is_paid' => null,
        ];
    }

    /**
     * Pending status
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Approved status
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'action_by' => User::factory(),
        ]);
    }

    /**
     * Rejected status
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'action_by' => User::factory(),
        ]);
    }

    /**
     * Paid leave
     */
    public function paid(float $days = null): static
    {
        return $this->state(function (array $attributes) use ($days) {
            $paidDays = $days ?? 2;

            return [
                'status' => 'approved',
                'is_paid' => true,
                'paid_days' => $paidDays,
                'unpaid_days' => 0,
                'total_days' => $paidDays,
            ];
        });
    }

    /**
     * Unpaid leave
     */
    public function unpaid(float $days = null): static
    {
        return $this->state(function (array $attributes) use ($days) {
            $unpaidDays = $days ?? 2;

            return [
                'status' => 'approved',
                'is_paid' => false,
                'paid_days' => 0,
                'unpaid_days' => $unpaidDays,
                'total_days' => $unpaidDays,
            ];
        });
    }

    /**
     * Partial leave (half day)
     */
    public function partial(): static
    {
        return $this->state(fn (array $attributes) => [
            'from_time' => '09:00',
            'to_time' => '13:00',
            'total_days' => 0.5,
        ]);
    }

    /**
     * For specific dates
     * Dates should be in the format expected by the application (d-m-Y by default)
     */
    public function forDates(string $from, string $to): static
    {
        return $this->state(fn (array $attributes) => [
            'from_date' => $from,
            'to_date' => $to,
        ]);
    }
}
