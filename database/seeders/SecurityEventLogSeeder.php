<?php

namespace Database\Seeders;

use App\Models\SecurityEventLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class SecurityEventLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create diverse security events for testing

        // Recent login successes (70% of events)
        SecurityEventLog::factory()
            ->count(35)
            ->loginSuccess()
            ->recent()
            ->create();

        // Failed login attempts (15% of events)
        SecurityEventLog::factory()
            ->count(8)
            ->loginFailed()
            ->create();

        // Suspicious activities (10% of events)
        SecurityEventLog::factory()
            ->count(5)
            ->suspiciousActivity()
            ->create();

        // Unusual location events (3% of events)
        SecurityEventLog::factory()
            ->count(2)
            ->unusualLocation()
            ->create();

        // Mixed critical events (2% of events)
        SecurityEventLog::factory()
            ->count(1)
            ->critical()
            ->create();

        // Historical events (older data for trend analysis)
        SecurityEventLog::factory()
            ->count(20)
            ->create();

        // Ensure we have some events for existing users if they exist
        if (User::count() > 0) {
            $users = User::limit(3)->get();

            foreach ($users as $user) {
                // Create login history for each user
                SecurityEventLog::factory()
                    ->count(5)
                    ->loginSuccess()
                    ->state(['user_id' => $user->id, 'email' => $user->email])
                    ->create();

                // Add some failed attempts
                SecurityEventLog::factory()
                    ->count(2)
                    ->loginFailed()
                    ->state(['user_id' => null, 'email' => $user->email])
                    ->create();
            }
        }
    }
}
