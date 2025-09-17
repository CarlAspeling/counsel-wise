<?php

use App\Models\PasswordChangeLog;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('password edit page can be displayed', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/password');

    $response->assertStatus(200);
});

test('password can be updated', function () {
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

    $this->assertTrue(Hash::check('UniqueTestPassword123!', $user->refresh()->password));
});

test('correct password must be provided to update password', function () {
    $user = User::factory()->create();

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
});

test('successful password change is logged', function () {
    $user = User::factory()->create();

    // Ensure no existing logs
    $this->assertDatabaseMissing('password_change_logs', ['user_id' => $user->id]);

    $this
        ->actingAs($user)
        ->from('/profile')
        ->put('/password', [
            'current_password' => 'password',
            'password' => 'UniqueTestPassword123!',
            'password_confirmation' => 'UniqueTestPassword123!',
        ]);

    // Verify successful log entry was created
    $this->assertDatabaseHas('password_change_logs', [
        'user_id' => $user->id,
        'success' => true,
        'failure_reason' => null,
    ]);

    // Verify we have exactly one log entry
    $this->assertDatabaseCount('password_change_logs', 1);
});

test('failed password change attempt is logged with correct reason', function () {
    $user = User::factory()->create();

    // Ensure no existing logs
    $this->assertDatabaseMissing('password_change_logs', ['user_id' => $user->id]);

    $this
        ->actingAs($user)
        ->from('/profile')
        ->put('/password', [
            'current_password' => 'wrong-password',
            'password' => 'UniqueTestPassword123!',
            'password_confirmation' => 'UniqueTestPassword123!',
        ]);

    // Verify failed log entry was created with correct reason
    $this->assertDatabaseHas('password_change_logs', [
        'user_id' => $user->id,
        'success' => false,
        'failure_reason' => 'invalid_current_password',
    ]);
});

test('password validation failure is logged with correct reason', function () {
    $user = User::factory()->create();

    $this
        ->actingAs($user)
        ->from('/profile')
        ->put('/password', [
            'current_password' => 'password',
            'password' => 'weak',
            'password_confirmation' => 'weak',
        ]);

    // Verify failed log entry was created with correct reason
    $this->assertDatabaseHas('password_change_logs', [
        'user_id' => $user->id,
        'success' => false,
        'failure_reason' => 'new_password_validation_failed',
    ]);
});

test('password change logs include request metadata', function () {
    $user = User::factory()->create();

    $this
        ->actingAs($user)
        ->from('/profile')
        ->withServerVariables([
            'REMOTE_ADDR' => '192.168.1.100',
            'HTTP_USER_AGENT' => 'Test User Agent',
        ])
        ->put('/password', [
            'current_password' => 'password',
            'password' => 'UniqueTestPassword123!',
            'password_confirmation' => 'UniqueTestPassword123!',
        ]);

    // Verify log includes IP and user agent
    $log = PasswordChangeLog::where('user_id', $user->id)->first();
    expect($log->ip_address)->toBe('192.168.1.100');
    expect($log->user_agent)->toBe('Test User Agent');
    expect($log->attempted_at)->not->toBeNull();
});
