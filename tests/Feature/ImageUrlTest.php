<?php

use App\Models\Image;
use Illuminate\Support\Facades\Storage;

it('builds root-relative image urls that work across hosts', function () {
    Storage::fake('public');

    $image = Image::factory()->create([
        'file_path' => 'images/mobile-check.jpg',
    ]);

    Storage::disk('public')->put($image->file_path, 'fake-image');

    expect($image->url())
        ->toStartWith('/storage/')
        ->toEndWith('images/mobile-check.jpg')
        ->not->toContain('localhost')
        ->not->toContain('http://')
        ->not->toContain('https://');
});

it('returns null when the image file is missing', function () {
    Storage::fake('public');

    $image = Image::factory()->create([
        'file_path' => 'images/missing.jpg',
    ]);

    expect($image->url())->toBeNull();
});
