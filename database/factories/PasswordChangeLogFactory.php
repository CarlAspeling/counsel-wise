<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PasswordChangeLog>
 */
class PasswordChangeLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $attemptedAt = fake()->dateTimeBetween('-30 days', 'now');
        $success = fake()->boolean(85); // 85% success rate for realistic data

        return [
            'user_id' => User::factory(),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'attempted_at' => $attemptedAt,
            'success' => $success,
            'failure_reason' => $success ? null : fake()->randomElement([
                'invalid_current_password',
                'validation_failed',
                'rate_limited',
                'session_expired',
            ]),
        ];
    }

    /**
     * Create a successful password change log.
     */
    public function successful(): static
    {
        return $this->state(fn (array $attributes) => [
            'success' => true,
            'failure_reason' => null,
        ]);
    }

    /**
     * Create a failed password change log.
     */
    public function failed(?string $reason = null): static
    {
        return $this->state(fn (array $attributes) => [
            'success' => false,
            'failure_reason' => $reason ?? fake()->randomElement([
                'invalid_current_password',
                'validation_failed',
                'rate_limited',
                'session_expired',
            ]),
        ]);
    }

    /**
     * Create a log with IPv6 address.
     */
    public function withIpv6(): static
    {
        return $this->state(fn (array $attributes) => [
            'ip_address' => fake()->ipv6(),
        ]);
    }

    /**
     * Create a recent attempt (within last hour).
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'attempted_at' => fake()->dateTimeBetween('-1 hour', 'now'),
        ]);
    }

    /**
     * Create a log for a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }
}
