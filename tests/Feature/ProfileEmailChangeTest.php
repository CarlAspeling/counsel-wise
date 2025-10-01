<?php

use App\Enums\SecurityEventType;
use App\Models\SecurityEventLog;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    SecurityEventLog::truncate();
    Notification::fake();
});

describe('Email Change Verification', function () {
    test('changing email requires password confirmation', function () {
        $user = User::factory()->create(['email' => 'old@example.com']);

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'surname' => $user->surname,
            'email' => 'new@example.com',
            'hpcsa_number' => $user->hpcsa_number,
            // No password provided
        ]);

        $response->assertSessionHasErrors('password');
    });

    test('changing email with correct password succeeds', function () {
        $user = User::factory()->create(['email' => 'old@example.com']);

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'surname' => $user->surname,
            'email' => 'new@example.com',
            'hpcsa_number' => $user->hpcsa_number,
            'password' => 'password', // Correct password
        ]);

        $response->assertSessionHasNoErrors();
        expect($user->fresh()->email)->toBe('new@example.com');
    });

    test('changing email sends verification email to new address', function () {
        $user = User::factory()->create(['email' => 'old@example.com']);

        $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'surname' => $user->surname,
            'email' => 'new@example.com',
            'hpcsa_number' => $user->hpcsa_number,
            'password' => 'password',
        ]);

        Notification::assertSentTo($user, VerifyEmail::class);
    });

    test('changing email redirects to verification notice', function () {
        $user = User::factory()->create(['email' => 'old@example.com']);

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'surname' => $user->surname,
            'email' => 'new@example.com',
            'hpcsa_number' => $user->hpcsa_number,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('verification.notice'));
        $response->assertSessionHas('status', 'email-changed-verify');
    });

    test('changing email resets email_verified_at', function () {
        $user = User::factory()->create(['email' => 'old@example.com']);
        expect($user->email_verified_at)->not->toBeNull();

        $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'surname' => $user->surname,
            'email' => 'new@example.com',
            'hpcsa_number' => $user->hpcsa_number,
            'password' => 'password',
        ]);

        expect($user->fresh()->email_verified_at)->toBeNull();
    });

    test('updating profile without changing email does not require password', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => 'New Name',
            'surname' => $user->surname,
            'email' => $user->email, // Same email
            'hpcsa_number' => $user->hpcsa_number,
            // No password needed
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/profile');
    });

    test('incorrect password prevents email change', function () {
        $user = User::factory()->create(['email' => 'old@example.com']);

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'surname' => $user->surname,
            'email' => 'new@example.com',
            'hpcsa_number' => $user->hpcsa_number,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('password');
        expect($user->fresh()->email)->toBe('old@example.com');
    });

    test('email change logs security event before verification sent', function () {
        $user = User::factory()->create(['email' => 'old@example.com']);

        $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'surname' => $user->surname,
            'email' => 'new@example.com',
            'hpcsa_number' => $user->hpcsa_number,
            'password' => 'password',
        ]);

        $this->assertDatabaseHas('security_event_logs', [
            'event_type' => SecurityEventType::EMAIL_CHANGE_REQUESTED->value,
            'user_id' => $user->id,
        ]);
    });
});

describe('Email Change Verification UI', function () {
    test('profile page shows verification banner when email unverified', function () {
        $user = User::factory()->create(['email_verified_at' => null]);

        $response = $this->actingAs($user)->get('/profile');

        $response->assertOk();
        // Check Inertia props include mustVerifyEmail flag
        $response->assertInertia(fn ($page) => $page
            ->component('Profile/Edit')
            ->has('mustVerifyEmail')
            ->where('auth.user.email_verified_at', null)
        );
    });
});
