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
        'password' => 'password',
        'password_confirmation' => 'password',
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
