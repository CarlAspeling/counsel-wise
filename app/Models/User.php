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
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class User extends Authenticatable implements HasMedia, MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, InteractsWithMedia, Notifiable, SoftDeletes;

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
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = [
        'profile_picture_url',
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

    /**
     * Register media collections
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('profilePicture')
            ->singleFile() // Only one profile picture at a time
            ->acceptsMimeTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/webp'])
            ->useDisk('media');
    }

    /**
     * Register media conversions
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(100)
            ->height(100)
            ->sharpen(10)
            ->nonQueued(); // Generate immediately for fast feedback

        $this->addMediaConversion('medium')
            ->width(300)
            ->height(300)
            ->sharpen(10)
            ->queued(); // Queue for async processing

        $this->addMediaConversion('large')
            ->width(600)
            ->height(600)
            ->sharpen(10)
            ->queued(); // Queue for async processing
    }

    /**
     * Get profile picture URL (with Laravolt fallback)
     */
    public function getProfilePictureUrlAttribute(): string
    {
        $media = $this->getFirstMedia('profilePicture');

        if ($media) {
            // Use thumb conversion (non-queued, always available immediately)
            // or original if thumb doesn't exist yet
            if ($media->hasGeneratedConversion('thumb')) {
                return $media->getUrl('thumb');
            }

            return $media->getUrl();
        }

        // Fallback to Laravolt Avatar - check for name/surname
        $name = $this->name ?? 'User';
        $surname = $this->surname ?? '';

        return \Avatar::create(trim($name.' '.$surname))->toBase64();
    }

    /**
     * Get avatar for specific size
     */
    public function getAvatar(string $conversion = 'medium'): string
    {
        $media = $this->getFirstMedia('profilePicture');

        if ($media) {
            return $media->getUrl($conversion);
        }

        // Fallback to Laravolt Avatar - check for name/surname
        $name = $this->name ?? 'User';
        $surname = $this->surname ?? '';

        return \Avatar::create(trim($name.' '.$surname))->toBase64();
    }
}
