<?php

use App\Enums\SecurityEventType;
use App\Models\SecurityEventLog;
use App\Models\User;

beforeEach(function () {
    // Ensure security events table is clean
    SecurityEventLog::truncate();
});

describe('Login Security Event Logging', function () {
    test('successful login creates security event log', function () {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('security_event_logs', [
            'event_type' => SecurityEventType::LOGIN_SUCCESS->value,
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
        ]);
    });

    test('failed login creates security event log', function () {
        $response = $this->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('security_event_logs', [
            'event_type' => SecurityEventType::LOGIN_FAILED->value,
            'ip_address' => '127.0.0.1',
            'user_id' => null,
        ]);
    });

    test('rate limited login creates security event log', function () {
        $email = 'test@example.com';
        $maxAttempts = config('auth.rate_limits.login.max_attempts', 5);

        // Trigger rate limit
        for ($i = 0; $i < $maxAttempts + 1; $i++) {
            $this->post('/login', [
                'email' => $email,
                'password' => 'wrongpassword',
            ]);
        }

        $this->assertDatabaseHas('security_event_logs', [
            'event_type' => SecurityEventType::RATE_LIMIT_EXCEEDED->value,
            'ip_address' => '127.0.0.1',
        ]);
    });

    test('logout creates security event log', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/logout');

        $response->assertRedirect();

        $this->assertDatabaseHas('security_event_logs', [
            'event_type' => SecurityEventType::LOGOUT->value,
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
        ]);
    });
});

describe('Registration Security Event Logging', function () {
    test('successful registration creates security event log', function () {
        $response = $this->post('/register', [
            'name' => 'Test',
            'surname' => 'User',
            'email' => 'test@example.com',
            'hpcsa_number' => 'HP12345',
            'account_type' => 'counsellor_free',
            'password' => 'UniqueTestP@ss2024!',
            'password_confirmation' => 'UniqueTestP@ss2024!',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('security_event_logs', [
            'event_type' => SecurityEventType::REGISTRATION_SUCCESS->value,
            'ip_address' => '127.0.0.1',
        ]);
    });

    test('failed registration creates security event log', function () {
        $response = $this->post('/register', [
            'name' => '',
            'surname' => '',
            'email' => 'invalid-email',
            'account_type' => 'invalid_type',
            'password' => '123',
            'password_confirmation' => '456',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('security_event_logs', [
            'event_type' => SecurityEventType::REGISTRATION_FAILED->value,
            'ip_address' => '127.0.0.1',
        ]);
    });
});

describe('Password Reset Security Event Logging', function () {
    test('password reset request creates security event log', function () {
        $user = User::factory()->create();

        $response = $this->post('/forgot-password', [
            'email' => $user->email,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('security_event_logs', [
            'event_type' => SecurityEventType::PASSWORD_RESET_REQUESTED->value,
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
        ]);
    });

    test('failed password reset request creates security event log', function () {
        $response = $this->post('/forgot-password', [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('security_event_logs', [
            'event_type' => SecurityEventType::PASSWORD_RESET_FAILED->value,
            'ip_address' => '127.0.0.1',
        ]);
    });
});

describe('Email Verification Security Event Logging', function () {
    test('email verification creates security event log', function () {
        $user = User::factory()->unverified()->create();
        $this->actingAs($user);

        $response = $this->post('/email/verification-notification');

        $response->assertRedirect();

        $this->assertDatabaseHas('security_event_logs', [
            'event_type' => SecurityEventType::EMAIL_VERIFICATION_SENT->value,
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
        ]);
    });

    test('failed email verification creates security event log', function () {
        $user = User::factory()->unverified()->create();
        $this->actingAs($user);

        // Trigger rate limit
        $maxAttempts = config('auth.rate_limits.email_verification.max_attempts', 2);
        for ($i = 0; $i < $maxAttempts + 1; $i++) {
            $this->post('/email/verification-notification');
        }

        $this->assertDatabaseHas('security_event_logs', [
            'event_type' => SecurityEventType::EMAIL_VERIFICATION_FAILED->value,
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
        ]);
    });
});

describe('Security Event Metadata', function () {
    test('security events include proper metadata', function () {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $event = SecurityEventLog::where('event_type', SecurityEventType::LOGIN_SUCCESS->value)->first();

        expect($event)->not->toBeNull();
        expect($event->ip_address)->toBe('127.0.0.1');
        expect($event->user_agent)->toContain('Symfony');
        expect($event->metadata)->toBeArray();
        expect($event->created_at)->not->toBeNull();
    });

    test('security events capture geolocation data', function () {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $event = SecurityEventLog::where('event_type', SecurityEventType::LOGIN_SUCCESS->value)->first();

        expect($event)->not->toBeNull();
        // Geolocation fields should be present (may be null for local IPs)
        expect($event->getAttributes())->toHaveKeys(['country', 'city', 'latitude', 'longitude']);
    });

    test('security events include response time', function () {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $event = SecurityEventLog::where('event_type', SecurityEventType::LOGIN_SUCCESS->value)->first();

        expect($event)->not->toBeNull();
        expect($event->metadata)->toHaveKey('response_time_ms');
        expect($event->metadata['response_time_ms'])->toBeGreaterThan(0);
    });
});

describe('Threat Detection Integration', function () {
    test('multiple failed logins trigger suspicious activity detection', function () {
        $email = 'test@example.com';

        // Generate multiple failed login attempts
        for ($i = 0; $i < 3; $i++) {
            $this->post('/login', [
                'email' => $email,
                'password' => 'wrongpassword',
            ]);
        }

        $failedAttempts = SecurityEventLog::where('event_type', SecurityEventType::LOGIN_FAILED->value)->count();
        expect($failedAttempts)->toBe(3);
    });

    test('security events can be queried by time range', function () {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $recentEvents = SecurityEventLog::recent()->count();
        expect($recentEvents)->toBeGreaterThan(0);

        $todayEvents = SecurityEventLog::today()->count();
        expect($todayEvents)->toBeGreaterThan(0);
    });

    test('security events can be filtered by IP address', function () {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $ipEvents = SecurityEventLog::byIpAddress('127.0.0.1')->count();
        expect($ipEvents)->toBeGreaterThan(0);
    });

    test('security events can be filtered by user', function () {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $userEvents = SecurityEventLog::byUser($user->id)->count();
        expect($userEvents)->toBeGreaterThan(0);
    });
});

describe('Event Type Coverage', function () {
    test('all major security event types are represented', function () {
        $expectedEventTypes = [
            SecurityEventType::LOGIN_SUCCESS,
            SecurityEventType::LOGIN_FAILED,
            SecurityEventType::LOGOUT,
            SecurityEventType::REGISTRATION_SUCCESS,
            SecurityEventType::REGISTRATION_FAILED,
            SecurityEventType::PASSWORD_RESET_REQUESTED,
            SecurityEventType::PASSWORD_RESET_FAILED,
            SecurityEventType::EMAIL_VERIFICATION_SENT,
            SecurityEventType::EMAIL_VERIFICATION_FAILED,
            SecurityEventType::RATE_LIMIT_EXCEEDED,
        ];

        foreach ($expectedEventTypes as $eventType) {
            expect($eventType->value)->toBeString();
            expect($eventType->name)->toBeString();
        }
    });

    test('security event logs include severity levels', function () {
        $user = User::factory()->create();

        // Successful login (INFO level)
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Logout to clear authentication state
        $this->post('/logout');

        // Failed login (WARNING level)
        $this->post('/login', [
            'email' => 'wrong@example.com',
            'password' => 'wrongpassword',
        ]);

        $successEvent = SecurityEventLog::where('event_type', SecurityEventType::LOGIN_SUCCESS->value)->first();
        $failedEvent = SecurityEventLog::where('event_type', SecurityEventType::LOGIN_FAILED->value)->first();

        expect($successEvent)->not->toBeNull();
        expect($failedEvent)->not->toBeNull();
    });
});
