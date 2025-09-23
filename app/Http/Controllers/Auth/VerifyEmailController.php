<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SecurityEventLog;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            SecurityEventLog::createEvent(
                \App\Enums\SecurityEventType::EMAIL_VERIFICATION_SUCCESS,
                $request->user(),
                metadata: ['already_verified' => true]
            );

            return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            // Update account status to Active after email verification
            $request->user()->update(['account_status' => \App\Enums\AccountStatus::Active]);

            event(new Verified($request->user()));

            SecurityEventLog::createEvent(
                \App\Enums\SecurityEventType::EMAIL_VERIFICATION_SUCCESS,
                $request->user(),
                metadata: ['newly_verified' => true]
            );
        }

        return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
    }
}
