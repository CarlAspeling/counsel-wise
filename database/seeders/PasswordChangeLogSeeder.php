<?php

namespace Database\Seeders;

use App\Models\PasswordChangeLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class PasswordChangeLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Seeds password change logs for development and testing purposes.
     * Creates realistic audit trail data showing various scenarios:
     * - Successful password changes
     * - Failed attempts with different reasons
     * - Recent activity patterns
     * - Multiple users with different activity levels
     */
    public function run(): void
    {
        // Only seed if we have users
        if (User::count() === 0) {
            $this->command->info('No users found. Skipping password change log seeding.');

            return;
        }

        $users = User::take(10)->get();

        foreach ($users as $user) {
            // Create realistic activity patterns for each user

            // Recent successful password change (most users)
            if (fake()->boolean(80)) {
                PasswordChangeLog::factory()
                    ->forUser($user)
                    ->successful()
                    ->recent()
                    ->create();
            }

            // Some failed attempts (shows security monitoring)
            if (fake()->boolean(30)) {
                PasswordChangeLog::factory()
                    ->forUser($user)
                    ->failed('invalid_current_password')
                    ->count(fake()->numberBetween(1, 3))
                    ->create();
            }

            // Historical successful changes
            PasswordChangeLog::factory()
                ->forUser($user)
                ->successful()
                ->count(fake()->numberBetween(2, 8))
                ->create();

            // Occasional rate limiting (shows protection works)
            if (fake()->boolean(10)) {
                PasswordChangeLog::factory()
                    ->forUser($user)
                    ->failed('rate_limited')
                    ->create();
            }
        }

        // Create some IPv6 logs for diversity
        PasswordChangeLog::factory()
            ->withIpv6()
            ->successful()
            ->count(5)
            ->create();

        $this->command->info('Password change logs seeded successfully.');
    }
}
