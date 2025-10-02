<?php

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    // Clear rate limiter before each test
    RateLimiter::clear('password_change:1|127.0.0.1');
});

afterEach(function () {
    // Clean up rate limiter after each test
    RateLimiter::clear('password_change:1|127.0.0.1');
});

test('password change is allowed within rate limit', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->put('/password', [
            'current_password' => 'password',
            'password' => 'UniqueTestPassword123!',
            'password_confirmation' => 'UniqueTestPassword123!',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');
});

test('rate limit is enforced after 5 failed attempts with wrong current password', function () {
    $user = User::factory()->create();

    // Make 5 failed attempts with wrong current password
    for ($i = 0; $i < 5; $i++) {
        try {
            $this
                ->actingAs($user)
                ->from('/profile')
                ->put('/password', [
                    'current_password' => 'wrong-password',
                    'password' => 'UniqueTestPassword123!',
                    'password_confirmation' => 'UniqueTestPassword123!',
                ]);
        } catch (\Exception $e) {
            // Expected to fail validation
        }
    }

    // 6th attempt should be rate limited
    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->put('/password', [
            'current_password' => 'wrong-password',
            'password' => 'UniqueTestPassword123!',
            'password_confirmation' => 'UniqueTestPassword123!',
        ]);

    $response
        ->assertSessionHasErrorsIn('updatePassword', 'current_password')
        ->assertRedirect('/profile');

    // Check that the error message mentions rate limiting
    $errors = $response->getSession()->get('errors')->getBag('updatePassword')->get('current_password');
    expect($errors[0])->toContain('Too many password change attempts');
});

test('rate limit is enforced after 5 failed attempts with weak passwords', function () {
    $user = User::factory()->create();

    // Make 5 failed attempts with passwords that don't meet requirements
    for ($i = 0; $i < 5; $i++) {
        try {
            $this
                ->actingAs($user)
                ->from('/profile')
                ->put('/password', [
                    'current_password' => 'password',
                    'password' => 'weak', // Fails: no uppercase, no numbers, no special chars, too short
                    'password_confirmation' => 'weak',
                ]);
        } catch (\Exception $e) {
            // Expected to fail validation
        }
    }

    // 6th attempt should be rate limited
    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->put('/password', [
            'current_password' => 'password',
            'password' => 'weak',
            'password_confirmation' => 'weak',
        ]);

    $response
        ->assertSessionHasErrorsIn('updatePassword', 'current_password')
        ->assertRedirect('/profile');

    // Check that the error message mentions rate limiting
    $errors = $response->getSession()->get('errors')->getBag('updatePassword')->get('current_password');
    expect($errors[0])->toContain('Too many password change attempts');
});

test('rate limit applies to mixed validation failure types', function () {
    $user = User::factory()->create();

    // Mix different types of validation failures
    // 2 wrong current password attempts
    for ($i = 0; $i < 2; $i++) {
        try {
            $this
                ->actingAs($user)
                ->from('/profile')
                ->put('/password', [
                    'current_password' => 'wrong-password',
                    'password' => 'UniqueTestPassword123!',
                    'password_confirmation' => 'UniqueTestPassword123!',
                ]);
        } catch (\Exception $e) {
            // Expected to fail
        }
    }

    // 3 weak password attempts
    for ($i = 0; $i < 3; $i++) {
        try {
            $this
                ->actingAs($user)
                ->from('/profile')
                ->put('/password', [
                    'current_password' => 'password',
                    'password' => 'tooshort', // Fails: no uppercase, no numbers, no special chars
                    'password_confirmation' => 'tooshort',
                ]);
        } catch (\Exception $e) {
            // Expected to fail
        }
    }

    // 6th attempt (any type) should be rate limited
    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->put('/password', [
            'current_password' => 'password',
            'password' => 'UniqueTestPassword123!',
            'password_confirmation' => 'UniqueTestPassword123!',
        ]);

    $response
        ->assertSessionHasErrorsIn('updatePassword', 'current_password')
        ->assertRedirect('/profile');

    $errors = $response->getSession()->get('errors')->getBag('updatePassword')->get('current_password');
    expect($errors[0])->toContain('Too many password change attempts');
});

test('rate limit violation is logged', function () {
    $user = User::factory()->create();

    // Make 5 failed attempts to trigger rate limit
    for ($i = 0; $i < 5; $i++) {
        try {
            $this
                ->actingAs($user)
                ->from('/profile')
                ->put('/password', [
                    'current_password' => 'wrong-password',
                    'password' => 'UniqueTestPassword123!',
                    'password_confirmation' => 'UniqueTestPassword123!',
                ]);
        } catch (\Exception $e) {
            // Expected to fail
        }
    }

    // 6th attempt should trigger rate limit logging
    $this
        ->actingAs($user)
        ->from('/profile')
        ->put('/password', [
            'current_password' => 'wrong-password',
            'password' => 'UniqueTestPassword123!',
            'password_confirmation' => 'UniqueTestPassword123!',
        ]);

    // Verify rate limit violation was logged
    $this->assertDatabaseHas('password_change_logs', [
        'user_id' => $user->id,
        'success' => false,
        'failure_reason' => 'rate_limited',
    ]);
});

test('rate limit is cleared on successful password change', function () {
    $user = User::factory()->create();

    // Make 4 failed attempts (just under the limit)
    for ($i = 0; $i < 4; $i++) {
        try {
            $this
                ->actingAs($user)
                ->from('/profile')
                ->put('/password', [
                    'current_password' => 'wrong-password',
                    'password' => 'UniqueTestPassword123!',
                    'password_confirmation' => 'UniqueTestPassword123!',
                ]);
        } catch (\Exception $e) {
            // Expected to fail
        }
    }

    // 5th attempt with correct password should succeed and clear rate limit
    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->put('/password', [
            'current_password' => 'password',
            'password' => 'UniqueTestPassword123!',
            'password_confirmation' => 'UniqueTestPassword123!',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    // Subsequent attempts should not be rate limited (rate limit was cleared)
    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->put('/password', [
            'current_password' => 'UniqueTestPassword123!',
            'password' => 'AnotherStrongPassword456@',
            'password_confirmation' => 'AnotherStrongPassword456@',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');
});

test('rate limit is user and IP specific', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    // Make 5 failed attempts with user1
    for ($i = 0; $i < 5; $i++) {
        try {
            $this
                ->actingAs($user1)
                ->from('/profile')
                ->put('/password', [
                    'current_password' => 'wrong-password',
                    'password' => 'UniqueTestPassword123!',
                    'password_confirmation' => 'UniqueTestPassword123!',
                ]);
        } catch (\Exception $e) {
            // Expected to fail
        }
    }

    // User1 should be rate limited
    $response1 = $this
        ->actingAs($user1)
        ->from('/profile')
        ->put('/password', [
            'current_password' => 'wrong-password',
            'password' => 'UniqueTestPassword123!',
            'password_confirmation' => 'UniqueTestPassword123!',
        ]);

    $errors1 = $response1->getSession()->get('errors')->getBag('updatePassword')->get('current_password');
    expect($errors1[0])->toContain('Too many password change attempts');

    // User2 should NOT be rate limited (different user)
    $response2 = $this
        ->actingAs($user2)
        ->from('/profile')
        ->put('/password', [
            'current_password' => 'password',
            'password' => 'UniqueTestPassword123!',
            'password_confirmation' => 'UniqueTestPassword123!',
        ]);

    $response2
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');
});

test('rate limit only applies to PUT requests', function () {
    $user = User::factory()->create();

    // GET requests should not be rate limited
    $response = $this
        ->actingAs($user)
        ->get('/password');

    $response->assertStatus(200);

    // Even after many GET requests, PUT should still work normally
    for ($i = 0; $i < 10; $i++) {
        $this->actingAs($user)->get('/password')->assertStatus(200);
    }

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->put('/password', [
            'current_password' => 'password',
            'password' => 'UniqueTestPassword123!',
            'password_confirmation' => 'UniqueTestPassword123!',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');
});

test('password requirements are enforced and logged correctly', function () {
    $user = User::factory()->create();

    // Test various password requirement failures
    $weakPasswords = [
        'weak',                     // Too short, no uppercase, no numbers, no symbols
        'nouppercase123!',         // No uppercase
        'NOLOWERCASE123!',         // No lowercase
        'NoNumbers!',              // No numbers
        'NoSymbols123',            // No symbols
        'toolongbutweakpassword',  // Long but weak (no uppercase, numbers, symbols)
    ];

    foreach ($weakPasswords as $weakPassword) {
        // Clear rate limiter before each password test to isolate validation testing
        RateLimiter::clear('password_change:'.$user->id.'|127.0.0.1');

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->put('/password', [
                'current_password' => 'password',
                'password' => $weakPassword,
                'password_confirmation' => $weakPassword,
            ]);

        $response
            ->assertSessionHasErrorsIn('updatePassword', 'password')
            ->assertRedirect('/profile');

        // Verify the failure was logged with correct reason
        $this->assertDatabaseHas('password_change_logs', [
            'user_id' => $user->id,
            'success' => false,
            'failure_reason' => 'new_password_validation_failed',
        ]);
    }
});

test('strong passwords that meet all requirements are accepted', function () {
    $user = User::factory()->create();

    $strongPasswords = [
        'UniqueTestPassword123!',
        'AnotherStrongPass456@',
        'ComplexPassword789#',
        'SecureP@ssw0rd2024',
        'MyStr0ng!P@ssword',
    ];

    foreach ($strongPasswords as $strongPassword) {
        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->put('/password', [
                'current_password' => $user->password === hash('sha256', 'password') ? 'password' : $strongPasswords[array_search($strongPassword, $strongPasswords) - 1] ?? 'password',
                'password' => $strongPassword,
                'password_confirmation' => $strongPassword,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        // Verify successful change was logged
        $this->assertDatabaseHas('password_change_logs', [
            'user_id' => $user->id,
            'success' => true,
            'failure_reason' => null,
        ]);

        // Update user's password for next iteration
        $user->refresh();
    }
});
