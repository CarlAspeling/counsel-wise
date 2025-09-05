<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AccountTypeSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create one user for each account type
        User::factory()->freeCounsellor()->create([
            'email' => 'free-counsellor@example.com',
        ]);

        User::factory()->paidCounsellor()->create([
            'email' => 'paid-counsellor@example.com',
        ]);

        User::factory()->researcher()->create([
            'email' => 'researcher@example.com',
        ]);

        User::factory()->studentRc()->create([
            'email' => 'student-rc@example.com',
        ]);

        User::factory()->superAdmin()->create([
            'email' => 'super-admin@example.com',
        ]);
    }
}
