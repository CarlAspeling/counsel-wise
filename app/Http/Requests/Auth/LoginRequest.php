<?php

namespace App\Http\Requests\Auth;

use App\Exceptions\RateLimitExceededException;
use App\Models\SecurityEventLog;
use App\Services\RateLimitBypassService;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
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
            'password.required' => trans('auth.password_required'),
            'password.min' => trans('auth.password_too_short'),
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            $decaySeconds = config('auth.rate_limits.login.decay_seconds', 900);
            RateLimiter::hit($this->throttleKey(), $decaySeconds);

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        // Check if current user should bypass rate limiting
        $bypassService = app(RateLimitBypassService::class);
        if ($bypassService->shouldBypass($this)) {
            return;
        }

        $maxAttempts = config('auth.rate_limits.login.max_attempts', 5);

        if (! RateLimiter::tooManyAttempts($this->throttleKey(), $maxAttempts)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        // Log rate limit exceeded security event
        SecurityEventLog::createEvent(
            \App\Enums\SecurityEventType::RATE_LIMIT_EXCEEDED,
            email: $this->string('email'),
            metadata: [
                'attempts_remaining' => 0,
                'lockout_seconds' => $seconds,
                'lockout_minutes' => ceil($seconds / 60),
                'throttle_key' => $this->throttleKey(),
                'max_attempts' => $maxAttempts,
            ]
        );

        throw new RateLimitExceededException(
            errors: [
                'email' => [trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ])],
            ],
            retryAfterSeconds: $seconds
        );
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
