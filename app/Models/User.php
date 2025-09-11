<?php

namespace App\Models;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\Gender;
use App\Enums\Language;
use App\Enums\SouthAfricanProvince;
use App\Enums\ThemePreference;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'surname',
        'email',
        'password',
        'hpcsa_number',
        'account_type',
        'profile_picture',
        'account_status',
        'theme_preference',
        'phone_number',
        'gender',
        'language',
        'region',
        'notification_preferences',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'hpcsa_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'data_privacy_consent' => 'datetime',
            'terms_accepted_at' => 'datetime',
            'password' => 'hashed',
            'account_type' => AccountType::class,
            'account_status' => AccountStatus::class,
            'theme_preference' => ThemePreference::class,
            'gender' => Gender::class,
            'language' => Language::class,
            'region' => SouthAfricanProvince::class,
            'notification_preferences' => 'array',
        ];
    }
}
