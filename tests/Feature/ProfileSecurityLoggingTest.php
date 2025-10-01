<?php

use App\Enums\SecurityEventType;
use App\Models\SecurityEventLog;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    SecurityEventLog::truncate();
    RateLimiter::clear('profile-update:*');
});

describe('Profile Update Security Logging', function () {
    test('successful profile update logs security event', function () {
        $user = User::factory()->create();

        $this->actingAs($user)->patch('/profile', [
            'name' => 'Updated Name',
            'surname' => $user->surname,
            'email' => $user->email,
            'hpcsa_number' => $user->hpcsa_number,
        ]);

        $this->assertDatabaseHas('security_event_logs', [
            'event_type' => SecurityEventType::PROFILE_UPDATED->value,
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
        ]);

        $log = SecurityEventLog::where('event_type', SecurityEventType::PROFILE_UPDATED)->first();
        expect($log->metadata)->toHaveKey('email_changed');
        expect($log->metadata['email_changed'])->toBeFalse();
    });

    test('email change logs security event with old and new email', function () {
        $user = User::factory()->create(['email' => 'old@example.com']);

        $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'surname' => $user->surname,
            'email' => 'new@example.com',
            'hpcsa_number' => $user->hpcsa_number,
            'password' => 'password', // Required for email change
        ]);

        $this->assertDatabaseHas('security_event_logs', [
            'event_type' => SecurityEventType::EMAIL_CHANGE_REQUESTED->value,
            'user_id' => $user->id,
        ]);

        $log = SecurityEventLog::where('event_type', SecurityEventType::EMAIL_CHANGE_REQUESTED)->first();
        expect($log->metadata['old_email'])->toBe('old@example.com');
        expect($log->metadata['new_email'])->toBe('new@example.com');
    });

    test('failed profile update logs security event', function () {
        $user = User::factory()->create();
        $existingUser = User::factory()->create(['email' => 'taken@example.com']);

        $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'surname' => $user->surname,
            'email' => 'taken@example.com', // Duplicate email
            'hpcsa_number' => $user->hpcsa_number,
        ]);

        $this->assertDatabaseHas('security_event_logs', [
            'event_type' => SecurityEventType::PROFILE_UPDATE_FAILED->value,
            'user_id' => $user->id,
        ]);
    });

    test('profile update includes changed fields in metadata', function () {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'phone_number' => '+27 81 111 1111',
        ]);

        $this->actingAs($user)->patch('/profile', [
            'name' => 'New Name',
            'surname' => $user->surname,
            'email' => $user->email,
            'hpcsa_number' => $user->hpcsa_number,
            'phone_number' => '+27 82 222 2222',
        ]);

        $log = SecurityEventLog::where('event_type', SecurityEventType::PROFILE_UPDATED)->first();
        expect($log->metadata)->toHaveKey('changed_fields');
        expect($log->metadata['changed_fields'])->toContain('name', 'phone_number');
    });
});

describe('Profile Update Rate Limiting', function () {
    test('profile updates are rate limited after 10 attempts', function () {
        $user = User::factory()->create();
        $maxAttempts = 10;

        // Make max allowed attempts
        for ($i = 0; $i < $maxAttempts; $i++) {
            $response = $this->actingAs($user)->patch('/profile', [
                'name' => "Name $i",
                'surname' => $user->surname,
                'email' => $user->email,
                'hpcsa_number' => $user->hpcsa_number,
            ]);

            $response->assertRedirect('/profile');
        }

        // Next attempt should be rate limited
        $response = $this->actingAs($user)->patch('/profile', [
            'name' => 'Should Be Blocked',
            'surname' => $user->surname,
            'email' => $user->email,
            'hpcsa_number' => $user->hpcsa_number,
        ]);

        $response->assertStatus(429); // Too Many Requests
    });

    test('rate limit cleared after 1 hour', function () {
        $user = User::factory()->create();
        $maxAttempts = 10;

        // Trigger rate limit
        for ($i = 0; $i <= $maxAttempts; $i++) {
            $this->actingAs($user)->patch('/profile', [
                'name' => "Name $i",
                'surname' => $user->surname,
                'email' => $user->email,
                'hpcsa_number' => $user->hpcsa_number,
            ]);
        }

        // Travel 61 minutes into the future
        $this->travel(61)->minutes();

        // Should work now
        $response = $this->actingAs($user)->patch('/profile', [
            'name' => 'After Cooldown',
            'surname' => $user->surname,
            'email' => $user->email,
            'hpcsa_number' => $user->hpcsa_number,
        ]);

        $response->assertRedirect('/profile');
    });

    test('rate limit is per user and IP combination', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $maxAttempts = 10;

        // Exhaust user1's rate limit
        for ($i = 0; $i <= $maxAttempts; $i++) {
            $this->actingAs($user1)->patch('/profile', [
                'name' => "Name $i",
                'surname' => $user1->surname,
                'email' => $user1->email,
                'hpcsa_number' => $user1->hpcsa_number,
            ]);
        }

        // user2 should still be able to update
        $response = $this->actingAs($user2)->patch('/profile', [
            'name' => 'User 2 Update',
            'surname' => $user2->surname,
            'email' => $user2->email,
            'hpcsa_number' => $user2->hpcsa_number,
        ]);

        $response->assertRedirect('/profile');
    });

    test('rate limiting logs security event', function () {
        $user = User::factory()->create();
        $maxAttempts = 10;

        // Trigger rate limit
        for ($i = 0; $i <= $maxAttempts; $i++) {
            $this->actingAs($user)->patch('/profile', [
                'name' => "Name $i",
                'surname' => $user->surname,
                'email' => $user->email,
                'hpcsa_number' => $user->hpcsa_number,
            ]);
        }

        $this->assertDatabaseHas('security_event_logs', [
            'event_type' => SecurityEventType::PROFILE_UPDATE_RATE_LIMITED->value,
            'user_id' => $user->id,
        ]);
    });
});
