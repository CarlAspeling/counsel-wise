<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\EmailVerificationRequest;
use App\Http\StatusCodes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(EmailVerificationRequest $request): RedirectResponse|JsonResponse
    {
        try {
            if ($request->user()->hasVerifiedEmail()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => trans('auth.already_verified'),
                        'redirect' => route('dashboard'),
                    ], StatusCodes::OK);
                }

                return redirect()->intended(route('dashboard', absolute: false));
            }

            $request->user()->sendEmailVerificationNotification();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => trans('auth.verification_sent'),
                ], StatusCodes::OK);
            }

            return back()->with('status', 'verification-link-sent');

        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => trans('auth.rate_limited'),
                    'errors' => $e->errors(),
                ], StatusCodes::RATE_LIMITED);
            }

            throw $e;
        }
    }
}
