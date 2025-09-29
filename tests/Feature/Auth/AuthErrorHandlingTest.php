<?php

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    // Clear rate limiters before each test
    RateLimiter::clear('login:test@example.com|127.0.0.1');
    RateLimiter::clear('registration:127.0.0.1');
    RateLimiter::clear('password-reset:test@example.com|127.0.0.1');
    RateLimiter::clear('email-verification:127.0.0.1');
});

describe('Login Error Handling', function () {
    test('login with invalid email returns 422 with proper error message', function () {
        $response = $this->postJson('/login', [
            'email' => 'invalid-email',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        $errors = $response->json('errors');
        expect($errors['email'][0])->toContain('valid email');
    });

    test('login with missing email returns 422', function () {
        $response = $this->postJson('/login', [
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    test('login with invalid credentials returns 401 with failed message', function () {
        $response = $this->postJson('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJsonValidationErrors(['email']);
    });

    test('login with rate limited IP returns 429', function () {
        $email = 'test@example.com';
        $maxAttempts = config('auth.rate_limits.login.max_attempts', 5);

        // Trigger rate limit
        for ($i = 0; $i < $maxAttempts; $i++) {
            $this->postJson('/login', [
                'email' => $email,
                'password' => 'wrongpassword',
            ]);
        }

        $response = $this->postJson('/login', [
            'email' => $email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(429)
            ->assertJsonValidationErrors(['email']);
    });

    test('web login redirects properly on validation error', function () {
        $response = $this->post('/login', [
            'email' => 'invalid-email',
            'password' => 'password123',
        ]);

        $response->assertRedirect()
            ->assertSessionHasErrors(['email']);
    });
});

describe('Registration Error Handling', function () {
    test('registration with invalid email returns 422', function () {
        $response = $this->postJson('/register', [
            'name' => 'Test',
            'surname' => 'User',
            'email' => 'invalid-email',
            'account_type' => 'general',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    test('registration with existing email returns 422', function () {
        $existingUser = User::factory()->create();

        $response = $this->postJson('/register', [
            'name' => 'Test',
            'surname' => 'User',
            'email' => $existingUser->email,
            'account_type' => 'general',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    test('registration with short password returns 422', function () {
        $response = $this->postJson('/register', [
            'name' => 'Test',
            'surname' => 'User',
            'email' => 'test@example.com',
            'account_type' => 'general',
            'password' => '123',
            'password_confirmation' => '123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    });

    test('registration with mismatched password confirmation returns 422', function () {
        $response = $this->postJson('/register', [
            'name' => 'Test',
            'surname' => 'User',
            'email' => 'test@example.com',
            'account_type' => 'general',
            'password' => 'password123',
            'password_confirmation' => 'different123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    });

    test('registration with missing required fields returns 422', function () {
        $response = $this->postJson('/register', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'surname', 'email', 'account_type', 'password']);
    });

    test('web registration redirects properly on validation error', function () {
        $response = $this->post('/register', [
            'email' => 'invalid-email',
        ]);

        $response->assertRedirect()
            ->assertSessionHasErrors();
    });
});

describe('Password Reset Error Handling', function () {
    test('password reset with invalid email returns 422', function () {
        $response = $this->postJson('/forgot-password', [
            'email' => 'invalid-email',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    test('password reset with nonexistent email returns 422', function () {
        $response = $this->postJson('/forgot-password', [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    test('password reset rate limiting returns proper error', function () {
        $user = User::factory()->create();
        $maxAttempts = config('auth.rate_limits.password_reset.max_attempts', 3);

        // Trigger rate limit
        for ($i = 0; $i < $maxAttempts; $i++) {
            $this->postJson('/forgot-password', [
                'email' => $user->email,
            ]);
        }

        $response = $this->postJson('/forgot-password', [
            'email' => $user->email,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    test('new password with invalid token returns 422', function () {
        $response = $this->postJson('/reset-password', [
            'token' => 'invalid-token',
            'email' => 'test@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(422);
    });
});

describe('Email Verification Error Handling', function () {
    test('verification notification rate limiting returns proper error', function () {
        $user = User::factory()->unverified()->create();
        $this->actingAs($user);

        $maxAttempts = config('auth.rate_limits.email_verification.max_attempts', 2);

        // Trigger rate limit
        for ($i = 0; $i < $maxAttempts; $i++) {
            $this->postJson('/email/verification-notification');
        }

        $response = $this->postJson('/email/verification-notification');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    test('verification with invalid signature returns 403', function () {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get("/verify-email/{$user->id}/invalid-hash");

        $response->assertStatus(403);
    });
});

describe('HTTP Status Code Validation', function () {
    test('successful login returns 200', function () {
        $user = User::factory()->create();

        $response = $this->postJson('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200);
    });

    test('successful registration returns 201', function () {
        $response = $this->postJson('/register', [
            'name' => 'Test',
            'surname' => 'User',
            'email' => 'test@example.com',
            'hpcsa_number' => 'HP12345',
            'account_type' => 'counsellor_free',
            'password' => 'UniqueTestP@ss2024!',
            'password_confirmation' => 'UniqueTestP@ss2024!',
        ]);

        $response->assertStatus(201);
    });

    test('successful logout returns 200', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson('/logout');

        $response->assertStatus(200);
    });
});

describe('Error Message Accuracy', function () {
    test('login validation errors contain proper attribute names', function () {
        $response = $this->postJson('/login', [
            'email' => '',
            'password' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);

        $errors = $response->json('errors');
        expect($errors['email'][0])->toContain('Email');
        expect($errors['password'][0])->toContain('Password');
    });

    test('registration validation errors use translated attribute names', function () {
        $response = $this->postJson('/register', [
            'name' => '',
            'surname' => '',
            'email' => 'invalid',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'surname', 'email']);
    });

    test('rate limit error messages are user-friendly', function () {
        $email = 'test@example.com';
        $maxAttempts = config('auth.rate_limits.login.max_attempts', 5);

        // Trigger rate limit
        for ($i = 0; $i < $maxAttempts; $i++) {
            $this->postJson('/login', [
                'email' => $email,
                'password' => 'wrongpassword',
            ]);
        }

        $response = $this->postJson('/login', [
            'email' => $email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(429);
        $errors = $response->json('errors');
        expect($errors['email'][0])->toContain('Too many');
    });
});
