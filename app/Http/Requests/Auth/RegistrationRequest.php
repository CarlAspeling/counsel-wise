<?php

namespace App\Http\Requests\Auth;

use App\Enums\AccountType;
use App\Models\User;
use App\Services\RateLimitBypassService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class RegistrationRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:255', 'min:2'],
            'surname' => ['required', 'string', 'max:255', 'min:2'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'hpcsa_number' => ['required', 'string', 'max:255', 'min:3'],
            'account_type' => ['required', Rule::enum(AccountType::class)],
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
            'name.required' => trans('auth.name_required'),
            'name.string' => trans('auth.name_invalid'),
            'name.max' => trans('auth.name_too_long'),
            'name.min' => trans('auth.name_too_short'),
            'surname.required' => trans('auth.surname_required'),
            'surname.string' => trans('auth.surname_invalid'),
            'surname.max' => trans('auth.surname_too_long'),
            'surname.min' => trans('auth.surname_too_short'),
            'email.required' => trans('auth.email_required'),
            'email.email' => trans('auth.email_invalid'),
            'email.max' => trans('auth.email_too_long'),
            'email.unique' => trans('auth.email_taken'),
            'hpcsa_number.required' => trans('auth.hpcsa_number_required'),
            'hpcsa_number.string' => trans('auth.hpcsa_number_invalid'),
            'hpcsa_number.max' => trans('auth.hpcsa_number_too_long'),
            'hpcsa_number.min' => trans('auth.hpcsa_number_too_short'),
            'account_type.required' => trans('auth.account_type_required'),
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
            'name' => trans('auth.attribute_name'),
            'surname' => trans('auth.attribute_surname'),
            'email' => trans('auth.attribute_email'),
            'hpcsa_number' => trans('auth.attribute_hpcsa_number'),
            'account_type' => trans('auth.attribute_account_type'),
            'password' => trans('auth.attribute_password'),
        ];
    }

    /**
     * Ensure the registration request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function ensureIsNotRateLimited(): void
    {
        // Check if current user should bypass rate limiting
        $bypassService = app(RateLimitBypassService::class);
        if ($bypassService->shouldBypass($this)) {
            return;
        }

        $maxAttempts = config('auth.rate_limits.registration.max_attempts', 3);

        if (! RateLimiter::tooManyAttempts($this->throttleKey(), $maxAttempts)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.registration_throttle', [
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
        return 'registration:'.$this->ip();
    }

    /**
     * Handle a successful validation attempt.
     */
    protected function passedValidation(): void
    {
        $decaySeconds = config('auth.rate_limits.registration.decay_seconds', 900);
        RateLimiter::hit($this->throttleKey(), $decaySeconds);
    }
}
