<?php

namespace App\Enums;

enum SecurityEventType: string
{
    case LOGIN_SUCCESS = 'login_success';
    case LOGIN_FAILED = 'login_failed';
    case LOGIN_RATE_LIMITED = 'login_rate_limited';
    case LOGOUT = 'logout';

    case REGISTRATION_SUCCESS = 'registration_success';
    case REGISTRATION_FAILED = 'registration_failed';

    case PASSWORD_RESET_REQUESTED = 'password_reset_requested';
    case PASSWORD_RESET_SUCCESS = 'password_reset_success';
    case PASSWORD_RESET_FAILED = 'password_reset_failed';
    case PASSWORD_RESET_RATE_LIMITED = 'password_reset_rate_limited';

    case EMAIL_VERIFICATION_REQUESTED = 'email_verification_requested';
    case EMAIL_VERIFICATION_SENT = 'email_verification_sent';
    case EMAIL_VERIFICATION_SUCCESS = 'email_verification_success';
    case EMAIL_VERIFICATION_FAILED = 'email_verification_failed';
    case EMAIL_VERIFICATION_RATE_LIMITED = 'email_verification_rate_limited';

    case PASSWORD_CHANGED = 'password_changed';
    case PASSWORD_CHANGE_FAILED = 'password_change_failed';
    case PASSWORD_CHANGE_RATE_LIMITED = 'password_change_rate_limited';

    case PROFILE_UPDATED = 'profile_updated';
    case PROFILE_UPDATE_FAILED = 'profile_update_failed';
    case PROFILE_UPDATE_RATE_LIMITED = 'profile_update_rate_limited';
    case EMAIL_CHANGE_REQUESTED = 'email_change_requested';

    case PROFILE_PICTURE_UPDATED = 'profile_picture_updated';
    case PROFILE_PICTURE_UPLOAD_FAILED = 'profile_picture_upload_failed';
    case PROFILE_PICTURE_DELETED = 'profile_picture_deleted';

    case ACCOUNT_LOCKED = 'account_locked';
    case ACCOUNT_UNLOCKED = 'account_unlocked';
    case ACCOUNT_SUSPENDED = 'account_suspended';

    case SUSPICIOUS_ACTIVITY = 'suspicious_activity';
    case MULTIPLE_LOGIN_ATTEMPTS = 'multiple_login_attempts';
    case UNUSUAL_LOCATION = 'unusual_location';
    case RATE_LIMIT_EXCEEDED = 'rate_limit_exceeded';

    /**
     * Get a human-readable description of the event type.
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::LOGIN_SUCCESS => 'User logged in successfully',
            self::LOGIN_FAILED => 'Failed login attempt',
            self::LOGIN_RATE_LIMITED => 'Login attempt blocked due to rate limiting',
            self::LOGOUT => 'User logged out',

            self::REGISTRATION_SUCCESS => 'User registered successfully',
            self::REGISTRATION_FAILED => 'Failed registration attempt',

            self::PASSWORD_RESET_REQUESTED => 'Password reset link requested',
            self::PASSWORD_RESET_SUCCESS => 'Password reset completed successfully',
            self::PASSWORD_RESET_FAILED => 'Failed password reset attempt',
            self::PASSWORD_RESET_RATE_LIMITED => 'Password reset request blocked due to rate limiting',

            self::EMAIL_VERIFICATION_REQUESTED => 'Email verification link requested',
            self::EMAIL_VERIFICATION_SENT => 'Email verification link sent',
            self::EMAIL_VERIFICATION_SUCCESS => 'Email verified successfully',
            self::EMAIL_VERIFICATION_FAILED => 'Email verification failed',
            self::EMAIL_VERIFICATION_RATE_LIMITED => 'Email verification request blocked due to rate limiting',

            self::PASSWORD_CHANGED => 'Password changed successfully',
            self::PASSWORD_CHANGE_FAILED => 'Failed password change attempt',
            self::PASSWORD_CHANGE_RATE_LIMITED => 'Password change blocked due to rate limiting',

            self::PROFILE_UPDATED => 'User profile updated successfully',
            self::PROFILE_UPDATE_FAILED => 'Failed profile update attempt',
            self::PROFILE_UPDATE_RATE_LIMITED => 'Profile update blocked due to rate limiting',
            self::EMAIL_CHANGE_REQUESTED => 'User requested email address change',

            self::PROFILE_PICTURE_UPDATED => 'User uploaded new profile picture',
            self::PROFILE_PICTURE_UPLOAD_FAILED => 'Failed profile picture upload attempt',
            self::PROFILE_PICTURE_DELETED => 'User deleted profile picture',

            self::ACCOUNT_LOCKED => 'Account locked due to security concerns',
            self::ACCOUNT_UNLOCKED => 'Account unlocked',
            self::ACCOUNT_SUSPENDED => 'Account suspended',

            self::SUSPICIOUS_ACTIVITY => 'Suspicious activity detected',
            self::MULTIPLE_LOGIN_ATTEMPTS => 'Multiple failed login attempts detected',
            self::UNUSUAL_LOCATION => 'Login from unusual location detected',
            self::RATE_LIMIT_EXCEEDED => 'Rate limit exceeded',
        };
    }

    /**
     * Get the severity level of the event.
     */
    public function getSeverity(): string
    {
        return match ($this) {
            self::LOGIN_SUCCESS,
            self::LOGOUT,
            self::REGISTRATION_SUCCESS,
            self::PASSWORD_RESET_SUCCESS,
            self::EMAIL_VERIFICATION_SUCCESS,
            self::PASSWORD_CHANGED,
            self::PROFILE_UPDATED,
            self::PROFILE_PICTURE_UPDATED,
            self::PROFILE_PICTURE_DELETED,
            self::ACCOUNT_UNLOCKED => 'info',

            self::PASSWORD_RESET_REQUESTED,
            self::EMAIL_VERIFICATION_REQUESTED,
            self::EMAIL_VERIFICATION_SENT,
            self::EMAIL_CHANGE_REQUESTED => 'notice',

            self::LOGIN_FAILED,
            self::REGISTRATION_FAILED,
            self::PASSWORD_RESET_FAILED,
            self::EMAIL_VERIFICATION_FAILED,
            self::PASSWORD_CHANGE_FAILED,
            self::PROFILE_UPDATE_FAILED,
            self::PROFILE_PICTURE_UPLOAD_FAILED => 'warning',

            self::LOGIN_RATE_LIMITED,
            self::PASSWORD_RESET_RATE_LIMITED,
            self::EMAIL_VERIFICATION_RATE_LIMITED,
            self::PASSWORD_CHANGE_RATE_LIMITED,
            self::PROFILE_UPDATE_RATE_LIMITED,
            self::MULTIPLE_LOGIN_ATTEMPTS,
            self::UNUSUAL_LOCATION,
            self::RATE_LIMIT_EXCEEDED => 'alert',

            self::ACCOUNT_LOCKED,
            self::ACCOUNT_SUSPENDED,
            self::SUSPICIOUS_ACTIVITY => 'critical',
        };
    }

    /**
     * Check if this event type should trigger an alert.
     */
    public function shouldAlert(): bool
    {
        return in_array($this->getSeverity(), ['alert', 'critical']);
    }
}
