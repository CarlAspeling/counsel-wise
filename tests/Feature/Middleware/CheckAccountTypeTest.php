<?php

use App\Enums\AccountType;
use App\Http\Middleware\CheckAccountType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->middleware = new CheckAccountType;
});

test('unauthenticated user receives unauthorized response', function () {
    $request = Request::create('/test');
    $next = fn ($req) => new Response('OK', 200);

    $response = $this->middleware->handle($request, $next, 'super_admin');

    expect($response->getStatusCode())->toBe(302); // Redirect for web
});

test('authenticated user with correct role passes through', function () {
    $user = User::factory()->create(['account_type' => AccountType::SuperAdmin]);
    Auth::login($user);

    $request = Request::create('/test');
    $next = fn ($req) => new Response('OK', 200);

    $response = $this->middleware->handle($request, $next, 'super_admin');

    expect($response->getStatusCode())->toBe(200);
    expect($response->getContent())->toBe('OK');
});

test('authenticated user with incorrect role receives unauthorized response', function () {
    $user = User::factory()->create(['account_type' => AccountType::CounsellorFree]);
    Auth::login($user);

    $request = Request::create('/test');
    $next = fn ($req) => new Response('OK', 200);

    $response = $this->middleware->handle($request, $next, 'super_admin');

    expect($response->getStatusCode())->toBe(302); // Redirect for web
});

test('authenticated user with multiple allowed roles passes through', function () {
    $user = User::factory()->create(['account_type' => AccountType::Researcher]);
    Auth::login($user);

    $request = Request::create('/test');
    $next = fn ($req) => new Response('OK', 200);

    $response = $this->middleware->handle($request, $next, 'super_admin,researcher');

    expect($response->getStatusCode())->toBe(200);
    expect($response->getContent())->toBe('OK');
});

test('api request receives json response for unauthorized access', function () {
    $user = User::factory()->create(['account_type' => AccountType::CounsellorFree]);
    Auth::login($user);

    $request = Request::create('/api/test', 'GET');
    $request->headers->set('Accept', 'application/json');
    $next = fn ($req) => new Response('OK', 200);

    $response = $this->middleware->handle($request, $next, 'super_admin');

    expect($response->getStatusCode())->toBe(403);
    $data = json_decode($response->getContent(), true);
    expect($data['error'])->toBe('Unauthorized');
    expect($data['message'])->toContain('Insufficient permissions');
});

test('middleware works without roles specified', function () {
    $user = User::factory()->create(['account_type' => AccountType::CounsellorFree]);
    Auth::login($user);

    $request = Request::create('/test');
    $next = fn ($req) => new Response('OK', 200);

    $response = $this->middleware->handle($request, $next);

    expect($response->getStatusCode())->toBe(200);
    expect($response->getContent())->toBe('OK');
});

test('comma separated roles are parsed correctly', function () {
    $user = User::factory()->create(['account_type' => AccountType::CounsellorPaid]);
    Auth::login($user);

    $request = Request::create('/test');
    $next = fn ($req) => new Response('OK', 200);

    $response = $this->middleware->handle($request, $next, 'super_admin,counsellor_paid,researcher');

    expect($response->getStatusCode())->toBe(200);
    expect($response->getContent())->toBe('OK');
});
