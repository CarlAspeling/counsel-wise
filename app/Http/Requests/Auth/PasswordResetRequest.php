<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class PasswordResetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->ensureIsNotRateLimited();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // For password reset link request
        if ($this->routeIs('password.email')) {
            return [
                'email' => ['required', 'string', 'email', 'max:255'],
            ];
        }

        // For new password submission
        return [
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => trans('auth.email_required'),
            'email.email' => trans('auth.email_invalid'),
            'email.max' => trans('auth.email_too_long'),
            'token.required' => trans('auth.token_required'),
            'token.string' => trans('auth.token_invalid'),
            'password.required' => trans('auth.password_required'),
            'password.confirmed' => trans('auth.password_confirmation_mismatch'),
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'email' => trans('auth.attribute_email'),
            'token' => trans('auth.attribute_token'),
            'password' => trans('auth.attribute_password'),
        ];
    }

    /**
     * Ensure the password reset request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function ensureIsNotRateLimited(): void
    {
        $maxAttempts = config('auth.rate_limits.password_reset.max_attempts', 3);

        if (! RateLimiter::tooManyAttempts($this->throttleKey(), $maxAttempts)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.password_reset_throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        $email = $this->input('email', 'unknown');
        return "password-reset:{$email}|".$this->ip();
    }

    /**
     * Handle a successful validation attempt.
     */
    protected function passedValidation(): void
    {
        // Only track rate limiting for password reset link requests
        if ($this->routeIs('password.email')) {
            $decaySeconds = config('auth.rate_limits.password_reset.decay_seconds', 300);
            RateLimiter::hit($this->throttleKey(), $decaySeconds);
        }
    }
}
