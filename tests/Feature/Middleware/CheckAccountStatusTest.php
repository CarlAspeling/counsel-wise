<?php

use App\Enums\AccountStatus;
use App\Http\Middleware\CheckAccountStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->middleware = new CheckAccountStatus;
});

test('unauthenticated user receives unauthorized response', function () {
    $request = Request::create('/test');
    $next = fn ($req) => new Response('OK', 200);

    $response = $this->middleware->handle($request, $next);

    expect($response->getStatusCode())->toBe(302); // Redirect for web
});

test('authenticated user with active status passes through', function () {
    $user = User::factory()->create(['account_status' => AccountStatus::Active]);
    Auth::login($user);

    $request = Request::create('/test');
    $next = fn ($req) => new Response('OK', 200);

    $response = $this->middleware->handle($request, $next);

    expect($response->getStatusCode())->toBe(200);
    expect($response->getContent())->toBe('OK');
});

test('authenticated user with pending status receives unauthorized response', function () {
    $user = User::factory()->create(['account_status' => AccountStatus::Pending]);
    Auth::login($user);

    $request = Request::create('/test');
    $next = fn ($req) => new Response('OK', 200);

    $response = $this->middleware->handle($request, $next);

    expect($response->getStatusCode())->toBe(302); // Redirect for web
});

test('authenticated user with suspended status receives unauthorized response', function () {
    $user = User::factory()->create(['account_status' => AccountStatus::Suspended]);
    Auth::login($user);

    $request = Request::create('/test');
    $next = fn ($req) => new Response('OK', 200);

    $response = $this->middleware->handle($request, $next);

    expect($response->getStatusCode())->toBe(302); // Redirect for web
});

test('authenticated user with deleted status receives unauthorized response', function () {
    $user = User::factory()->create(['account_status' => AccountStatus::Deleted]);
    Auth::login($user);

    $request = Request::create('/test');
    $next = fn ($req) => new Response('OK', 200);

    $response = $this->middleware->handle($request, $next);

    expect($response->getStatusCode())->toBe(302); // Redirect for web
});

test('api request receives json response for pending status', function () {
    $user = User::factory()->create(['account_status' => AccountStatus::Pending]);
    Auth::login($user);

    $request = Request::create('/api/test', 'GET');
    $request->headers->set('Accept', 'application/json');
    $next = fn ($req) => new Response('OK', 200);

    $response = $this->middleware->handle($request, $next);

    expect($response->getStatusCode())->toBe(403);
    $data = json_decode($response->getContent(), true);
    expect($data['error'])->toBe('Account Status Error');
    expect($data['message'])->toContain('pending approval');
});

test('api request receives json response for suspended status', function () {
    $user = User::factory()->create(['account_status' => AccountStatus::Suspended]);
    Auth::login($user);

    $request = Request::create('/api/test', 'GET');
    $request->headers->set('Accept', 'application/json');
    $next = fn ($req) => new Response('OK', 200);

    $response = $this->middleware->handle($request, $next);

    expect($response->getStatusCode())->toBe(403);
    $data = json_decode($response->getContent(), true);
    expect($data['error'])->toBe('Account Status Error');
    expect($data['message'])->toContain('suspended');
});

test('middleware accepts custom status parameters', function () {
    $user = User::factory()->create(['account_status' => AccountStatus::Pending]);
    Auth::login($user);

    $request = Request::create('/test');
    $next = fn ($req) => new Response('OK', 200);

    $response = $this->middleware->handle($request, $next, 'pending,active');

    expect($response->getStatusCode())->toBe(200);
    expect($response->getContent())->toBe('OK');
});

test('comma separated statuses are parsed correctly', function () {
    $user = User::factory()->create(['account_status' => AccountStatus::Active]);
    Auth::login($user);

    $request = Request::create('/test');
    $next = fn ($req) => new Response('OK', 200);

    $response = $this->middleware->handle($request, $next, 'active,pending');

    expect($response->getStatusCode())->toBe(200);
    expect($response->getContent())->toBe('OK');
});
