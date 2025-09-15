<?php

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'surname' => 'Test Surname',
        'email' => 'test@example.com',
        'hpcsa_number' => 'HP123456',
        'account_type' => 'counsellor_free',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('registration requires all fields', function () {
    $response = $this->post('/register', []);

    $response->assertSessionHasErrors([
        'name', 'surname', 'email', 'hpcsa_number', 'account_type', 'password'
    ]);
});

test('registration validates account type', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'surname' => 'Test Surname', 
        'email' => 'test@example.com',
        'hpcsa_number' => 'HP123456',
        'account_type' => 'invalid_type',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors(['account_type']);
});

test('registration validates weak passwords', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'surname' => 'Test Surname',
        'email' => 'test@example.com',
        'hpcsa_number' => 'HP123456',
        'account_type' => 'counsellor_free',
        'password' => 'pass',
        'password_confirmation' => 'pass',
    ]);

    $response->assertSessionHasErrors(['password']);
});

test('registration validates password complexity', function () {
    // Test password without symbols
    $response = $this->post('/register', [
        'name' => 'Test User',
        'surname' => 'Test Surname',
        'email' => 'test1@example.com',
        'hpcsa_number' => 'HP123456',
        'account_type' => 'counsellor_free',
        'password' => 'Password123',
        'password_confirmation' => 'Password123',
    ]);
    $response->assertSessionHasErrors(['password']);

    // Test password without numbers
    $response = $this->post('/register', [
        'name' => 'Test User',
        'surname' => 'Test Surname',
        'email' => 'test2@example.com',
        'hpcsa_number' => 'HP123456',
        'account_type' => 'counsellor_free',
        'password' => 'Password!',
        'password_confirmation' => 'Password!',
    ]);
    $response->assertSessionHasErrors(['password']);

    // Test password without mixed case
    $response = $this->post('/register', [
        'name' => 'Test User',
        'surname' => 'Test Surname',
        'email' => 'test3@example.com',
        'hpcsa_number' => 'HP123456',
        'account_type' => 'counsellor_free',
        'password' => 'password123!',
        'password_confirmation' => 'password123!',
    ]);
    $response->assertSessionHasErrors(['password']);
});
