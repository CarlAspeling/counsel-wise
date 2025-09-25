<?php

namespace App\Http\Controllers\Auth;

use App\Enums\AccountType;
use App\Exceptions\RateLimitExceededException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\StatusCodes;
use App\Models\SecurityEventLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
            'routes' => [
                'login' => route('login'),
                'password.request' => Route::has('password.request') ? route('password.request') : null,
                'register' => route('register'),
            ],
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse|JsonResponse
    {
        $startTime = microtime(true);

        try {
            $request->authenticate();
            $request->session()->regenerate();

            // Log successful login security event
            SecurityEventLog::logLoginSuccess(Auth::user(), [
                'user_agent' => $request->userAgent(),
                'account_type' => Auth::user()->account_type->value,
                'response_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ]);

            // Determine the redirect URL based on user role
            $redirectUrl = $this->getRedirectUrl();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => trans('auth.login_successful'),
                    'redirect' => $redirectUrl,
                ], StatusCodes::OK);
            }

            // For super admins, always redirect to admin dashboard regardless of intended URL
            if (Auth::user()->account_type === AccountType::SuperAdmin) {
                return redirect($redirectUrl);
            }

            return redirect()->intended($redirectUrl);
        } catch (RateLimitExceededException $e) {
            // Handle rate limiting separately - no need to log failed login as it's already logged in the request
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => trans('auth.throttle_exceeded'),
                    'errors' => $e->getErrors(),
                ], StatusCodes::RATE_LIMITED)
                ->header('Retry-After', $e->getRetryAfterSeconds());
            }

            // For web requests, redirect with validation errors
            throw ValidationException::withMessages($e->getErrors());
        } catch (ValidationException $e) {
            // Log failed login security event
            SecurityEventLog::logLoginFailed($request->input('email'), [
                'user_agent' => $request->userAgent(),
                'validation_errors' => array_keys($e->errors()),
                'response_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => trans('auth.failed'),
                    'errors' => $e->errors(),
                ], StatusCodes::AUTH_FAILED);
            }

            throw $e;
        }
    }

    /**
     * Get the redirect URL based on the authenticated user's account type.
     */
    private function getRedirectUrl(): string
    {
        $user = Auth::user();

        // Redirect super admins to the admin dashboard
        if ($user->account_type === AccountType::SuperAdmin) {
            return '/admin/dashboard';
        }

        // Default redirect for all other users
        return '/dashboard';
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse|JsonResponse
    {
        $startTime = microtime(true);
        $user = Auth::user();

        // Log logout security event before logging out
        if ($user) {
            SecurityEventLog::logLogout($user, [
                'user_agent' => $request->userAgent(),
                'account_type' => $user->account_type->value,
                'response_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ]);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => trans('auth.logout_successful'),
            ], StatusCodes::OK);
        }

        return redirect('/');
    }
}
