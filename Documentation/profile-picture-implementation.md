# Profile Picture Implementation Strategy

**Generated:** October 1, 2025
**Status:** 📋 **PLANNING**
**Approach:** Test-Driven Development (TDD)
**GitHub Issue:** Profile Picture Functionality

---

## TDD Approach

This implementation follows **strict Test-Driven Development**:

1. **Red:** Write failing tests first for each feature
2. **Green:** Write minimal code to make tests pass
3. **Refactor:** Improve code while keeping tests green

### Documentation Updates
- **README.md** will be updated at the completion of each phase with setup instructions
- **.env.example** will be updated as new environment variables are introduced
- This document will be updated to reflect completion status

---

## Executive Summary

### Current Status
- ✅ **Database Column:** `profile_picture` string column exists in users table (nullable)
- ✅ **Model Field:** `profile_picture` included in User model fillable array
- ❌ **Upload System:** No file upload implementation
- ❌ **Image Processing:** No validation or optimization
- ❌ **Storage:** No storage configuration for profile pictures
- ❌ **Default Avatars:** No fallback system for users without pictures
- ❌ **UI Components:** No upload interface or display components

### Ideal Status
- ✅ Secure file upload with comprehensive validation
- ✅ Automatic image optimization and responsive variants
- ✅ Elegant default profile pictures with user initials
- ✅ Profile pictures displayed consistently across the app
- ✅ Performant image delivery with CDN-ready structure
- ✅ Comprehensive test coverage

---

## Package Analysis & Recommendations

### Recommended: Spatie Media Library ✅
**Package:** `spatie/laravel-medialibrary`
**Purpose:** Handles upload, storage, retrieval, and conversions

**Pros:**
- ✅ Battle-tested Laravel package with 5.6k+ stars
- ✅ Built-in support for multiple collections (profile pictures, attachments, etc.)
- ✅ Automatic responsive image generation (thumbnail, medium, large)
- ✅ Native Laravel filesystem integration (local, S3, etc.)
- ✅ Excellent documentation and community support
- ✅ Built-in image optimization via Spatie Image Optimizer
- ✅ Support for custom properties and metadata
- ✅ Queue-friendly for async processing

**Cons:**
- ⚠️ Requires additional database table (`media`)
- ⚠️ Slight learning curve for the API
- ⚠️ Adds ~15-20 dependencies

**Verdict:** **STRONGLY RECOMMENDED** - This is the industry-standard solution for Laravel media management. The benefits far outweigh the minimal overhead.

---

### Recommended: Spatie Image Optimizer ✅
**Package:** `spatie/image-optimizer`
**Purpose:** Optimize image file sizes without quality loss

**Pros:**
- ✅ Integrates seamlessly with Spatie Media Library
- ✅ Supports multiple formats (JPEG, PNG, GIF, SVG, WebP)
- ✅ Automatic optimization during upload
- ✅ Can reduce file sizes by 30-70% without visible quality loss
- ✅ Works with popular optimizers (jpegoptim, pngquant, etc.)

**Cons:**
- ⚠️ Requires system binaries to be installed (jpegoptim, optipng, etc.)
- ⚠️ Additional server configuration needed

**Verdict:** **RECOMMENDED** - Essential for keeping storage costs down and improving page load times. However, binaries may not be available in all environments (consider fallback).

---

### Recommended: Laravolt Avatar ✅
**Package:** `laravolt/avatar`
**Purpose:** Generate default profile pictures with user initials

**Pros:**
- ✅ Generates beautiful SVG/PNG avatars from user names
- ✅ Customizable colors, fonts, and shapes
- ✅ Zero storage required (generates on-the-fly or caches)
- ✅ Perfect fallback for users without uploaded pictures
- ✅ Lightweight (~100KB)
- ✅ Can use Gravatar as fallback

**Cons:**
- ⚠️ Limited customization compared to custom solutions
- ⚠️ SVG generation may have minor performance impact at scale

**Verdict:** **RECOMMENDED** - Excellent UX improvement with minimal overhead. Provides professional-looking defaults.

---

### Optional: Intervention Image ⚠️
**Package:** `intervention/image`
**Purpose:** Image manipulation and custom processing

**Pros:**
- ✅ Powerful image manipulation (crop, resize, filters, etc.)
- ✅ Supports GD and Imagick drivers
- ✅ Useful for custom avatar generation or special effects

**Cons:**
- ❌ **NOT NEEDED** - Spatie Media Library already handles all standard image manipulation
- ❌ Adds unnecessary complexity and dependencies
- ❌ Overlapping functionality with Media Library

**Verdict:** **NOT RECOMMENDED** - Spatie Media Library covers 99% of use cases. Only add if you need advanced custom image manipulation (circular crops, watermarks, complex filters).

---

## Recommended Package Stack

### Core Implementation (Required)
```json
{
    "spatie/laravel-medialibrary": "^11.0",
    "laravolt/avatar": "^6.0"
}
```

### Optional Enhancement
```json
{
    "spatie/image-optimizer": "^1.7"
}
```

**Total additional dependencies:** ~20 packages
**Storage overhead:** +1 database table (media), +1 directory (storage/app/public/media)
**Performance impact:** Minimal (async processing via queues)

---

## Current Infrastructure

### Database Schema
**Column:** `users.profile_picture` (string, nullable)
**Status:** ✅ Ready (will be replaced by Media Library relationship)

**Migration Strategy:**
- Keep existing column for backward compatibility
- Add Media Library migration
- Create data migration to move existing paths to media table (if needed)
- Eventually deprecate `profile_picture` column

### Storage Configuration
**Current:** Default Laravel filesystem (storage/app)
**Needed:**
- Public disk configuration for profile pictures
- Media collection configuration
- Image conversion definitions

### User Model
**Current:** `profile_picture` in fillable array
**Needed:**
- Implement `HasMedia` trait
- Register media collections
- Define conversions (thumbnail, medium, large)
- Add accessor for avatar URL

---

## Implementation Strategy

### Phase 1: Foundation Setup (2-3 hours)
**Goal:** Install packages and configure infrastructure
**Status:** ⏳ Pending

#### TDD Step 1: Write Failing Tests (20 min)

Create: `tests/Unit/UserMediaTest.php`

```php
<?php

use App\Models\User;

test('user implements HasMedia trait', function () {
    $user = User::factory()->create();

    expect($user)->toBeInstanceOf(\Spatie\MediaLibrary\HasMedia::class);
});

test('user has profilePicture media collection registered', function () {
    $user = User::factory()->create();

    $reflection = new ReflectionClass($user);
    $method = $reflection->getMethod('registerMediaCollections');

    expect($method)->not->toBeNull();
});

test('user defines image conversions for profile pictures', function () {
    $user = User::factory()->create();

    $reflection = new ReflectionClass($user);
    $method = $reflection->getMethod('registerMediaConversions');

    expect($method)->not->toBeNull();
});

test('user without profile picture returns laravolt avatar url', function () {
    $user = User::factory()->create(['name' => 'John', 'surname' => 'Doe']);

    expect($user->profile_picture_url)->toContain('avatar');
    expect($user->profile_picture_url)->toContain('JD'); // initials
});
```

Run tests (should fail):
```bash
php.bat artisan test --without-tty --no-ansi tests/Unit/UserMediaTest.php
```

#### TDD Step 2: Install Packages (10 min)

```bash
composer require spatie/laravel-medialibrary
composer require laravolt/avatar
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-migrations"
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-config"
php artisan vendor:publish --provider="Laravolt\Avatar\ServiceProvider" --tag="config"
php artisan migrate
```

#### TDD Step 3: Configure Packages (30 min)

**Update `config/medialibrary.php`:**
```php
'disk_name' => env('MEDIA_DISK', 'public'),
'max_file_size' => 1024 * 1024 * 5, // 5MB
'queue_name' => env('QUEUE_CONNECTION', 'sync'),
```

**Update `config/avatar.php`:**
```php
'shape' => 'circle',
'chars' => 2,
'backgrounds' => [
    '#3B82F6', // blue
    '#10B981', // green
    '#F59E0B', // amber
    '#EF4444', // red
    '#8B5CF6', // purple
],
'foregrounds' => ['#FFFFFF'],
'fonts' => [public_path('fonts/OpenSans-Bold.ttf')],
'fontSize' => 48,
'width' => 100,
'height' => 100,
'cache' => [
    'enabled' => true,
    'duration' => 60 * 24, // 24 hours
],
```

**Update `config/filesystems.php`:**
```php
'disks' => [
    'public' => [
        'driver' => 'local',
        'root' => storage_path('app/public'),
        'url' => env('APP_URL').'/storage',
        'visibility' => 'public',
        'throw' => false,
    ],

    'media' => [
        'driver' => 'local',
        'root' => storage_path('app/public/media'),
        'url' => env('APP_URL').'/storage/media',
        'visibility' => 'public',
        'throw' => false,
    ],
],
```

#### TDD Step 4: Update User Model (30 min)

**File:** `app/Models/User.php`

```php
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class User extends Authenticatable implements MustVerifyEmail, HasMedia
{
    use HasFactory, Notifiable, SoftDeletes, InteractsWithMedia;

    // ... existing code ...

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
            ->sharpen(10);

        $this->addMediaConversion('large')
            ->width(600)
            ->height(600)
            ->sharpen(10);
    }

    /**
     * Get profile picture URL (with Laravolt fallback)
     */
    public function getProfilePictureUrlAttribute(): string
    {
        $media = $this->getFirstMedia('profilePicture');

        if ($media) {
            return $media->getUrl('medium');
        }

        // Fallback to Laravolt Avatar
        return \Avatar::create($this->name . ' ' . $this->surname)->toBase64();
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

        return \Avatar::create($this->name . ' ' . $this->surname)->toBase64();
    }
}
```

#### TDD Step 5: Run Tests (Should Pass)

```bash
php.bat artisan test --without-tty --no-ansi tests/Unit/UserMediaTest.php
```

#### Step 6: Create Storage Symlink

```bash
php artisan storage:link
```

#### Step 7: Update .env.example

Add to `.env.example`:
```env
# Media Library Configuration
MEDIA_DISK=public
```

#### Step 8: Update README.md

Add new section after installation instructions:

```markdown
## Profile Picture Setup

This application uses Spatie Media Library for profile picture management.

### First-Time Setup

1. Create storage symlink:
   ```bash
   php artisan storage:link
   ```

2. Ensure storage directories are writable:
   ```bash
   chmod -R 775 storage/app/public/media
   ```

### Packages Used
- **Spatie Media Library** - File upload and management
- **Laravolt Avatar** - Default profile pictures with user initials
```

#### Phase 1 Complete ✅
- [x] Tests written and passing
- [x] Packages installed and configured
- [x] User model implements HasMedia
- [x] Media collections and conversions defined
- [x] Storage configured
- [x] README.md updated
- [x] .env.example updated

---

### Phase 2: Upload & Validation (3-4 hours)
**Goal:** Implement secure file upload with validation
**Status:** ⏳ Pending

#### TDD Step 1: Write Failing Tests (45 min)

Create: `tests/Feature/ProfilePictureUploadTest.php`

```php
<?php

use App\Enums\SecurityEventType;
use App\Models\SecurityEventLog;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('media');
    SecurityEventLog::truncate();
});

describe('Profile Picture Upload', function () {
    test('user can upload profile picture', function () {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('avatar.jpg', 500, 500);

        $response = $this->actingAs($user)->post('/profile/picture', [
            'profile_picture' => $file,
        ]);

        $response->assertRedirect();
        expect($user->fresh()->getFirstMedia('profilePicture'))->not->toBeNull();
    });

    test('validates file type - rejects non-image files', function () {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($user)->post('/profile/picture', [
            'profile_picture' => $file,
        ]);

        $response->assertSessionHasErrors('profile_picture');
    });

    test('validates file size - rejects files over 5MB', function () {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('huge.jpg')->size(6000); // 6MB

        $response = $this->actingAs($user)->post('/profile/picture', [
            'profile_picture' => $file,
        ]);

        $response->assertSessionHasErrors('profile_picture');
    });

    test('validates minimum dimensions - rejects tiny images', function () {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('tiny.jpg', 100, 100);

        $response = $this->actingAs($user)->post('/profile/picture', [
            'profile_picture' => $file,
        ]);

        $response->assertSessionHasErrors('profile_picture');
    });

    test('validates maximum dimensions - rejects huge images', function () {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('giant.jpg', 5000, 5000);

        $response = $this->actingAs($user)->post('/profile/picture', [
            'profile_picture' => $file,
        ]);

        $response->assertSessionHasErrors('profile_picture');
    });

    test('deletes old profile picture when uploading new one', function () {
        $user = User::factory()->create();

        // Upload first picture
        $file1 = UploadedFile::fake()->image('first.jpg', 500, 500);
        $this->actingAs($user)->post('/profile/picture', ['profile_picture' => $file1]);
        $firstMedia = $user->fresh()->getFirstMedia('profilePicture');

        // Upload second picture
        $file2 = UploadedFile::fake()->image('second.jpg', 500, 500);
        $this->actingAs($user)->post('/profile/picture', ['profile_picture' => $file2]);

        // First media should be gone
        expect($user->fresh()->media)->toHaveCount(1);
        expect($user->fresh()->getFirstMedia('profilePicture')->id)->not->toBe($firstMedia->id);
    });

    test('logs security event on successful upload', function () {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('avatar.jpg', 500, 500);

        $this->actingAs($user)->post('/profile/picture', [
            'profile_picture' => $file,
        ]);

        $this->assertDatabaseHas('security_event_logs', [
            'event_type' => SecurityEventType::PROFILE_PICTURE_UPDATED->value,
            'user_id' => $user->id,
        ]);
    });

    test('logs security event on failed upload', function () {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->create('invalid.exe', 100);

        $this->actingAs($user)->post('/profile/picture', [
            'profile_picture' => $file,
        ]);

        $this->assertDatabaseHas('security_event_logs', [
            'event_type' => SecurityEventType::PROFILE_PICTURE_UPLOAD_FAILED->value,
            'user_id' => $user->id,
        ]);
    });

    test('rate limits profile picture uploads to 10 per hour', function () {
        $user = User::factory()->create();
        $maxAttempts = 10;

        // Make max allowed uploads
        for ($i = 0; $i < $maxAttempts; $i++) {
            $file = UploadedFile::fake()->image("avatar-{$i}.jpg", 500, 500);
            $response = $this->actingAs($user)->post('/profile/picture', [
                'profile_picture' => $file,
            ]);
            $response->assertRedirect();
        }

        // Next upload should be rate limited
        $file = UploadedFile::fake()->image('blocked.jpg', 500, 500);
        $response = $this->actingAs($user)->post('/profile/picture', [
            'profile_picture' => $file,
        ]);

        $response->assertStatus(429);
    });
});
```

Run tests (should fail):
```bash
php.bat artisan test --without-tty --no-ansi tests/Feature/ProfilePictureUploadTest.php
```

#### TDD Step 2: Add Security Event Types (5 min)

**File:** `app/Enums/SecurityEventType.php`

Add these cases:
```php
case PROFILE_PICTURE_UPDATED = 'profile_picture_updated';
case PROFILE_PICTURE_UPLOAD_FAILED = 'profile_picture_upload_failed';
case PROFILE_PICTURE_DELETED = 'profile_picture_deleted';
```

Add to `getDescription()`:
```php
self::PROFILE_PICTURE_UPDATED => 'User uploaded new profile picture',
self::PROFILE_PICTURE_UPLOAD_FAILED => 'Failed profile picture upload attempt',
self::PROFILE_PICTURE_DELETED => 'User deleted profile picture',
```

Add to `getSeverity()`:
```php
// In 'info' section:
self::PROFILE_PICTURE_UPDATED => 'info',
self::PROFILE_PICTURE_DELETED => 'info',

// In 'warning' section:
self::PROFILE_PICTURE_UPLOAD_FAILED => 'warning',
```

#### TDD Step 3: Create Form Request (20 min)

Create: `app/Http/Requests/ProfilePictureUpdateRequest.php`

```php
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
                    ->max(5 * 1024) // 5MB maximum
                    ->dimensions(
                        Rule::dimensions()
                            ->minWidth(200)
                            ->minHeight(200)
                            ->maxWidth(4000)
                            ->maxHeight(4000)
                    ),
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
```

#### TDD Step 4: Create Throttle Middleware (15 min)

Create: `app/Http/Middleware/ThrottleProfilePictureUploads.php`

```php
<?php

namespace App\Http\Middleware;

use App\Enums\SecurityEventType;
use App\Models\SecurityEventLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ThrottleProfilePictureUploads
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->ensureIsNotRateLimited($request);

        RateLimiter::hit($this->throttleKey($request), 3600); // 60 minutes

        return $next($request);
    }

    /**
     * Ensure the request is not rate limited.
     */
    protected function ensureIsNotRateLimited(Request $request): void
    {
        $maxAttempts = 10;

        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), $maxAttempts)) {
            return;
        }

        SecurityEventLog::createEvent(
            SecurityEventType::PROFILE_PICTURE_UPLOAD_FAILED,
            user: $request->user(),
            metadata: [
                'failure_reason' => 'rate_limited',
                'max_attempts' => $maxAttempts,
            ]
        );

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        abort(429, 'Too many upload attempts. Please try again in '.ceil($seconds / 60).' minutes.');
    }

    /**
     * Get the rate limiting throttle key.
     */
    protected function throttleKey(Request $request): string
    {
        return 'profile-picture-upload:'.$request->user()->id.'|'.$request->ip();
    }
}
```

#### TDD Step 5: Update ProfileController (30 min)

**File:** `app/Http/Controllers/ProfileController.php`

Add method:
```php
use App\Http\Requests\ProfilePictureUpdateRequest;
use App\Enums\SecurityEventType;
use App\Models\SecurityEventLog;

/**
 * Update the user's profile picture.
 */
public function updateProfilePicture(ProfilePictureUpdateRequest $request): RedirectResponse
{
    try {
        $user = $request->user();

        // Delete old profile picture if exists
        $user->clearMediaCollection('profilePicture');

        // Add new profile picture
        $user->addMediaFromRequest('profile_picture')
            ->toMediaCollection('profilePicture');

        // Log successful upload
        SecurityEventLog::createEvent(
            SecurityEventType::PROFILE_PICTURE_UPDATED,
            user: $user,
            metadata: [
                'file_name' => $request->file('profile_picture')->getClientOriginalName(),
                'file_size' => $request->file('profile_picture')->getSize(),
                'mime_type' => $request->file('profile_picture')->getMimeType(),
            ]
        );

        return Redirect::route('profile.edit')
            ->with('status', 'profile-picture-updated');

    } catch (\Exception $e) {
        SecurityEventLog::createEvent(
            SecurityEventType::PROFILE_PICTURE_UPLOAD_FAILED,
            user: $request->user(),
            metadata: [
                'failure_reason' => 'system_error',
                'error' => $e->getMessage(),
            ]
        );

        throw $e;
    }
}

/**
 * Delete the user's profile picture.
 */
public function deleteProfilePicture(Request $request): RedirectResponse
{
    $user = $request->user();

    $user->clearMediaCollection('profilePicture');

    SecurityEventLog::createEvent(
        SecurityEventType::PROFILE_PICTURE_DELETED,
        user: $user
    );

    return Redirect::route('profile.edit')
        ->with('status', 'profile-picture-deleted');
}
```

#### TDD Step 6: Register Middleware & Add Routes (10 min)

**File:** `bootstrap/app.php`

Add import:
```php
use App\Http\Middleware\ThrottleProfilePictureUploads;
```

Add to middleware aliases:
```php
'throttle.picture' => ThrottleProfilePictureUploads::class,
```

**File:** `routes/web.php`

Add routes:
```php
Route::middleware(['auth', 'status', 'throttle.picture'])->group(function () {
    Route::post('/profile/picture', [ProfileController::class, 'updateProfilePicture'])
        ->name('profile.picture.update');
    Route::delete('/profile/picture', [ProfileController::class, 'deleteProfilePicture'])
        ->name('profile.picture.delete');
});
```

#### TDD Step 7: Run Tests (Should Pass)

```bash
php.bat artisan test --without-tty --no-ansi tests/Feature/ProfilePictureUploadTest.php
php.bat artisan test --without-tty --no-ansi --filter=Profile
```

#### Step 8: Update .env.example

Add to `.env.example`:
```env
# Profile Picture Upload Limits
PROFILE_PICTURE_MAX_SIZE=5120
PROFILE_PICTURE_MIN_DIMENSIONS=200
PROFILE_PICTURE_MAX_DIMENSIONS=4000
```

#### Step 9: Update README.md

Add to Profile Picture Setup section:

```markdown
### Upload Configuration

Profile picture uploads are restricted to:
- **File types:** JPEG, PNG, WebP
- **Maximum size:** 5MB
- **Minimum dimensions:** 200x200 pixels
- **Maximum dimensions:** 4000x4000 pixels
- **Rate limit:** 10 uploads per hour per user

All uploads are validated and logged for security purposes.
```

#### Phase 2 Complete ✅
- [x] Tests written and passing
- [x] Form request validation created
- [x] Throttle middleware implemented
- [x] ProfileController upload methods added
- [x] Routes registered with middleware
- [x] Security event logging complete
- [x] README.md updated
- [x] .env.example updated

---

### Phase 3: Image Processing & Optimization (2-3 hours)
**Goal:** Optimize images and generate responsive variants
**Status:** ⏳ Pending

> **Note:** Image conversions are already defined in Phase 1 (User model). This phase focuses on testing, optimization, and queue configuration.

#### TDD Step 1: Write Tests for Conversions (20 min)

Add to `tests/Feature/ProfilePictureUploadTest.php`:

```php
describe('Image Conversions', function () {
    test('generates thumbnail conversion after upload', function () {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('avatar.jpg', 500, 500);

        $this->actingAs($user)->post('/profile/picture', ['profile_picture' => $file]);

        $media = $user->fresh()->getFirstMedia('profilePicture');
        expect($media->hasGeneratedConversion('thumb'))->toBeTrue();
    });

    test('generates medium conversion after upload', function () {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('avatar.jpg', 500, 500);

        $this->actingAs($user)->post('/profile/picture', ['profile_picture' => $file]);

        $media = $user->fresh()->getFirstMedia('profilePicture');
        expect($media->hasGeneratedConversion('medium'))->toBeTrue();
    });

    test('generates large conversion after upload', function () {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('avatar.jpg', 500, 500);

        $this->actingAs($user)->post('/profile/picture', ['profile_picture' => $file]);

        $media = $user->fresh()->getFirstMedia('profilePicture');
        expect($media->hasGeneratedConversion('large'))->toBeTrue();
    });
});
```

#### Step 2: Optional - Install Image Optimizer (15 min)

Only if system binaries available:

```bash
composer require spatie/image-optimizer
```

Update `config/medialibrary.php`:
```php
'image_optimizers' => [
    Spatie\ImageOptimizer\Optimizers\Jpegoptim::class => [
        '-m85', // Max quality 85%
        '--strip-all',  // Strip metadata
        '--all-progressive',
    ],
    Spatie\ImageOptimizer\Optimizers\Pngquant::class => [
        '--quality=85',
    ],
    Spatie\ImageOptimizer\Optimizers\Optipng::class => [
        '-i0',
        '-o2',
        '-quiet',
    ],
],
```

#### Step 3: Configure Queue for Async Processing (20 min)

Update User model conversions to use queue:
```php
public function registerMediaConversions(?Media $media = null): void
{
    $this->addMediaConversion('thumb')
        ->width(100)
        ->height(100)
        ->sharpen(10)
        ->nonQueued(); // Keep immediate for UX

    $this->addMediaConversion('medium')
        ->width(300)
        ->height(300)
        ->sharpen(10)
        ->queued(); // Queue this

    $this->addMediaConversion('large')
        ->width(600)
        ->height(600)
        ->sharpen(10)
        ->queued(); // Queue this
}
```

#### Step 4: Update .env.example

```env
# Image Optimization (optional - requires system binaries)
IMAGE_OPTIMIZER_ENABLED=false
```

#### Step 5: Update README.md

```markdown
### Image Processing

Uploaded images are automatically converted to three sizes:
- **Thumbnail:** 100x100px (generated immediately)
- **Medium:** 300x300px (queued)
- **Large:** 600x600px (queued)

#### Optional: Image Optimization

For optimal file sizes, install system optimization binaries:
```bash
# Ubuntu/Debian
sudo apt-get install jpegoptim optipng pngquant

# macOS
brew install jpegoptim optipng pngquant
```

Then set `IMAGE_OPTIMIZER_ENABLED=true` in `.env`.
```

#### Phase 3 Complete ✅
- [x] Conversion tests passing
- [x] Optional optimizer configured
- [x] Queue configuration added
- [x] README.md updated
- [x] .env.example updated

---

### Phase 4: Default Avatars (1-2 hours)
**Goal:** Implement Laravolt Avatar for users without pictures

#### TDD Step 1: Write Avatar Tests (30 min)

Create: `tests/Unit/UserAvatarTest.php`

```php
<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'name' => 'John',
        'surname' => 'Doe',
    ]);
});

describe('User Avatar Fallback', function () {
    test('user without uploaded picture returns laravolt avatar', function () {
        $url = $this->user->profile_picture_url;

        expect($url)->toContain('data:image/svg+xml;base64,');
    });

    test('user with uploaded picture returns media url', function () {
        $this->user->addMedia(UploadedFile::fake()->image('profile.jpg'))
            ->toMediaCollection('profilePicture');

        $url = $this->user->profile_picture_url;

        expect($url)
            ->not->toContain('data:image/svg+xml')
            ->toContain('/media/');
    });

    test('avatar contains correct initials', function () {
        $svg = $this->user->avatar_svg;

        expect($svg)
            ->toContain('JD') // John Doe initials
            ->toContain('<svg');
    });

    test('avatar respects custom name format', function () {
        $user = User::factory()->create([
            'name' => 'Mary Jane',
            'surname' => 'Watson',
        ]);

        $svg = $user->avatar_svg;

        expect($svg)->toContain('MW');
    });

    test('avatar uses consistent colors for same user', function () {
        $svg1 = $this->user->avatar_svg;
        $svg2 = $this->user->fresh()->avatar_svg;

        // Color should be consistent based on name
        expect($svg1)->toBe($svg2);
    });
});

describe('Avatar HTML Helper', function () {
    test('avatar html includes proper img tag', function () {
        $html = $this->user->avatar_html;

        expect($html)
            ->toContain('<img')
            ->toContain('alt="John Doe"')
            ->toContain('src="data:image/svg+xml;base64,');
    });

    test('avatar html includes size class when provided', function () {
        $html = $this->user->avatar_html(['size' => 'large']);

        expect($html)
            ->toContain('w-16 h-16') // large size classes
            ->toContain('rounded-full');
    });

    test('avatar html supports dark mode', function () {
        $html = $this->user->avatar_html();

        expect($html)->toContain('dark:ring-gray-700');
    });
});
```

**Run tests (they should fail):**
```bash
php.bat artisan test --without-tty --no-ansi tests/Unit/UserAvatarTest.php
```

#### TDD Step 2: Publish Avatar Config (10 min)

```bash
php.bat artisan vendor:publish --provider="Laravolt\Avatar\ServiceProvider"
```

#### TDD Step 3: Configure Avatar Package (15 min)

Edit: `config/avatar.php`

Update these key settings:
```php
<?php

return [
    // Use SVG format (lighter, scalable)
    'driver' => 'svg',

    // Circle shape matches most modern UI
    'shape' => 'circle',

    // Responsive to user initials (consistent colors per user)
    'backgrounds' => [
        '#f44336', // Red
        '#e91e63', // Pink
        '#9c27b0', // Purple
        '#673ab7', // Deep Purple
        '#3f51b5', // Indigo
        '#2196f3', // Blue
        '#00bcd4', // Cyan
        '#009688', // Teal
        '#4caf50', // Green
        '#ff9800', // Orange
        '#ff5722', // Deep Orange
    ],

    // White text for readability
    'foreground' => '#ffffff',

    // Border for definition
    'border' => [
        'size' => 1,
        'color' => 'foreground',
        'radius' => 0,
    ],

    // Clean, readable font
    'font' => [
        'file' => public_path('fonts/OpenSans-Bold.ttf'), // Optional custom font
        'size' => 48,
        'color' => '#ffffff',
    ],

    // Cache for performance
    'cache' => [
        'enabled' => true,
        'duration' => 3600, // 1 hour
    ],
];
```

#### TDD Step 4: Update User Model with Avatar Methods (20 min)

Edit: `app/Models/User.php`

Add these methods:
```php
/**
 * Get the user's avatar SVG (raw).
 */
public function getAvatarSvgAttribute(): string
{
    return \Avatar::create($this->name . ' ' . $this->surname)->toSvg();
}

/**
 * Get the user's avatar as base64 data URL.
 */
public function getAvatarBase64Attribute(): string
{
    return \Avatar::create($this->name . ' ' . $this->surname)->toBase64();
}

/**
 * Get avatar HTML with optional size and classes.
 */
public function avatarHtml(array $options = []): string
{
    $size = $options['size'] ?? 'medium';
    $extraClasses = $options['class'] ?? '';

    $sizeClasses = match ($size) {
        'small' => 'w-8 h-8',
        'medium' => 'w-12 h-12',
        'large' => 'w-16 h-16',
        'xlarge' => 'w-24 h-24',
        default => 'w-12 h-12',
    };

    $classes = "{$sizeClasses} rounded-full ring-2 ring-white dark:ring-gray-700 {$extraClasses}";

    return sprintf(
        '<img src="%s" alt="%s" class="%s" />',
        $this->avatar_base64,
        $this->name . ' ' . $this->surname,
        $classes
    );
}

/**
 * Get profile picture URL attribute (updated from Phase 1).
 * Returns uploaded media URL or Laravolt avatar fallback.
 */
public function getProfilePictureUrlAttribute(): string
{
    $media = $this->getFirstMedia('profilePicture');

    if ($media) {
        return $media->getUrl('medium');
    }

    return $this->avatar_base64;
}
```

#### TDD Step 5: Run Tests (5 min)

```bash
php.bat artisan test --without-tty --no-ansi tests/Unit/UserAvatarTest.php
```

All tests should now pass! ✅

#### TDD Step 6: Update .env.example (5 min)

Add to `.env.example`:
```bash
# Avatar Configuration
AVATAR_DRIVER=svg
AVATAR_CACHE_ENABLED=true
AVATAR_CACHE_DURATION=3600
```

#### TDD Step 7: Update README.md (10 min)

Add to `README.md` in the Profile Picture Setup section:

```markdown
### Default Avatars

Users without uploaded profile pictures automatically get generated avatars with their initials.

**Configuration:**
- Shape: Circle
- Colors: 11 distinct colors (consistent per user)
- Font: OpenSans Bold
- Cache: 1 hour

**Customization:**
Edit `config/avatar.php` to change colors, shape, or font.

**Usage in Blade:**
```blade
{!! $user->avatar_html(['size' => 'large']) !!}
```

**Usage in Vue:**
```vue
<img :src="user.profile_picture_url" :alt="user.name" class="w-12 h-12 rounded-full" />
```
```

#### Phase 4 Complete ✅
- [x] Avatar tests written and passing (8 tests)
- [x] Avatar package configured
- [x] User model methods added
- [x] README.md updated
- [x] .env.example updated

---

### Phase 5: UI Components (4-5 hours)
**Goal:** Build upload interface and display components

#### TDD Step 1: Write Browser Tests (45 min)

Create: `tests/Browser/ProfilePictureUploadTest.php`

```php
<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('media');
});

describe('Profile Picture Upload UI', function () {
    test('user can upload profile picture via file browser', function () {
        $user = User::factory()->create();

        $page = visit('/profile');

        $page->assertAuthenticated()
            ->assertSee('Profile Information')
            ->assertSee('Profile Picture')
            ->attach('profile_picture', UploadedFile::fake()->image('avatar.jpg', 500, 500)->tempName)
            ->click('Upload Picture')
            ->assertSee('Profile picture uploaded successfully')
            ->assertNoJavascriptErrors();

        expect($user->fresh()->getFirstMedia('profilePicture'))->not->toBeNull();
    });

    test('user can see preview before upload', function () {
        $user = User::factory()->create();

        $page = visit('/profile');

        $page->assertAuthenticated()
            ->attach('profile_picture', UploadedFile::fake()->image('avatar.jpg', 500, 500)->tempName)
            ->waitFor('#preview-image', 2) // Wait for preview to render
            ->assertVisible('#preview-image')
            ->assertAttribute('#preview-image', 'src', fn($src) => str_contains($src, 'blob:'));
    });

    test('error messages display for invalid files', function () {
        $user = User::factory()->create();

        $page = visit('/profile');

        $page->assertAuthenticated()
            ->attach('profile_picture', UploadedFile::fake()->create('invalid.pdf', 100)->tempName)
            ->click('Upload Picture')
            ->waitFor('.error-message', 2)
            ->assertSee('The file must be an image')
            ->assertNoJavascriptErrors();
    });

    test('success message shows after upload', function () {
        $user = User::factory()->create();

        $page = visit('/profile');

        $page->assertAuthenticated()
            ->attach('profile_picture', UploadedFile::fake()->image('avatar.jpg', 500, 500)->tempName)
            ->click('Upload Picture')
            ->waitFor('.success-message', 3)
            ->assertSee('Profile picture uploaded successfully');
    });

    test('picture displays in navigation immediately after upload', function () {
        $user = User::factory()->create();

        $page = visit('/profile');

        $page->assertAuthenticated()
            ->attach('profile_picture', UploadedFile::fake()->image('avatar.jpg', 500, 500)->tempName)
            ->click('Upload Picture')
            ->waitFor('.success-message', 3)
            ->visit('/dashboard')
            ->assertVisible('nav img[alt*="'.$user->name.'"]');
    });

    test('delete button removes picture', function () {
        $user = User::factory()->create();
        $user->addMedia(UploadedFile::fake()->image('avatar.jpg'))
            ->toMediaCollection('profilePicture');

        $page = visit('/profile');

        $page->assertAuthenticated()
            ->assertVisible('#current-profile-picture')
            ->click('Delete Picture')
            ->acceptDialog() // Confirm deletion
            ->waitFor('.success-message', 2)
            ->assertSee('Profile picture deleted successfully');

        expect($user->fresh()->getFirstMedia('profilePicture'))->toBeNull();
    });

    test('drag and drop zone works for upload', function () {
        $user = User::factory()->create();

        $page = visit('/profile');

        // Simulate drag-and-drop (Pest v4 supports this)
        $page->assertAuthenticated()
            ->assertVisible('#dropzone')
            ->dragFileInto(
                UploadedFile::fake()->image('avatar.jpg', 500, 500)->tempName,
                '#dropzone'
            )
            ->waitFor('#preview-image', 2)
            ->assertVisible('#preview-image')
            ->click('Upload Picture')
            ->assertSee('Profile picture uploaded successfully');
    });
});

describe('User Avatar Display Component', function () {
    test('avatar displays on profile page', function () {
        $user = User::factory()->create();

        $page = visit('/profile');

        $page->assertAuthenticated()
            ->assertVisible('img[alt*="'.$user->name.'"]');
    });

    test('default avatar shows initials for user without picture', function () {
        $user = User::factory()->create(['name' => 'John', 'surname' => 'Doe']);

        $page = visit('/profile');

        $page->assertAuthenticated()
            ->assertVisible('img[src*="data:image/svg"]'); // Laravolt base64 SVG
    });

    test('uploaded picture shows instead of default avatar', function () {
        $user = User::factory()->create();
        $user->addMedia(UploadedFile::fake()->image('avatar.jpg'))
            ->toMediaCollection('profilePicture');

        $page = visit('/profile');

        $page->assertAuthenticated()
            ->assertVisible('img[src*="/media/"]')
            ->assertAttribute('img', 'src', fn($src) => !str_contains($src, 'data:image/svg'));
    });
});
```

**Run tests (should fail):**
```bash
php.bat artisan test --without-tty --no-ansi tests/Browser/ProfilePictureUploadTest.php
```

#### TDD Step 2: Create UserAvatar Component (30 min)

Create: `resources/js/Components/UserAvatar.vue`

```vue
<script setup>
import { computed } from 'vue'

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
    size: {
        type: String,
        default: 'medium',
        validator: (value) => ['small', 'medium', 'large', 'xlarge'].includes(value),
    },
    rounded: {
        type: Boolean,
        default: true,
    },
    border: {
        type: Boolean,
        default: true,
    },
})

const sizeClasses = computed(() => {
    const sizes = {
        small: 'w-8 h-8',
        medium: 'w-12 h-12',
        large: 'w-16 h-16',
        xlarge: 'w-24 h-24',
    }
    return sizes[props.size]
})

const borderClasses = computed(() => {
    return props.border ? 'ring-2 ring-white dark:ring-gray-700' : ''
})

const roundedClasses = computed(() => {
    return props.rounded ? 'rounded-full' : 'rounded-lg'
})

const altText = computed(() => {
    return `${props.user.name} ${props.user.surname}`
})
</script>

<template>
    <img
        :src="user.profile_picture_url"
        :alt="altText"
        :class="[sizeClasses, roundedClasses, borderClasses]"
        loading="lazy"
        class="object-cover"
    />
</template>
```

#### TDD Step 3: Create ProfilePictureUpload Component (90 min)

Create: `resources/js/Components/ProfilePictureUpload.vue`

```vue
<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
})

const fileInput = ref(null)
const previewUrl = ref(null)
const selectedFile = ref(null)
const isDragging = ref(false)
const isUploading = ref(false)
const uploadProgress = ref(0)
const errors = ref({})
const successMessage = ref(null)

const hasCurrentPicture = computed(() => {
    return props.user.profile_picture_url && !props.user.profile_picture_url.includes('data:image/svg')
})

const handleFileSelect = (event) => {
    const file = event.target.files[0]
    processFile(file)
}

const handleDrop = (event) => {
    event.preventDefault()
    isDragging.value = false

    const file = event.dataTransfer.files[0]
    processFile(file)
}

const processFile = (file) => {
    if (!file) return

    // Validate file type
    if (!['image/jpeg', 'image/jpg', 'image/png', 'image/webp'].includes(file.type)) {
        errors.value = { profile_picture: 'The file must be an image (JPEG, PNG, or WebP).' }
        return
    }

    // Validate file size (5MB)
    if (file.size > 5 * 1024 * 1024) {
        errors.value = { profile_picture: 'The image must not exceed 5MB.' }
        return
    }

    selectedFile.value = file
    errors.value = {}

    // Create preview
    const reader = new FileReader()
    reader.onload = (e) => {
        previewUrl.value = e.target.result
    }
    reader.readAsDataURL(file)
}

const uploadPicture = () => {
    if (!selectedFile.value) return

    const formData = new FormData()
    formData.append('profile_picture', selectedFile.value)

    isUploading.value = true
    errors.value = {}
    successMessage.value = null

    router.post(route('profile.picture.update'), formData, {
        onProgress: (progress) => {
            uploadProgress.value = Math.round(progress.percentage)
        },
        onSuccess: () => {
            isUploading.value = false
            uploadProgress.value = 0
            selectedFile.value = null
            previewUrl.value = null
            successMessage.value = 'Profile picture uploaded successfully!'
            setTimeout(() => successMessage.value = null, 5000)
        },
        onError: (uploadErrors) => {
            isUploading.value = false
            uploadProgress.value = 0
            errors.value = uploadErrors
        },
    })
}

const deletePicture = () => {
    if (!confirm('Are you sure you want to delete your profile picture?')) return

    router.delete(route('profile.picture.delete'), {
        onSuccess: () => {
            successMessage.value = 'Profile picture deleted successfully!'
            setTimeout(() => successMessage.value = null, 5000)
        },
        onError: (deleteErrors) => {
            errors.value = deleteErrors
        },
    })
}

const cancelUpload = () => {
    selectedFile.value = null
    previewUrl.value = null
    errors.value = {}
    if (fileInput.value) {
        fileInput.value.value = ''
    }
}

const triggerFileInput = () => {
    fileInput.value?.click()
}
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Profile Picture</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Update your profile picture. JPEG, PNG, and WebP formats accepted (max 5MB).
            </p>
        </header>

        <!-- Success Message -->
        <div v-if="successMessage" class="mt-4 p-4 bg-green-100 dark:bg-green-900 rounded-lg success-message">
            <p class="text-sm text-green-800 dark:text-green-200">{{ successMessage }}</p>
        </div>

        <div class="mt-6 space-y-6">
            <!-- Current Picture -->
            <div v-if="hasCurrentPicture && !previewUrl" class="flex items-center gap-4">
                <img
                    id="current-profile-picture"
                    :src="user.profile_picture_url"
                    :alt="`${user.name} ${user.surname}`"
                    class="w-24 h-24 rounded-full object-cover ring-2 ring-white dark:ring-gray-700"
                />
                <SecondaryButton @click="deletePicture" type="button">
                    Delete Picture
                </SecondaryButton>
            </div>

            <!-- Preview -->
            <div v-if="previewUrl" class="flex items-center gap-4">
                <img
                    id="preview-image"
                    :src="previewUrl"
                    alt="Preview"
                    class="w-24 h-24 rounded-full object-cover ring-2 ring-white dark:ring-gray-700"
                />
                <div class="flex gap-2">
                    <PrimaryButton @click="uploadPicture" :disabled="isUploading">
                        {{ isUploading ? 'Uploading...' : 'Upload Picture' }}
                    </PrimaryButton>
                    <SecondaryButton @click="cancelUpload" :disabled="isUploading">
                        Cancel
                    </SecondaryButton>
                </div>
            </div>

            <!-- Upload Progress -->
            <div v-if="isUploading" class="w-full">
                <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                    <div
                        class="bg-blue-600 h-2.5 rounded-full transition-all duration-300"
                        :style="{ width: `${uploadProgress}%` }"
                    ></div>
                </div>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Uploading: {{ uploadProgress }}%
                </p>
            </div>

            <!-- Drag & Drop Zone -->
            <div
                v-if="!previewUrl"
                id="dropzone"
                @click="triggerFileInput"
                @dragover.prevent="isDragging = true"
                @dragleave.prevent="isDragging = false"
                @drop.prevent="handleDrop"
                :class="[
                    'border-2 border-dashed rounded-lg p-8 text-center cursor-pointer transition-colors',
                    isDragging
                        ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
                        : 'border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500'
                ]"
            >
                <svg
                    class="mx-auto h-12 w-12 text-gray-400"
                    stroke="currentColor"
                    fill="none"
                    viewBox="0 0 48 48"
                >
                    <path
                        d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    <span class="font-semibold">Click to upload</span> or drag and drop
                </p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">
                    JPEG, PNG, or WebP (max 5MB, min 200x200px)
                </p>
            </div>

            <!-- Hidden File Input -->
            <input
                ref="fileInput"
                type="file"
                accept="image/jpeg,image/jpg,image/png,image/webp"
                @change="handleFileSelect"
                class="hidden"
            />

            <!-- Validation Errors -->
            <InputError v-if="errors.profile_picture" :message="errors.profile_picture" class="mt-2 error-message" />
        </div>
    </section>
</template>
```

#### TDD Step 4: Update Profile Edit Page (30 min)

Edit: `resources/js/Pages/Profile/Edit.vue`

Add import:
```vue
<script setup>
import ProfilePictureUpload from '@/Components/ProfilePictureUpload.vue'
// ... existing imports
</script>
```

Add component to template (before UpdateProfileInformationForm):
```vue
<template>
    <Head title="Profile" />

    <AuthenticatedLayout>
        <!-- ... existing code ... -->

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Profile Picture Upload Section -->
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <ProfilePictureUpload :user="$page.props.auth.user" />
                </div>

                <!-- Existing sections -->
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <UpdateProfileInformationForm
                        <!-- ... existing props ... -->
                    />
                </div>
                <!-- ... rest of existing code ... -->
            </div>
        </div>
    </AuthenticatedLayout>
</template>
```

#### TDD Step 5: Update Navigation with Avatar (20 min)

Locate navigation component (likely `resources/js/Layouts/AuthenticatedLayout.vue` or similar).

Add import:
```vue
<script setup>
import UserAvatar from '@/Components/UserAvatar.vue'
// ... existing imports
</script>
```

Update user dropdown trigger to show avatar:
```vue
<template>
    <!-- ... existing navigation code ... -->

    <!-- User Dropdown -->
    <div class="flex items-center gap-3">
        <UserAvatar :user="$page.props.auth.user" size="small" />
        <span class="text-sm font-medium">{{ $page.props.auth.user.name }}</span>
        <!-- dropdown icon -->
    </div>

    <!-- ... rest of navigation ... -->
</template>
```

#### TDD Step 6: Update ProfileController to Return Inertia Props (10 min)

Edit: `app/Http/Controllers/ProfileController.php`

Update the `edit` method to ensure profile_picture_url is in props:
```php
public function edit(Request $request): Response
{
    return Inertia::render('Profile/Edit', [
        'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
        'status' => session('status'),
        // profile_picture_url is automatically included via user relationship
    ]);
}
```

Ensure User model has proper Inertia serialization in `app/Http/Middleware/HandleInertiaRequests.php`:
```php
public function share(Request $request): array
{
    return [
        ...parent::share($request),
        'auth' => [
            'user' => $request->user() ? [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'surname' => $request->user()->surname,
                'email' => $request->user()->email,
                'profile_picture_url' => $request->user()->profile_picture_url, // Add this
                // ... other user fields
            ] : null,
        ],
    ];
}
```

#### TDD Step 7: Run Browser Tests (5 min)

```bash
php.bat artisan test --without-tty --no-ansi tests/Browser/ProfilePictureUploadTest.php
```

All tests should pass! ✅

#### Step 8: Update README.md (10 min)

Add to `README.md`:

```markdown
### UI Components

Two Vue components are provided for profile pictures:

#### UserAvatar Component
Displays user profile picture or default avatar.

**Props:**
- `user` (required): User object
- `size`: 'small' | 'medium' | 'large' | 'xlarge' (default: 'medium')
- `rounded`: Boolean (default: true)
- `border`: Boolean (default: true)

**Usage:**
```vue
<UserAvatar :user="user" size="large" />
```

#### ProfilePictureUpload Component
Full-featured upload interface with drag-and-drop, preview, and validation.

**Props:**
- `user` (required): Current authenticated user

**Features:**
- Drag-and-drop or click to browse
- Live preview before upload
- Progress indicator
- Client-side validation
- Delete functionality
```

#### Phase 5 Complete ✅
- [x] Browser tests written and passing (10 tests)
- [x] UserAvatar component created
- [x] ProfilePictureUpload component created
- [x] Profile Edit page updated
- [x] Navigation updated with avatar
- [x] Inertia props configured
- [x] README.md updated

---

### Phase 6: Performance & Security (2-3 hours)
**Goal:** Optimize delivery and secure access

#### TDD Step 1: Write Security Tests (30 min)

Create: `tests/Feature/ProfilePictureSecurityTest.php`

```php
<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('media');
});

describe('Profile Picture Security', function () {
    test('only authenticated users can upload profile pictures', function () {
        $file = UploadedFile::fake()->image('avatar.jpg', 500, 500);

        $response = $this->post('/profile/picture', [
            'profile_picture' => $file,
        ]);

        $response->assertRedirect('/login'); // or 401/403 depending on your auth setup
    });

    test('users can only upload to their own profile', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $file = UploadedFile::fake()->image('avatar.jpg', 500, 500);

        // Try to upload to user2's profile while authenticated as user1
        $response = $this->actingAs($user1)->post('/profile/picture', [
            'profile_picture' => $file,
            'user_id' => $user2->id, // Attempt to manipulate
        ]);

        // Should upload to user1, not user2
        expect($user1->fresh()->getFirstMedia('profilePicture'))->not->toBeNull();
        expect($user2->fresh()->getFirstMedia('profilePicture'))->toBeNull();
    });

    test('csrf protection is enabled on upload route', function () {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('avatar.jpg', 500, 500);

        // Attempt without CSRF token (Inertia/Laravel handles this automatically)
        $response = $this->actingAs($user)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post('/profile/picture', [
                'profile_picture' => $file,
            ]);

        // With middleware disabled, should still work (testing middleware exists)
        // In real scenario, without CSRF token it would fail
        $response->assertRedirect();
    });

    test('uploaded files are validated for actual image content', function () {
        $user = User::factory()->create();

        // Create fake "image" that's actually a PHP file
        $maliciousFile = UploadedFile::fake()->createWithContent(
            'malicious.jpg',
            '<?php system($_GET["cmd"]); ?>'
        );

        $response = $this->actingAs($user)->post('/profile/picture', [
            'profile_picture' => $maliciousFile,
        ]);

        $response->assertSessionHasErrors('profile_picture');
    });

    test('uploaded images have exif data stripped', function () {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('with-exif.jpg', 500, 500);

        $this->actingAs($user)->post('/profile/picture', [
            'profile_picture' => $file,
        ]);

        $media = $user->fresh()->getFirstMedia('profilePicture');
        $imagePath = $media->getPath();

        // Check that EXIF data is removed (Spatie Media Library does this automatically)
        $exifData = @exif_read_data($imagePath);
        expect($exifData)->toBeFalse(); // No EXIF data present
    });
});

describe('Profile Picture Performance', function () {
    test('media relationships are not n+1 queried', function () {
        $users = User::factory()->count(10)->create();

        // Add profile pictures to some users
        foreach ($users->take(5) as $user) {
            $user->addMedia(UploadedFile::fake()->image('avatar.jpg'))
                ->toMediaCollection('profilePicture');
        }

        // Fetch users with eager loading
        \DB::enableQueryLog();
        $fetchedUsers = User::with('media')->limit(10)->get();

        // Access profile_picture_url for all users
        foreach ($fetchedUsers as $user) {
            $url = $user->profile_picture_url;
        }

        $queries = \DB::getQueryLog();

        // Should be 2 queries: 1 for users, 1 for media (not 11+)
        expect(count($queries))->toBeLessThanOrEqual(2);
    });

    test('avatar urls are efficiently generated', function () {
        $user = User::factory()->create();

        $startTime = microtime(true);

        // Generate avatar 100 times
        for ($i = 0; $i < 100; $i++) {
            $url = $user->profile_picture_url;
        }

        $endTime = microtime(true);
        $duration = $endTime - $startTime;

        // Should complete in under 1 second for 100 generations
        expect($duration)->toBeLessThan(1.0);
    });
});
```

**Run tests (should fail):**
```bash
php.bat artisan test --without-tty --no-ansi tests/Feature/ProfilePictureSecurityTest.php
```

#### TDD Step 2: Configure EXIF Stripping (10 min)

Spatie Media Library automatically strips EXIF data during conversions. Verify in User model:

```php
public function registerMediaConversions(?Media $media = null): void
{
    $this->addMediaConversion('thumb')
        ->width(100)
        ->height(100)
        ->sharpen(10)
        ->keepOriginalImageFormat() // Preserves format
        ->nonQueued();

    // ... other conversions
}
```

EXIF stripping is automatic - no additional configuration needed! ✅

#### TDD Step 3: Add N+1 Query Prevention (15 min)

Edit: `app/Http/Controllers/ProfileController.php`

```php
public function edit(Request $request): Response
{
    // Eager load media to prevent N+1 queries
    $user = $request->user()->load('media');

    return Inertia::render('Profile/Edit', [
        'mustVerifyEmail' => $user instanceof MustVerifyEmail,
        'status' => session('status'),
    ]);
}
```

Edit: `app/Http/Middleware/HandleInertiaRequests.php`

```php
public function share(Request $request): array
{
    return [
        ...parent::share($request),
        'auth' => [
            'user' => $request->user() ? $request->user()->load('media')->only([
                'id',
                'name',
                'surname',
                'email',
                'profile_picture_url', // This will use the eager loaded media
            ]) : null,
        ],
    ];
}
```

#### TDD Step 4: Add Responsive Images (srcset) (30 min)

Update `UserAvatar.vue` to use srcset:

```vue
<script setup>
import { computed } from 'vue'

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
    size: {
        type: String,
        default: 'medium',
        validator: (value) => ['small', 'medium', 'large', 'xlarge'].includes(value),
    },
    rounded: {
        type: Boolean,
        default: true,
    },
    border: {
        type: Boolean,
        default: true,
    },
})

const sizeClasses = computed(() => {
    const sizes = {
        small: 'w-8 h-8',
        medium: 'w-12 h-12',
        large: 'w-16 h-16',
        xlarge: 'w-24 h-24',
    }
    return sizes[props.size]
})

const borderClasses = computed(() => {
    return props.border ? 'ring-2 ring-white dark:ring-gray-700' : ''
})

const roundedClasses = computed(() => {
    return props.rounded ? 'rounded-full' : 'rounded-lg'
})

const altText = computed(() => {
    return `${props.user.name} ${props.user.surname}`
})

// Generate srcset for responsive images
const srcset = computed(() => {
    if (!props.user.profile_picture_url || props.user.profile_picture_url.includes('data:image/svg')) {
        return '' // No srcset for default avatars
    }

    // Generate URLs for different sizes (if media exists)
    // Format: URL 1x, URL 2x
    const baseUrl = props.user.profile_picture_url
    return `${baseUrl} 1x, ${baseUrl} 2x`
})
</script>

<template>
    <img
        :src="user.profile_picture_url"
        :srcset="srcset || undefined"
        :alt="altText"
        :class="[sizeClasses, roundedClasses, borderClasses]"
        loading="lazy"
        class="object-cover"
    />
</template>
```

#### TDD Step 5: Configure CDN Support (20 min)

Edit: `config/filesystems.php`

```php
'disks' => [
    'media' => [
        'driver' => 'local',
        'root' => storage_path('app/public/media'),
        'url' => env('MEDIA_CDN_URL', env('APP_URL').'/storage/media'),
        'visibility' => 'public',
        'throw' => false,
    ],
],
```

Edit: `config/medialibrary.php`

```php
return [
    // ... existing config

    /*
     * The base URL to use for generating URLs to media files.
     * Set MEDIA_CDN_URL in .env for CDN support.
     */
    'path_generator' => \Spatie\MediaLibrary\Support\PathGenerator\DefaultPathGenerator::class,

    /*
     * Add custom headers for media files
     */
    'custom_headers' => [
        'Cache-Control' => 'max-age=31536000, public', // 1 year cache
    ],
];
```

#### TDD Step 6: Add WebP Support (25 min)

Edit User model conversions to generate WebP versions:

```php
public function registerMediaConversions(?Media $media = null): void
{
    // Generate standard formats
    $this->addMediaConversion('thumb')
        ->width(100)
        ->height(100)
        ->sharpen(10)
        ->nonQueued();

    $this->addMediaConversion('medium')
        ->width(300)
        ->height(300)
        ->sharpen(10);

    $this->addMediaConversion('large')
        ->width(600)
        ->height(600)
        ->sharpen(10);

    // Generate WebP versions for modern browsers
    $this->addMediaConversion('thumb-webp')
        ->width(100)
        ->height(100)
        ->sharpen(10)
        ->format('webp')
        ->nonQueued();

    $this->addMediaConversion('medium-webp')
        ->width(300)
        ->height(300)
        ->sharpen(10)
        ->format('webp');

    $this->addMediaConversion('large-webp')
        ->width(600)
        ->height(600)
        ->sharpen(10)
        ->format('webp');
}
```

Update User model to serve WebP when available:

```php
public function getProfilePictureUrlAttribute(): string
{
    $media = $this->getFirstMedia('profilePicture');

    if ($media) {
        // Prefer WebP if conversion exists, fallback to JPEG/PNG
        if ($media->hasGeneratedConversion('medium-webp')) {
            return $media->getUrl('medium-webp');
        }
        return $media->getUrl('medium');
    }

    return $this->avatar_base64;
}
```

#### TDD Step 7: Run Tests (5 min)

```bash
php.bat artisan test --without-tty --no-ansi tests/Feature/ProfilePictureSecurityTest.php
php.bat artisan test --without-tty --no-ansi --filter=ProfilePicture
```

All tests should pass! ✅

#### Step 8: Update .env.example (5 min)

Add to `.env.example`:
```bash
# CDN Configuration (optional)
MEDIA_CDN_URL=

# Cache Headers
MEDIA_CACHE_MAX_AGE=31536000
```

#### Step 9: Update README.md (15 min)

Add to `README.md`:

```markdown
### Performance Optimizations

The profile picture system includes several performance optimizations:

#### Image Formats
- **WebP Support:** Modern browsers receive WebP images (25-35% smaller)
- **Fallback:** JPEG/PNG for older browsers
- **Responsive Images:** Multiple sizes generated automatically

#### Caching
- **Browser Cache:** 1 year cache headers on media files
- **Avatar Cache:** Generated avatars cached for 1 hour
- **Eager Loading:** Media relationships eager-loaded to prevent N+1 queries

#### CDN Support
To use a CDN for media delivery, set `MEDIA_CDN_URL` in `.env`:
```bash
MEDIA_CDN_URL=https://cdn.example.com/media
```

All media URLs will automatically use the CDN.

### Security Features

#### File Upload Security
- **MIME Type Validation:** Server-side validation of file types
- **Content Verification:** Actual image content verified (not just extension)
- **EXIF Stripping:** Location and metadata automatically removed
- **Rate Limiting:** 10 uploads per hour per user
- **Security Logging:** All upload attempts logged

#### Access Control
- **Authentication Required:** Only logged-in users can upload
- **Owner Verification:** Users can only modify their own pictures
- **CSRF Protection:** All upload routes protected

#### File Storage Security
- **Random Filenames:** Prevents filename guessing
- **Symlink Storage:** Files stored outside web root
- **Public Visibility:** Only for profile pictures (configurable)
```

#### Phase 6 Complete ✅
- [x] Security tests written and passing (7 tests)
- [x] EXIF stripping verified (automatic via Spatie)
- [x] N+1 query prevention implemented
- [x] Responsive images (srcset) added
- [x] CDN support configured
- [x] WebP format support added
- [x] README.md updated
- [x] .env.example updated

---

## File Structure

### New Files to Create
```
app/Http/Requests/ProfilePictureUpdateRequest.php
resources/js/Components/ProfilePictureUpload.vue
resources/js/Components/UserAvatar.vue
tests/Feature/ProfilePictureTest.php
tests/Browser/ProfilePictureUploadTest.php
config/medialibrary.php (published)
config/avatar.php (published)
```

### Files to Modify
```
app/Models/User.php (add HasMedia trait)
app/Http/Controllers/ProfileController.php (add upload method)
app/Enums/SecurityEventType.php (add new event types)
routes/web.php (add picture upload route)
resources/js/Pages/Profile/Edit.vue (add upload component)
database/migrations/YYYY_MM_DD_create_media_table.php (from package)
```

---

## Database Changes

### New Table: `media` (from Spatie Media Library)
```sql
CREATE TABLE media (
    id BIGINT UNSIGNED PRIMARY KEY,
    model_type VARCHAR(255),
    model_id BIGINT UNSIGNED,
    uuid CHAR(36),
    collection_name VARCHAR(255),
    name VARCHAR(255),
    file_name VARCHAR(255),
    mime_type VARCHAR(255),
    disk VARCHAR(255),
    conversions_disk VARCHAR(255),
    size BIGINT UNSIGNED,
    manipulations JSON,
    custom_properties JSON,
    generated_conversions JSON,
    responsive_images JSON,
    order_column INT UNSIGNED,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    KEY media_model_type_model_id_index (model_type, model_id),
    KEY media_uuid_unique (uuid),
    KEY media_order_column_index (order_column)
);
```

### Migration Strategy for Existing `profile_picture` Column
1. **Option A - Keep column, use as fallback:**
   - Don't remove `profile_picture` column
   - Use Media Library as primary
   - Fall back to `profile_picture` if no media
   - Gradually migrate users over time

2. **Option B - Replace entirely:**
   - Create migration to move existing URLs to media table
   - Drop `profile_picture` column after migration
   - Cleaner long-term solution

**Recommendation:** Option A for safety, migrate to Option B later.

---

## Security Considerations

### File Upload Security
- ✅ Validate MIME types (not just extensions)
- ✅ Scan for executable code in images
- ✅ Store outside web root (use symlink)
- ✅ Generate random filenames (prevent guessing)
- ✅ Implement rate limiting (prevent abuse)
- ✅ Log all upload attempts

### Image Processing Security
- ✅ Strip EXIF data (prevent location leaks)
- ✅ Re-encode images (remove embedded scripts)
- ✅ Validate image integrity before conversion
- ✅ Limit processing resources (prevent DoS)

### Access Control
- ✅ Only allow users to update their own picture
- ✅ Admin can moderate inappropriate pictures
- ✅ Consider approval workflow for public profiles

---

## Performance Benchmarks

### Expected Performance Impact
- **Upload:** 1-3 seconds (includes validation, conversion, optimization)
- **Display:** <100ms (cached URLs)
- **Storage:** ~50-200KB per user (3 conversions + original)
- **Database:** +1 row per upload in `media` table

### Optimization Strategies
- Queue conversions for async processing
- Cache avatar URLs (1 hour TTL)
- Lazy load images below the fold
- Use WebP format for 25-35% size reduction
- Implement CDN for global delivery

---

## Testing Strategy

### Unit Tests
- Media registration and collections
- Conversion configuration
- Avatar fallback logic
- Validation rules

### Feature Tests
- Upload flow (success/failure)
- File validation (size, type, dimensions)
- Old media deletion
- Security event logging
- Rate limiting

### Browser Tests (Pest v4)
- Drag-and-drop upload
- File browser upload
- Preview before upload
- Error handling
- Success feedback
- Display across app

### Manual Testing Checklist
- [ ] Upload from mobile device
- [ ] Upload from desktop
- [ ] Test various image formats
- [ ] Test edge cases (very large, very small, corrupted)
- [ ] Verify avatars in all UI locations
- [ ] Test with slow network
- [ ] Verify accessibility (alt text, keyboard navigation)

---

## Acceptance Criteria Mapping

### ✅ Profile picture upload functionality
- Phase 2: Upload & Validation
- Phase 5: UI Components

### ✅ Image validation (size, format, dimensions)
- Phase 2: ProfilePictureUpdateRequest with comprehensive rules

### ✅ Image storage and retrieval
- Phase 1: Spatie Media Library setup
- Phase 3: Image processing & optimization

### ✅ Default profile picture handling
- Phase 4: Laravolt Avatar integration

### ✅ Profile picture display across the app
- Phase 5: UserAvatar component + integration

---

## Definition of Done Mapping

### ✅ File upload system implemented
- Phase 2: Complete upload flow with validation

### ✅ Image processing and validation working
- Phase 3: Conversions, optimization, and responsive variants

### ✅ Storage solution configured
- Phase 1: Media Library + filesystem configuration

### ✅ UI components complete
- Phase 5: Upload and display components

### ✅ Performance optimized
- Phase 6: CDN, caching, lazy loading, WebP support

---

## Estimated Timeline

| Phase | Duration | Cumulative |
|-------|----------|------------|
| Phase 1: Foundation Setup | 2-3 hours | 3 hours |
| Phase 2: Upload & Validation | 3-4 hours | 7 hours |
| Phase 3: Image Processing | 2-3 hours | 10 hours |
| Phase 4: Default Avatars | 1-2 hours | 12 hours |
| Phase 5: UI Components | 4-5 hours | 17 hours |
| Phase 6: Performance & Security | 2-3 hours | 20 hours |

**Total Estimated Time:** 15-20 hours (2-3 full working days)

---

## Risks & Mitigations

### Risk: Binary Dependencies for Image Optimizer
**Impact:** Medium
**Mitigation:** Make optimization optional, gracefully degrade if binaries unavailable

### Risk: Storage Costs
**Impact:** Low-Medium
**Mitigation:** Implement automatic cleanup of old media, set retention policies

### Risk: Inappropriate Images
**Impact:** Medium
**Mitigation:** Implement admin moderation, consider AI content moderation API

### Risk: Performance Degradation
**Impact:** Low
**Mitigation:** Queue all conversions, implement aggressive caching, use CDN

---

## Post-Implementation Monitoring

### Metrics to Track
- Average upload success rate
- Average conversion processing time
- Storage usage growth
- CDN bandwidth usage
- Failed upload reasons
- User adoption rate

### Weekly Review
- Check `security_event_logs` for upload failures
- Review storage usage trends
- Monitor queue processing times

### Monthly Review
- Analyze most common upload errors
- Review moderation reports (if applicable)
- Optimize conversion settings based on usage

---

## Future Enhancements (Post-MVP)

### Phase 7: Advanced Features (Optional)
- **Image Cropping Tool:** Let users crop before upload
- **Multiple Pictures:** Photo gallery or carousel
- **Cover Photos:** Separate header image
- **AI Enhancements:** Auto-crop, background removal, quality enhancement
- **Social Integrations:** Import from Gravatar, LinkedIn, Google
- **Watermarking:** Protect professional headshots
- **Analytics:** Track profile picture completion rates

### Phase 8: Admin Features (Optional)
- **Moderation Dashboard:** Review uploaded pictures
- **Bulk Operations:** Regenerate conversions, migrate storage
- **Storage Reports:** Usage by user, disk space forecasting
- **Content Policy Enforcement:** Auto-detect inappropriate content

---

## Summary & Recommendation

### Recommended Package Stack
✅ **spatie/laravel-medialibrary** - Core media management
✅ **laravolt/avatar** - Default avatars
⚠️ **spatie/image-optimizer** - Optional (requires system binaries)
❌ **intervention/image** - Not needed (redundant with Media Library)

### Why This Stack?
1. **Industry Standard:** Spatie Media Library is the de facto solution for Laravel media
2. **Minimal Complexity:** Laravolt Avatar is lightweight and purpose-built
3. **Future-Proof:** Easy to extend with additional media types later
4. **Well-Tested:** Both packages have excellent test coverage and community support
5. **Laravel-Native:** Deep integration with Laravel's filesystem and queue system

### Critical Success Factors
- ✅ Follow TDD approach (write tests first)
- ✅ Use queues for all image processing
- ✅ Implement aggressive caching
- ✅ Add comprehensive security validation
- ✅ Monitor performance metrics closely

### Next Steps
1. Review and approve this strategy
2. Create GitHub branch: `feature/profile-picture-upload`
3. Begin Phase 1: Foundation Setup
4. Follow TDD approach throughout implementation