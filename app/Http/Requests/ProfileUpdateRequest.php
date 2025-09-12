<?php

namespace App\Http\Requests;

use App\Enums\AccountType;
use App\Enums\Gender;
use App\Enums\Language;
use App\Enums\SouthAfricanProvince;
use App\Models\User;
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
        return [
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
    }
}
