<?php

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Dashboard Access', function () {
    test('unauthenticated users are redirected to login', function () {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    });

    test('authenticated users with active status can access dashboard', function () {
        $user = User::factory()->create([
            'account_type' => AccountType::CounsellorFree,
            'account_status' => AccountStatus::Active,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
    });

    test('authenticated users with pending status cannot access dashboard', function () {
        $user = User::factory()->create([
            'account_type' => AccountType::CounsellorFree,
            'account_status' => AccountStatus::Pending,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect();
        $response->assertSessionHas('error');
    });

    test('authenticated users with suspended status cannot access dashboard', function () {
        $user = User::factory()->create([
            'account_type' => AccountType::CounsellorFree,
            'account_status' => AccountStatus::Suspended,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect();
        $response->assertSessionHas('error');
    });
});

describe('Profile Access', function () {
    test('authenticated users with active status can access profile', function () {
        $user = User::factory()->create([
            'account_type' => AccountType::CounsellorFree,
            'account_status' => AccountStatus::Active,
        ]);

        $response = $this->actingAs($user)->get('/profile');

        $response->assertOk();
    });

    test('authenticated users with pending status cannot access profile', function () {
        $user = User::factory()->create([
            'account_type' => AccountType::CounsellorFree,
            'account_status' => AccountStatus::Pending,
        ]);

        $response = $this->actingAs($user)->get('/profile');

        $response->assertRedirect();
        $response->assertSessionHas('error');
    });
});

describe('Admin Routes Access', function () {
    test('super admin can access admin routes', function () {
        $user = User::factory()->create([
            'account_type' => AccountType::SuperAdmin,
            'account_status' => AccountStatus::Active,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/admin/dashboard');

        $response->assertOk();
    });

    test('counsellor cannot access admin routes', function () {
        $user = User::factory()->create([
            'account_type' => AccountType::CounsellorFree,
            'account_status' => AccountStatus::Active,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/admin/dashboard');

        $response->assertRedirect();
        $response->assertSessionHas('error');
    });

    test('researcher cannot access admin routes', function () {
        $user = User::factory()->create([
            'account_type' => AccountType::Researcher,
            'account_status' => AccountStatus::Active,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/admin/dashboard');

        $response->assertRedirect();
        $response->assertSessionHas('error');
    });

    test('super admin with pending status cannot access admin routes', function () {
        $user = User::factory()->create([
            'account_type' => AccountType::SuperAdmin,
            'account_status' => AccountStatus::Pending,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/admin/dashboard');

        $response->assertRedirect();
        $response->assertSessionHas('error');
    });
});

describe('Research Routes Access', function () {
    test('researcher can access research routes', function () {
        $user = User::factory()->create([
            'account_type' => AccountType::Researcher,
            'account_status' => AccountStatus::Active,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/research/data');

        $response->assertOk();
    });

    test('super admin can access research routes', function () {
        $user = User::factory()->create([
            'account_type' => AccountType::SuperAdmin,
            'account_status' => AccountStatus::Active,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/research/analytics');

        $response->assertOk();
    });

    test('counsellor cannot access research routes', function () {
        $user = User::factory()->create([
            'account_type' => AccountType::CounsellorFree,
            'account_status' => AccountStatus::Active,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/research/data');

        $response->assertRedirect();
        $response->assertSessionHas('error');
    });

    test('student cannot access research routes', function () {
        $user = User::factory()->create([
            'account_type' => AccountType::StudentRc,
            'account_status' => AccountStatus::Active,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/research/analytics');

        $response->assertRedirect();
        $response->assertSessionHas('error');
    });
});

describe('Multiple Role Scenarios', function () {
    test('all account types can access general routes when active', function () {
        $accountTypes = [
            AccountType::CounsellorFree,
            AccountType::CounsellorPaid,
            AccountType::Researcher,
            AccountType::SuperAdmin,
            AccountType::StudentRc,
        ];

        foreach ($accountTypes as $accountType) {
            $user = User::factory()->create([
                'account_type' => $accountType,
                'account_status' => AccountStatus::Active,
                'email_verified_at' => now(),
            ]);

            $response = $this->actingAs($user)->get('/dashboard');
            $response->assertOk();

            $response = $this->actingAs($user)->get('/profile');
            $response->assertOk();
        }
    });

    test('no account types can access routes when not active', function () {
        $accountTypes = [
            AccountType::CounsellorFree,
            AccountType::CounsellorPaid,
            AccountType::Researcher,
            AccountType::SuperAdmin,
            AccountType::StudentRc,
        ];

        $inactiveStatuses = [
            AccountStatus::Pending,
            AccountStatus::Suspended,
            AccountStatus::Deleted,
        ];

        foreach ($accountTypes as $accountType) {
            foreach ($inactiveStatuses as $status) {
                $user = User::factory()->create([
                    'account_type' => $accountType,
                    'account_status' => $status,
                    'email_verified_at' => now(),
                ]);

                $response = $this->actingAs($user)->get('/dashboard');
                $response->assertRedirect();

                $response = $this->actingAs($user)->get('/profile');
                $response->assertRedirect();
            }
        }
    });
});
