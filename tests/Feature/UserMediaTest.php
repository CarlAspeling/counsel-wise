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

    expect($user->profile_picture_url)->toStartWith('data:image');
});
