<?php

namespace App\Http\Controllers\Auth;

use App\Enums\AccountType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\StatusCodes;
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
        try {
            $request->authenticate();
            $request->session()->regenerate();

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
        } catch (ValidationException $e) {
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
