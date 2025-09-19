<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\PasswordResetRequest;
use App\Http\StatusCodes;
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

            if ($status == Password::RESET_LINK_SENT) {
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
                Password::INVALID_USER => StatusCodes::NOT_FOUND,
                default => StatusCodes::BAD_REQUEST,
            };

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
