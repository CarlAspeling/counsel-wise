<?php

namespace App\Http\Controllers\Auth;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegistrationRequest;
use App\Http\StatusCodes;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register', [
            'routes' => [
                'register' => route('register'),
                'login' => route('login'),
            ],
            'old' => [
                'name' => old('name'),
                'surname' => old('surname'),
                'email' => old('email'),
                'hpcsa_number' => old('hpcsa_number'),
                'account_type' => old('account_type'),
                // Intentionally exclude password fields for security
            ],
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(RegistrationRequest $request): RedirectResponse|JsonResponse
    {
        try {
            $validated = $request->validated();

            $user = User::create([
                'name' => $validated['name'],
                'surname' => $validated['surname'],
                'email' => $validated['email'],
                'hpcsa_number' => $validated['hpcsa_number'],
                'account_type' => $validated['account_type'],
                'account_status' => AccountStatus::Active, // TODO: Set to Pending when email/HPCSA validation implemented
                'password' => Hash::make($validated['password']),
            ]);

            event(new Registered($user));
            Auth::login($user);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => trans('auth.registration_successful'),
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'surname' => $user->surname,
                        'email' => $user->email,
                    ],
                    'redirect' => route('dashboard'),
                ], StatusCodes::CREATED);
            }

            return redirect()->route('dashboard');
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => trans('auth.registration_failed'),
                    'errors' => $e->errors(),
                ], StatusCodes::VALIDATION_FAILED);
            }

            throw $e;
        }
    }
}
