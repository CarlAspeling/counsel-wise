<?php

use App\Models\User;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/profile');

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();
    $originalAccountType = $user->account_type;

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'surname' => 'Updated Surname',
            'email' => 'test@example.com',
            'hpcsa_number' => $user->hpcsa_number,
            'phone_number' => '+27 82 555 1234',
            'gender' => 'female',
            'language' => 'afrikaans',
            'region' => 'gauteng',
            'password' => 'password', // Required for email change
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/verify-email');

    $user->refresh();

    $this->assertSame('Test User', $user->name);
    $this->assertSame('Updated Surname', $user->surname);
    $this->assertSame('test@example.com', $user->email);
    $this->assertSame('+27 82 555 1234', $user->phone_number);
    $this->assertSame('female', $user->gender->value);
    $this->assertSame('afrikaans', $user->language->value);
    $this->assertSame('gauteng', $user->region->value);
    $this->assertSame($originalAccountType, $user->account_type);
    $this->assertNull($user->email_verified_at);
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'surname' => $user->surname,
            'email' => $user->email,
            'hpcsa_number' => $user->hpcsa_number,
            'phone_number' => '+27 81 123 4567',
            'gender' => 'male',
            'language' => 'english',
            'region' => 'western_cape',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $this->assertNotNull($user->refresh()->email_verified_at);
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete('/profile', [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
    $this->assertTrue($user->fresh()->trashed());
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->delete('/profile', [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrorsIn('userDeletion', 'password')
        ->assertRedirect('/profile');

    $this->assertNotNull($user->fresh());
});
