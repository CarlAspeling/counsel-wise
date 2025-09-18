<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Middleware\ThrottlePasswordChanges;
use App\Models\PasswordChangeLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PasswordController extends Controller
{
    /**
     * Show the password change form.
     */
    public function edit(): Response
    {
        return Inertia::render('Profile/Edit', [
            'auth' => [
                'user' => request()->user(),
            ],
            'routes' => [
                'profile.update' => route('profile.update'),
                'password.update' => route('password.update'),
            ],
        ]);
    }

    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        try {
            $validated = $request->validateWithBag('updatePassword', [
                'current_password' => ['required', 'current_password'],
                'password' => ['required', Password::defaults(), 'confirmed'],
            ]);

            $request->user()->update([
                'password' => Hash::make($validated['password']),
            ]);

            // Log successful password change
            $this->logPasswordAttempt($request, true);

            return back()->with('status', 'password-updated');

        } catch (ValidationException $e) {
            // Determine failure reason based on which validation failed
            $failureReason = $this->determineFailureReason($e);

            // Log failed password change attempt
            $this->logPasswordAttempt($request, false, $failureReason);

            // Increment rate limit attempts for validation failures
            ThrottlePasswordChanges::incrementAttempts($request);

            // Re-throw the exception with the proper error bag
            $e->errorBag = 'updatePassword';
            throw $e;
        } catch (\Exception $e) {
            // Log unexpected failures
            $this->logPasswordAttempt($request, false, 'system_error');

            // Re-throw the exception
            throw $e;
        }
    }

    /**
     * Log password change attempt for security audit.
     */
    private function logPasswordAttempt(Request $request, bool $success, ?string $failureReason = null): void
    {
        PasswordChangeLog::create([
            'user_id' => $request->user()->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'attempted_at' => now(),
            'success' => $success,
            'failure_reason' => $failureReason,
        ]);
    }

    /**
     * Determine failure reason from validation exception.
     */
    private function determineFailureReason(ValidationException $exception): string
    {
        $errors = $exception->errors();

        // Check for current password validation failure
        if (isset($errors['current_password'])) {
            return 'invalid_current_password';
        }

        // Check for new password validation failure
        if (isset($errors['password'])) {
            return 'new_password_validation_failed';
        }

        // Generic validation failure
        return 'validation_failed';
    }
}
