<?php

namespace Database\Factories;

use App\Enums\SecurityEventType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SecurityEventLog>
 */
class SecurityEventLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $eventType = fake()->randomElement(SecurityEventType::cases());
        $user = fake()->boolean(70) ? User::factory() : null;

        return [
            'event_type' => $eventType,
            'severity' => $eventType->getSeverity(),
            'description' => $eventType->getDescription(),
            'user_id' => $user,
            'email' => $user ? null : fake()->email(),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'country' => fake()->countryCode(),
            'city' => fake()->city(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'metadata' => fake()->boolean(60) ? [
                'additional_info' => fake()->sentence(),
                'browser' => fake()->randomElement(['Chrome', 'Firefox', 'Safari', 'Edge']),
                'device_type' => fake()->randomElement(['desktop', 'mobile', 'tablet']),
            ] : null,
            'session_id' => fake()->uuid(),
            'occurred_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Indicate that the security event is for a successful login.
     */
    public function loginSuccess(): static
    {
        return $this->state(fn (array $attributes) => [
            'event_type' => SecurityEventType::LOGIN_SUCCESS,
            'severity' => SecurityEventType::LOGIN_SUCCESS->getSeverity(),
            'description' => SecurityEventType::LOGIN_SUCCESS->getDescription(),
            'user_id' => User::factory(),
            'email' => null,
        ]);
    }

    /**
     * Indicate that the security event is for a failed login.
     */
    public function loginFailed(): static
    {
        return $this->state(fn (array $attributes) => [
            'event_type' => SecurityEventType::LOGIN_FAILED,
            'severity' => SecurityEventType::LOGIN_FAILED->getSeverity(),
            'description' => SecurityEventType::LOGIN_FAILED->getDescription(),
            'user_id' => null,
        ]);
    }

    /**
     * Indicate that the security event is suspicious activity.
     */
    public function suspiciousActivity(): static
    {
        return $this->state(fn (array $attributes) => [
            'event_type' => SecurityEventType::SUSPICIOUS_ACTIVITY,
            'severity' => SecurityEventType::SUSPICIOUS_ACTIVITY->getSeverity(),
            'description' => SecurityEventType::SUSPICIOUS_ACTIVITY->getDescription(),
            'metadata' => [
                'threat_level' => fake()->randomElement(['low', 'medium', 'high']),
                'suspicious_patterns' => fake()->words(3, true),
                'automated_response' => fake()->boolean(),
            ],
        ]);
    }

    /**
     * Indicate that the security event is from an unusual location.
     */
    public function unusualLocation(): static
    {
        return $this->state(fn (array $attributes) => [
            'event_type' => SecurityEventType::UNUSUAL_LOCATION,
            'severity' => SecurityEventType::UNUSUAL_LOCATION->getSeverity(),
            'description' => SecurityEventType::UNUSUAL_LOCATION->getDescription(),
            'user_id' => User::factory(),
            'email' => null,
            'metadata' => [
                'previous_country' => fake()->countryCode(),
                'distance_km' => fake()->numberBetween(1000, 15000),
                'location_summary' => fake()->sentence(),
            ],
        ]);
    }

    /**
     * Indicate that the security event is recent (within last 24 hours).
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'occurred_at' => fake()->dateTimeBetween('-24 hours', 'now'),
        ]);
    }

    /**
     * Indicate that the security event has critical severity.
     */
    public function critical(): static
    {
        $criticalEvents = [
            SecurityEventType::ACCOUNT_LOCKED,
            SecurityEventType::ACCOUNT_SUSPENDED,
            SecurityEventType::SUSPICIOUS_ACTIVITY,
        ];

        $eventType = fake()->randomElement($criticalEvents);

        return $this->state(fn (array $attributes) => [
            'event_type' => $eventType,
            'severity' => $eventType->getSeverity(),
            'description' => $eventType->getDescription(),
        ]);
    }
}
