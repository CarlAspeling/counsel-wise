<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;

beforeEach(function () {
    $this->user = User::factory()->create([
        'name' => 'John',
        'surname' => 'Doe',
    ]);
});

describe('User Avatar Fallback', function () {
    test('user without uploaded picture returns laravolt avatar', function () {
        $url = $this->user->profile_picture_url;

        expect($url)->toContain('data:image/png;base64,');
    });

    test('user with uploaded picture returns media url', function () {
        $this->user->addMedia(UploadedFile::fake()->image('profile.jpg', 500, 500)->size(100))
            ->toMediaCollection('profilePicture');

        $url = $this->user->fresh()->profile_picture_url;

        expect($url)
            ->not->toContain('data:image/')
            ->toContain('/media/');
    });

    test('avatar is generated for user without picture', function () {
        $url = $this->user->profile_picture_url;

        // Avatar should be a data URL (base64 encoded image)
        expect($url)->toStartWith('data:image/');
        expect($url)->toContain('base64,');
    });

    test('different users get different avatar colors', function () {
        $user1 = User::factory()->create(['name' => 'John', 'surname' => 'Doe']);
        $user2 = User::factory()->create(['name' => 'Jane', 'surname' => 'Smith']);

        $url1 = $user1->profile_picture_url;
        $url2 = $user2->profile_picture_url;

        // Different users should get different avatars
        expect($url1)->not->toBe($url2);
    });

    test('avatar uses consistent output for same user', function () {
        $url1 = $this->user->profile_picture_url;
        $url2 = $this->user->fresh()->profile_picture_url;

        // URLs should be identical for same user
        expect($url1)->toBe($url2);
    });
});

describe('Avatar with Different Sizes', function () {
    test('getAvatar returns thumb conversion for uploaded picture', function () {
        $this->user->addMedia(UploadedFile::fake()->image('profile.jpg', 500, 500)->size(100))
            ->toMediaCollection('profilePicture');

        $url = $this->user->fresh()->getAvatar('thumb');

        expect($url)
            ->not->toContain('data:image/svg+xml')
            ->toContain('/media/')
            ->toContain('/conversions/');
    });

    test('getAvatar returns medium conversion by default', function () {
        $this->user->addMedia(UploadedFile::fake()->image('profile.jpg', 500, 500)->size(100))
            ->toMediaCollection('profilePicture');

        $url = $this->user->fresh()->getAvatar();

        expect($url)
            ->not->toContain('data:image/svg+xml')
            ->toContain('/media/');
    });

    test('getAvatar returns large conversion for uploaded picture', function () {
        $this->user->addMedia(UploadedFile::fake()->image('profile.jpg', 500, 500)->size(100))
            ->toMediaCollection('profilePicture');

        $url = $this->user->fresh()->getAvatar('large');

        expect($url)
            ->not->toContain('data:image/svg+xml')
            ->toContain('/media/')
            ->toContain('/conversions/');
    });

    test('getAvatar returns fallback avatar for user without picture', function () {
        $url = $this->user->getAvatar('medium');

        expect($url)->toContain('data:image/png;base64,');
    });
});
