<?php

namespace App\Http\Requests;

use App\Enums\SecurityEventType;
use App\Models\SecurityEventLog;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class ProfilePictureUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'profile_picture' => [
                'required',
                File::image()
                    ->min(50) // 50KB minimum
                    ->max(5 * 1024), // 5MB maximum
                'dimensions:min_width=200,min_height=200,max_width=4000,max_height=4000',
                'mimes:jpeg,jpg,png,webp',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'profile_picture.required' => 'Please select an image to upload.',
            'profile_picture.image' => 'The file must be an image.',
            'profile_picture.mimes' => 'Only JPEG, PNG, and WebP images are allowed.',
            'profile_picture.min' => 'The image must be at least 50KB.',
            'profile_picture.max' => 'The image must not exceed 5MB.',
            'profile_picture.dimensions' => 'The image must be between 200x200 and 4000x4000 pixels.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator): void
    {
        SecurityEventLog::createEvent(
            SecurityEventType::PROFILE_PICTURE_UPLOAD_FAILED,
            user: $this->user(),
            metadata: [
                'failure_reason' => 'validation_failed',
                'validation_errors' => $validator->errors()->toArray(),
            ]
        );

        parent::failedValidation($validator);
    }
}
