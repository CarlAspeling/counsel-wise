<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'failed' => 'These credentials do not match our records.',
    'password' => 'The provided password is incorrect.',
    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',

    // Validation messages
    'email_required' => 'Email address is required.',
    'email_invalid' => 'Please enter a valid email address.',
    'email_too_long' => 'Email address cannot exceed 255 characters.',
    'email_taken' => 'This email address is already registered.',
    'password_required' => 'Password is required.',
    'password_too_short' => 'Password must be at least 8 characters long.',
    'password_confirmation_mismatch' => 'Password confirmation does not match.',
    'name_required' => 'First name is required.',
    'name_invalid' => 'First name must be text only.',
    'name_too_long' => 'First name cannot exceed 255 characters.',
    'name_too_short' => 'First name must be at least 2 characters long.',
    'surname_required' => 'Last name is required.',
    'surname_invalid' => 'Last name must be text only.',
    'surname_too_long' => 'Last name cannot exceed 255 characters.',
    'surname_too_short' => 'Last name must be at least 2 characters long.',
    'hpcsa_number_required' => 'HPCSA number is required.',
    'hpcsa_number_invalid' => 'HPCSA number must be text only.',
    'hpcsa_number_too_long' => 'HPCSA number cannot exceed 255 characters.',
    'hpcsa_number_too_short' => 'HPCSA number must be at least 3 characters long.',
    'account_type_required' => 'Account type is required.',
    'token_required' => 'Reset token is required.',
    'token_invalid' => 'Reset token must be text only.',
    'password_reset_throttle' => 'Too many password reset attempts. Please try again in :seconds seconds.',
    'email_verification_throttle' => 'Too many verification emails sent. Please try again in :seconds seconds.',
    'verification_id_required' => 'Verification ID is required.',
    'verification_id_invalid' => 'Invalid verification ID format.',
    'verification_hash_required' => 'Verification hash is required.',
    'verification_hash_invalid' => 'Invalid verification hash format.',
    'verification_expires_required' => 'Verification expiration is required.',
    'verification_expires_invalid' => 'Invalid verification expiration format.',
    'verification_signature_required' => 'Verification signature is required.',
    'verification_signature_invalid' => 'Invalid verification signature format.',

    // Attribute names
    'attribute_name' => 'first name',
    'attribute_surname' => 'last name',
    'attribute_email' => 'email address',
    'attribute_hpcsa_number' => 'HPCSA number',
    'attribute_account_type' => 'account type',
    'attribute_password' => 'password',
    'attribute_token' => 'reset token',
    'attribute_verification_id' => 'verification ID',
    'attribute_verification_hash' => 'verification hash',
    'attribute_verification_expires' => 'verification expiration',
    'attribute_verification_signature' => 'verification signature',

];
