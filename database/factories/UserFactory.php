<?php

namespace Database\Factories;

use App\Enums\AccountType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->firstName(),
            'surname' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'hpcsa_number' => fake()->numerify('#######'),
            'hpcsa_verified_at' => fake()->optional(0.5)->dateTimeBetween('-1 year', 'now'),
            'profile_picture' => fake()->optional(0.3)->imageUrl(200, 200, 'people'),
            'account_type' => fake()->randomElement(AccountType::cases())->value,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Create a free account-based counsellor with HPCSA verification.
     */
    public function freeCounsellor(): static
    {
        return $this->state(fn (array $attributes) => [
            'account_type' => AccountType::CounsellorFree->value,
            'hpcsa_number' => fake()->numerify('#######'),
            'hpcsa_verified_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ]);
    }

    /**
     * Create a paid account-based counsellor with HPCSA verification.
     */
    public function paidCounsellor(): static
    {
        return $this->state(fn (array $attributes) => [
            'account_type' => AccountType::CounsellorPaid->value,
            'hpcsa_number' => fake()->numerify('#######'),
            'hpcsa_verified_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ]);
    }

    /**
     * Create a researcher account.
     */
    public function researcher(): static
    {
        return $this->state(fn (array $attributes) => [
            'account_type' => AccountType::Researcher->value,
            'hpcsa_number' => null,
            'hpcsa_verified_at' => null,
        ]);
    }

    /**
     * Create a paid account-based student counsellor account.
     */
    public function studentRc(): static
    {
        return $this->state(fn (array $attributes) => [
            'account_type' => AccountType::StudentRc->value,
            'hpcsa_number' => fake()->numerify('#######'),
            'hpcsa_verified_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ]);
    }

    /**
     * Create a super admin account.
     */
    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'account_type' => AccountType::SuperAdmin->value,
        ]);
    }
}
