<?php

namespace App\Http\Controllers;

use App\Enums\SecurityEventType;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\SecurityEventLog;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'auth' => [
                'user' => $request->user(),
            ],
            'routes' => [
                'profile.update' => route('profile.update'),
                'password.update' => route('password.update'),
            ],
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        try {
            $validated = $request->validated();

            // Track what changed
            $emailChanged = $user->email !== $validated['email'];
            $changedFields = [];

            foreach ($validated as $field => $value) {
                if (isset($user->$field) && $value != $user->$field) {
                    $changedFields[] = $field;
                }
            }

            // Handle email change separately
            if ($emailChanged) {
                $oldEmail = $user->email;

                $user->fill($validated);
                $user->email_verified_at = null;
                $user->save();

                // Log email change request
                SecurityEventLog::createEvent(
                    SecurityEventType::EMAIL_CHANGE_REQUESTED,
                    user: $user,
                    metadata: [
                        'old_email' => $oldEmail,
                        'new_email' => $validated['email'],
                    ]
                );

                // Send verification email to new address
                $user->sendEmailVerificationNotification();

                // Log successful profile update
                SecurityEventLog::createEvent(
                    SecurityEventType::PROFILE_UPDATED,
                    user: $user,
                    metadata: [
                        'email_changed' => true,
                        'changed_fields' => $changedFields,
                    ]
                );

                return Redirect::route('verification.notice')
                    ->with('status', 'email-changed-verify');
            }

            // Regular profile update (no email change)
            $user->fill($validated);
            $user->save();

            // Log successful profile update
            SecurityEventLog::createEvent(
                SecurityEventType::PROFILE_UPDATED,
                user: $user,
                metadata: [
                    'email_changed' => false,
                    'changed_fields' => $changedFields,
                ]
            );

            return Redirect::route('profile.edit')->with('status', 'profile-updated');

        } catch (\Exception $e) {
            // Log unexpected failures
            SecurityEventLog::createEvent(
                SecurityEventType::PROFILE_UPDATE_FAILED,
                user: $user,
                metadata: [
                    'failure_reason' => 'system_error',
                    'error' => $e->getMessage(),
                ]
            );

            throw $e;
        }
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
