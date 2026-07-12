<?php

use App\Models\Image;
use App\Models\Story;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\PaginationDemoSeeder;

it('adds dummy images and stories for pagination demo', function () {
    $this->seed(DatabaseSeeder::class);

    $imageCountBefore = Image::query()->count();
    $testUser = User::query()->where('email', 'test@example.com')->firstOrFail();
    $storyCountBefore = Story::query()->where('user_id', $testUser->id)->count();

    $this->seed(PaginationDemoSeeder::class);

    expect(Image::query()->count())->toBe($imageCountBefore + 10)
        ->and(Story::query()->where('user_id', $testUser->id)->count())->toBe($storyCountBefore + 10)
        ->and(Image::query()->where('title', 'like', '【ダミー】お題%')->count())->toBe(10)
        ->and(Story::query()->where('user_id', $testUser->id)->where('title', 'like', '【ダミー】記事%')->count())->toBe(10);
});
