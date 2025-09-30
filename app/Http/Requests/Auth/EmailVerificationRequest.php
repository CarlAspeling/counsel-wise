<?php

namespace App\Http\Requests\Auth;

use App\Models\SecurityEventLog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class EmailVerificationRequest extends FormRequest
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
        // For email verification notification requests
        if ($this->routeIs('verification.send')) {
            return [];
        }

        // For email verification link validation - usually handled by Laravel's built-in request
        return [
            'id' => ['required', 'string'],
            'hash' => ['required', 'string'],
            'expires' => ['required', 'integer'],
            'signature' => ['required', 'string'],
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
            'id.required' => trans('auth.verification_id_required'),
            'id.string' => trans('auth.verification_id_invalid'),
            'hash.required' => trans('auth.verification_hash_required'),
            'hash.string' => trans('auth.verification_hash_invalid'),
            'expires.required' => trans('auth.verification_expires_required'),
            'expires.integer' => trans('auth.verification_expires_invalid'),
            'signature.required' => trans('auth.verification_signature_required'),
            'signature.string' => trans('auth.verification_signature_invalid'),
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
            'id' => trans('auth.attribute_verification_id'),
            'hash' => trans('auth.attribute_verification_hash'),
            'expires' => trans('auth.attribute_verification_expires'),
            'signature' => trans('auth.attribute_verification_signature'),
        ];
    }

    /**
     * Ensure the email verification request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function ensureIsNotRateLimited(): void
    {
        // Only apply rate limiting to verification notification requests
        if (! $this->routeIs('verification.send')) {
            return;
        }

        $maxAttempts = config('auth.rate_limits.email_verification.max_attempts', 2);

        if (! RateLimiter::tooManyAttempts($this->throttleKey(), $maxAttempts)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey());

        // Log failed email verification event due to rate limiting
        SecurityEventLog::createEvent(
            \App\Enums\SecurityEventType::EMAIL_VERIFICATION_FAILED,
            user: $this->user(),
            metadata: [
                'rate_limited' => true,
                'lockout_seconds' => $seconds,
                'lockout_minutes' => ceil($seconds / 60),
                'throttle_key' => $this->throttleKey(),
                'max_attempts' => $maxAttempts,
            ]
        );

        throw ValidationException::withMessages([
            'email' => trans('auth.email_verification_throttle', [
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
        // IP-based rate limiting only to prevent abuse from same source
        return 'email-verification:'.$this->ip();
    }

    /**
     * Handle a successful validation attempt.
     */
    protected function passedValidation(): void
    {
        // Only track rate limiting for verification notification requests
        if ($this->routeIs('verification.send')) {
            $decaySeconds = config('auth.rate_limits.email_verification.decay_seconds', 300);
            RateLimiter::hit($this->throttleKey(), $decaySeconds);
        }
    }
}
