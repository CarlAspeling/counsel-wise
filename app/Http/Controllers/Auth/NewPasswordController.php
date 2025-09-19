<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\PasswordResetRequest;
use App\Http\StatusCodes;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('Auth/ResetPassword', [
            'email' => $request->email,
            'token' => $request->route('token'),
            'routes' => [
                'password.store' => route('password.store'),
            ],
        ]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(PasswordResetRequest $request): RedirectResponse|JsonResponse
    {
        try {
            $validated = $request->validated();

            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function (User $user) use ($validated) {
                    $user->forceFill([
                        'password' => Hash::make($validated['password']),
                        'remember_token' => Str::random(60),
                    ])->save();

                    event(new PasswordReset($user));
                }
            );

            if ($status == Password::PASSWORD_RESET) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => __($status),
                        'redirect' => route('login'),
                    ], StatusCodes::OK);
                }

                return redirect()->route('login')->with('status', __($status));
            }

            // Handle failure cases
            $statusCode = match ($status) {
                Password::INVALID_TOKEN => StatusCodes::BAD_REQUEST,
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
