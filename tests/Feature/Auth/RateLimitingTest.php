<?php

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    // Clear all rate limiters before each test
    RateLimiter::clear('login:test@example.com|127.0.0.1');
    RateLimiter::clear('registration:127.0.0.1');
    RateLimiter::clear('password-reset:test@example.com|127.0.0.1');
    RateLimiter::clear('email-verification:127.0.0.1');
});

describe('Login Rate Limiting', function () {
    test('login rate limiting triggers after configured attempts', function () {
        $email = 'test@example.com';
        $maxAttempts = config('auth.rate_limits.login.max_attempts', 5);

        // Make failed attempts up to the limit
        for ($i = 0; $i < $maxAttempts; $i++) {
            $response = $this->postJson('/login', [
                'email' => $email,
                'password' => 'wrongpassword',
            ]);
            $response->assertStatus(422);
        }

        // Next attempt should trigger rate limiting
        $response = $this->postJson('/login', [
            'email' => $email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        $errors = $response->json('errors');
        expect($errors['email'][0])->toContain('Too many');
    });

    test('successful login clears rate limiting', function () {
        $user = User::factory()->create(['email' => 'test@example.com']);
        $email = $user->email;
        $maxAttempts = config('auth.rate_limits.login.max_attempts', 5);

        // Make some failed attempts
        for ($i = 0; $i < $maxAttempts - 1; $i++) {
            $this->postJson('/login', [
                'email' => $email,
                'password' => 'wrongpassword',
            ]);
        }

        // Successful login should clear the rate limit
        $response = $this->postJson('/login', [
            'email' => $email,
            'password' => 'password',
        ]);

        $response->assertStatus(200);

        // Should be able to make failed attempts again
        $response = $this->postJson('/login', [
            'email' => $email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email'])
            ->assertJsonMissing(['Too many']);
    });

    test('login rate limiting uses configurable decay time', function () {
        $email = 'test@example.com';
        $maxAttempts = config('auth.rate_limits.login.max_attempts', 5);
        $decaySeconds = config('auth.rate_limits.login.decay_seconds', 900);

        // Trigger rate limit
        for ($i = 0; $i < $maxAttempts; $i++) {
            $this->postJson('/login', [
                'email' => $email,
                'password' => 'wrongpassword',
            ]);
        }

        // Verify rate limit is active
        $response = $this->postJson('/login', [
            'email' => $email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422);
        $errors = $response->json('errors');
        expect($errors['email'][0])->toContain('Too many');

        // Check that the available time is within expected range
        $availableIn = RateLimiter::availableIn("login:{$email}|127.0.0.1");
        expect($availableIn)->toBeLessThanOrEqual($decaySeconds);
        expect($availableIn)->toBeGreaterThan(0);
    });
});

describe('Registration Rate Limiting', function () {
    test('registration rate limiting triggers after configured attempts', function () {
        $maxAttempts = config('auth.rate_limits.registration.max_attempts', 3);

        // Make failed attempts up to the limit
        for ($i = 0; $i < $maxAttempts; $i++) {
            $response = $this->postJson('/register', [
                'email' => 'invalid-email',
            ]);
            $response->assertStatus(422);
        }

        // Next attempt should trigger rate limiting
        $response = $this->postJson('/register', [
            'name' => 'Test',
            'surname' => 'User',
            'email' => "test{$maxAttempts}@example.com",
            'account_type' => 'general',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        $errors = $response->json('errors');
        expect($errors['email'][0])->toContain('Too many');
    });

    test('successful registration triggers rate limiting', function () {
        $response = $this->postJson('/register', [
            'name' => 'Test',
            'surname' => 'User',
            'email' => 'test@example.com',
            'account_type' => 'general',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201);

        // Check that rate limit counter was incremented
        $attempts = RateLimiter::attempts('registration:127.0.0.1');
        expect($attempts)->toBe(1);
    });

    test('registration rate limiting is IP-based', function () {
        $maxAttempts = config('auth.rate_limits.registration.max_attempts', 3);

        // Simulate different users from same IP
        for ($i = 0; $i < $maxAttempts; $i++) {
            $this->postJson('/register', [
                'email' => 'invalid-email',
            ]);
        }

        // Different valid registration should still be rate limited
        $response = $this->postJson('/register', [
            'name' => 'Test',
            'surname' => 'User',
            'email' => 'newuser@example.com',
            'account_type' => 'general',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422);
        $errors = $response->json('errors');
        expect($errors['email'][0])->toContain('Too many');
    });
});

describe('Password Reset Rate Limiting', function () {
    test('password reset rate limiting triggers after configured attempts', function () {
        $user = User::factory()->create(['email' => 'test@example.com']);
        $maxAttempts = config('auth.rate_limits.password_reset.max_attempts', 3);

        // Make requests up to the limit
        for ($i = 0; $i < $maxAttempts; $i++) {
            $response = $this->postJson('/forgot-password', [
                'email' => $user->email,
            ]);
            $response->assertStatus(200);
        }

        // Next attempt should trigger rate limiting
        $response = $this->postJson('/forgot-password', [
            'email' => $user->email,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        $errors = $response->json('errors');
        expect($errors['email'][0])->toContain('Too many');
    });

    test('password reset rate limiting prevents abuse with nonexistent emails', function () {
        $maxAttempts = config('auth.rate_limits.password_reset.max_attempts', 3);

        // Make failed attempts up to the limit
        for ($i = 0; $i < $maxAttempts; $i++) {
            $response = $this->postJson('/forgot-password', [
                'email' => 'nonexistent@example.com',
            ]);
            $response->assertStatus(422);
        }

        // Next attempt should trigger rate limiting
        $response = $this->postJson('/forgot-password', [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertStatus(422);
        $errors = $response->json('errors');
        expect($errors['email'][0])->toContain('Too many');
    });

    test('password reset rate limiting has shorter decay time', function () {
        $user = User::factory()->create(['email' => 'test@example.com']);
        $maxAttempts = config('auth.rate_limits.password_reset.max_attempts', 3);
        $decaySeconds = config('auth.rate_limits.password_reset.decay_seconds', 300);

        // Trigger rate limit
        for ($i = 0; $i < $maxAttempts; $i++) {
            $this->postJson('/forgot-password', [
                'email' => $user->email,
            ]);
        }

        // Verify shorter decay time
        $availableIn = RateLimiter::availableIn("password-reset:{$user->email}|127.0.0.1");
        expect($availableIn)->toBeLessThanOrEqual($decaySeconds);
        expect($availableIn)->toBeGreaterThan(0);
        expect($decaySeconds)->toBeLessThan(config('auth.rate_limits.login.decay_seconds', 900));
    });
});

describe('Email Verification Rate Limiting', function () {
    test('email verification rate limiting triggers after configured attempts', function () {
        $user = User::factory()->unverified()->create();
        $this->actingAs($user);
        $maxAttempts = config('auth.rate_limits.email_verification.max_attempts', 2);

        // Make requests up to the limit
        for ($i = 0; $i < $maxAttempts; $i++) {
            $response = $this->postJson('/email/verification-notification');
            $response->assertStatus(202);
        }

        // Next attempt should trigger rate limiting
        $response = $this->postJson('/email/verification-notification');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        $errors = $response->json('errors');
        expect($errors['email'][0])->toContain('Too many');
    });

    test('email verification rate limiting is per user and IP', function () {
        $user1 = User::factory()->unverified()->create();
        $user2 = User::factory()->unverified()->create();
        $maxAttempts = config('auth.rate_limits.email_verification.max_attempts', 2);

        // Exhaust rate limit for user1
        $this->actingAs($user1);
        for ($i = 0; $i < $maxAttempts; $i++) {
            $this->postJson('/email/verification-notification');
        }

        // user2 should still be rate limited (IP-based)
        $this->actingAs($user2);
        $response = $this->postJson('/email/verification-notification');

        $response->assertStatus(422);
        $errors = $response->json('errors');
        expect($errors['email'][0])->toContain('Too many');
    });
});

describe('Rate Limiting Configuration', function () {
    test('rate limits use configured values from config file', function () {
        $loginLimit = config('auth.rate_limits.login.max_attempts');
        $registrationLimit = config('auth.rate_limits.registration.max_attempts');
        $passwordResetLimit = config('auth.rate_limits.password_reset.max_attempts');
        $emailVerificationLimit = config('auth.rate_limits.email_verification.max_attempts');

        expect($loginLimit)->toBeGreaterThan(0);
        expect($registrationLimit)->toBeGreaterThan(0);
        expect($passwordResetLimit)->toBeGreaterThan(0);
        expect($emailVerificationLimit)->toBeGreaterThan(0);

        // Verify different endpoints have different limits
        expect($loginLimit)->not->toBe($registrationLimit);
        expect($passwordResetLimit)->toBeLessThanOrEqual($loginLimit);
        expect($emailVerificationLimit)->toBeLessThanOrEqual($registrationLimit);
    });

    test('rate limit decay times are configurable', function () {
        $loginDecay = config('auth.rate_limits.login.decay_seconds');
        $registrationDecay = config('auth.rate_limits.registration.decay_seconds');
        $passwordResetDecay = config('auth.rate_limits.password_reset.decay_seconds');
        $emailVerificationDecay = config('auth.rate_limits.email_verification.decay_seconds');

        expect($loginDecay)->toBeGreaterThan(0);
        expect($registrationDecay)->toBeGreaterThan(0);
        expect($passwordResetDecay)->toBeGreaterThan(0);
        expect($emailVerificationDecay)->toBeGreaterThan(0);

        // Password reset should have shorter decay time
        expect($passwordResetDecay)->toBeLessThanOrEqual($loginDecay);
        expect($emailVerificationDecay)->toBeLessThanOrEqual($registrationDecay);
    });

    test('rate limiting error messages are consistent', function () {
        $responses = [];

        // Trigger rate limiting for each endpoint
        $user = User::factory()->create(['email' => 'test@example.com']);

        // Login rate limiting
        $maxAttempts = config('auth.rate_limits.login.max_attempts', 5);
        for ($i = 0; $i <= $maxAttempts; $i++) {
            $responses['login'] = $this->postJson('/login', [
                'email' => 'test@example.com',
                'password' => 'wrongpassword',
            ]);
        }

        // Registration rate limiting
        RateLimiter::clear('registration:127.0.0.1');
        $maxAttempts = config('auth.rate_limits.registration.max_attempts', 3);
        for ($i = 0; $i <= $maxAttempts; $i++) {
            $responses['registration'] = $this->postJson('/register', [
                'email' => 'invalid-email',
            ]);
        }

        // Password reset rate limiting
        RateLimiter::clear('password-reset:test@example.com|127.0.0.1');
        $maxAttempts = config('auth.rate_limits.password_reset.max_attempts', 3);
        for ($i = 0; $i <= $maxAttempts; $i++) {
            $responses['password_reset'] = $this->postJson('/forgot-password', [
                'email' => $user->email,
            ]);
        }

        // Check that all rate limit messages are user-friendly
        foreach ($responses as $endpoint => $response) {
            if ($response->status() === 422) {
                $errors = $response->json('errors');
                if (isset($errors['email'][0])) {
                    expect($errors['email'][0])->toContain('Too many');
                    expect($errors['email'][0])->toContain('security');
                }
            }
        }
    });
});
