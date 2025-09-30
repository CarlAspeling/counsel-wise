<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\PasswordResetRequest;
use App\Http\StatusCodes;
use App\Models\SecurityEventLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/ForgotPassword', [
            'routes' => [
                'password.email' => route('password.email'),
            ],
        ]);
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(PasswordResetRequest $request): RedirectResponse|JsonResponse
    {
        try {
            $validated = $request->validated();
            $status = Password::sendResetLink($validated);

            // Only track rate limiting if this wasn't Laravel's built-in throttling
            if ($status !== Password::RESET_THROTTLED) {
                $request->trackAttempt();
            }

            if ($status == Password::RESET_LINK_SENT) {
                // Log successful password reset request
                $user = User::where('email', $validated['email'])->first();
                SecurityEventLog::createEvent(
                    \App\Enums\SecurityEventType::PASSWORD_RESET_REQUESTED,
                    user: $user,
                    email: $validated['email'],
                    userAgent: $request->userAgent(),
                    metadata: [
                        'route' => $request->route()?->getName(),
                        'method' => $request->method(),
                    ]
                );

                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => __($status),
                    ], StatusCodes::OK);
                }

                return back()->with('status', __($status));
            }

            // Handle failure cases
            $statusCode = match ($status) {
                Password::RESET_THROTTLED => StatusCodes::RATE_LIMITED,
                Password::INVALID_USER => StatusCodes::VALIDATION_FAILED,
                default => StatusCodes::BAD_REQUEST,
            };

            // Log failed password reset attempt
            SecurityEventLog::createEvent(
                \App\Enums\SecurityEventType::PASSWORD_RESET_FAILED,
                user: null, // No user for invalid email
                email: $validated['email'],
                userAgent: $request->userAgent(),
                metadata: [
                    'failure_reason' => $status,
                    'route' => $request->route()?->getName(),
                    'method' => $request->method(),
                ]
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __($status),
                    'errors' => ['email' => __($status)],
                ], $statusCode);
            }

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);

        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => trans('auth.validation_failed'),
                    'errors' => $e->errors(),
                ], StatusCodes::VALIDATION_FAILED);
            }

            throw $e;
        }
    }
}
