<?php

namespace Database\Factories;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\Gender;
use App\Enums\Language;
use App\Enums\SouthAfricanProvince;
use App\Enums\ThemePreference;
use Carbon\Carbon;
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
        $createdAt = fake()->dateTimeBetween('-100 days', 'now');
        $createdAtCarbon = Carbon::parse($createdAt);
        $updatedAt = fake()->dateTimeBetween($createdAt, 'now');
        
        return [
            'name' => fake()->firstName(),
            'surname' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => fake()->dateTimeBetween($createdAt, $createdAtCarbon->copy()->addMinutes(2)),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'hpcsa_number' => fake()->numerify('#######'),
            'hpcsa_verified_at' => fake()->optional(0.5)->dateTimeBetween($createdAt, $createdAtCarbon->copy()->addMinutes(10)),
            'profile_picture' => fake()->optional(0.3)->imageUrl(200, 200, 'people'),
            'account_type' => fake()->randomElement(AccountType::cases())->value,
            'last_login_at' => fake()->optional(0.7)->dateTimeBetween($createdAt, 'now'),
            'account_status' => AccountStatus::Active->value, // Default to Active for testing - production uses database default (Pending)
            'theme_preference' => fake()->optional(0.6)->randomElement(ThemePreference::cases())?->value,
            'data_privacy_consent' => fake()->optional(0.8)->dateTimeBetween($createdAt, $createdAtCarbon->copy()->addMinutes(10)),
            'terms_accepted_at' => fake()->optional(0.9)->dateTimeBetween($createdAt, $createdAtCarbon->copy()->addMinutes(10)),
            'notification_preferences' => fake()->randomElement([
                [],
                ['email' => true, 'sms' => false],
                ['email' => true, 'sms' => true, 'push' => false],
                ['email' => false, 'sms' => true, 'push' => true],
            ]),
            'phone_number' => fake()->optional(0.7)->numerify('##########'),
            'gender' => fake()->optional(0.6)->randomElement(Gender::cases())?->value,
            'language' => fake()->randomElement(Language::cases())->value,
            'region' => fake()->optional(0.8)->randomElement(SouthAfricanProvince::cases())?->value,
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
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
            'account_status' => AccountStatus::Active->value,
            'hpcsa_number' => fake()->numerify('#######'),
        ]);
    }

    /**
     * Create a paid account-based counsellor with HPCSA verification.
     */
    public function paidCounsellor(): static
    {
        return $this->state(fn (array $attributes) => [
            'account_type' => AccountType::CounsellorPaid->value,
            'account_status' => AccountStatus::Active->value,
            'hpcsa_number' => fake()->numerify('#######'),
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
            'account_status' => AccountStatus::Active->value,
            'hpcsa_number' => fake()->numerify('#######'),
        ]);
    }

    /**
     * Create a super admin account.
     */
    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'account_type' => AccountType::SuperAdmin->value,
            'account_status' => AccountStatus::Active->value,
        ]);
    }
}
