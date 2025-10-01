<?php

namespace App\Http\Requests;

use App\Enums\Gender;
use App\Enums\Language;
use App\Enums\SecurityEventType;
use App\Enums\SouthAfricanProvince;
use App\Models\SecurityEventLog;
use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'hpcsa_number' => ['required', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', Rule::enum(Gender::class)],
            'language' => ['nullable', Rule::enum(Language::class)],
            'region' => ['nullable', Rule::enum(SouthAfricanProvince::class)],
        ];

        // Require password confirmation if email is being changed
        if ($this->email !== $this->user()->email) {
            $rules['password'] = ['required', 'current_password'];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'password.required' => 'Please confirm your password to change your email address.',
            'password.current_password' => 'The provided password is incorrect.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator): void
    {
        // Log failed validation
        SecurityEventLog::createEvent(
            SecurityEventType::PROFILE_UPDATE_FAILED,
            user: $this->user(),
            metadata: [
                'failure_reason' => 'validation_failed',
                'validation_errors' => $validator->errors()->toArray(),
            ]
        );

        parent::failedValidation($validator);
    }
}
