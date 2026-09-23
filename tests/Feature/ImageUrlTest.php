<?php

use App\Models\Image;
use Illuminate\Support\Facades\Storage;

it('builds image urls from APP_URL including subdirectory paths', function () {
    Storage::fake('public');
    config(['app.url' => 'https://example.test/fumikura']);

    $image = Image::factory()->create([
        'file_path' => 'images/mobile-check.jpg',
    ]);

    Storage::disk('public')->put($image->file_path, 'fake-image');

    expect($image->url())->toBe('https://example.test/fumikura/storage/images/mobile-check.jpg');
});

it('returns null when the image file is missing', function () {
    Storage::fake('public');

    $image = Image::factory()->create([
        'file_path' => 'images/missing.jpg',
    ]);

    expect($image->url())->toBeNull();
});
