<?php

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    $this->actingAs(User::factory()->superAdmin()->create());
});

it('returns active throttles when rate limits exist', function () {
    // Simulate some rate limits
    RateLimiter::hit('login:test@example.com|127.0.0.1', 60);
    RateLimiter::hit('login:test@example.com|127.0.0.1', 60);
    RateLimiter::hit('registration:test2@example.com|127.0.0.1', 60);

    $response = $this->getJson('/admin/rate-limits/active');

    $response->assertSuccessful();

    $throttles = $response->json();
    expect($throttles)->toBeArray();
});

it('returns empty array when no rate limits exist', function () {
    $response = $this->getJson('/admin/rate-limits/active');

    $response->assertSuccessful();

    $throttles = $response->json();
    expect($throttles)->toBeArray()
        ->and($throttles)->toBeEmpty();
});
