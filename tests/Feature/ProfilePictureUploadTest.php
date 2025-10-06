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
        $file = UploadedFile::fake()->image('avatar.jpg', 500, 500)->size(100); // 100KB

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
        $file = UploadedFile::fake()->image('giant.jpg', 4500, 4500);

        $response = $this->actingAs($user)->post('/profile/picture', [
            'profile_picture' => $file,
        ]);

        $response->assertSessionHasErrors('profile_picture');
    })->skip('Skipping to avoid memory issues in test environment');

    test('deletes old profile picture when uploading new one', function () {
        $user = User::factory()->create();

        // Upload first picture
        $file1 = UploadedFile::fake()->image('first.jpg', 500, 500)->size(100);
        $this->actingAs($user)->post('/profile/picture', ['profile_picture' => $file1]);
        $firstMedia = $user->fresh()->getFirstMedia('profilePicture');

        // Upload second picture
        $file2 = UploadedFile::fake()->image('second.jpg', 500, 500)->size(100);
        $this->actingAs($user)->post('/profile/picture', ['profile_picture' => $file2]);

        // First media should be gone
        expect($user->fresh()->media)->toHaveCount(1);
        expect($user->fresh()->getFirstMedia('profilePicture')->id)->not->toBe($firstMedia->id);
    });

    test('logs security event on successful upload', function () {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('avatar.jpg', 500, 500)->size(100);

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
            $file = UploadedFile::fake()->image("avatar-{$i}.jpg", 200, 200)->size(100);
            $response = $this->actingAs($user)->post('/profile/picture', [
                'profile_picture' => $file,
            ]);
            $response->assertRedirect();
        }

        // Next upload should be rate limited
        $file = UploadedFile::fake()->image('blocked.jpg', 200, 200)->size(100);
        $response = $this->actingAs($user)->post('/profile/picture', [
            'profile_picture' => $file,
        ]);

        $response->assertStatus(429);
    });
});

describe('Image Conversions', function () {
    test('generates thumbnail conversion after upload', function () {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('avatar.jpg', 500, 500)->size(100);

        $this->actingAs($user)->post('/profile/picture', ['profile_picture' => $file]);

        $media = $user->fresh()->getFirstMedia('profilePicture');
        expect($media->hasGeneratedConversion('thumb'))->toBeTrue();
    });

    test('generates medium conversion after upload', function () {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('avatar.jpg', 500, 500)->size(100);

        $this->actingAs($user)->post('/profile/picture', ['profile_picture' => $file]);

        $media = $user->fresh()->getFirstMedia('profilePicture');
        expect($media->hasGeneratedConversion('medium'))->toBeTrue();
    });

    test('generates large conversion after upload', function () {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('avatar.jpg', 500, 500)->size(100);

        $this->actingAs($user)->post('/profile/picture', ['profile_picture' => $file]);

        $media = $user->fresh()->getFirstMedia('profilePicture');
        expect($media->hasGeneratedConversion('large'))->toBeTrue();
    });
});
