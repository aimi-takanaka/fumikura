<?php

use App\Models\Bookmark;
use App\Models\Comment;
use App\Models\Image;
use App\Models\Like;
use App\Models\Story;
use App\Models\User;
use App\StoryStatus;
use Illuminate\Database\QueryException;

it('creates an image that belongs to an admin and has stories', function () {
    $image = Image::factory()
        ->has(Story::factory()->count(2))
        ->create();

    expect($image->user)->toBeInstanceOf(User::class)
        ->and($image->user->is_admin)->toBeTrue()
        ->and($image->stories)->toHaveCount(2)
        ->and($image->stories->first()->image->is($image))->toBeTrue();
});

it('links a story to its image, author, comments, likes and bookmarks', function () {
    $story = Story::factory()
        ->has(Comment::factory()->count(3))
        ->has(Like::factory()->count(2))
        ->has(Bookmark::factory()->count(1))
        ->create();

    expect($story->image)->toBeInstanceOf(Image::class)
        ->and($story->user)->toBeInstanceOf(User::class)
        ->and($story->comments)->toHaveCount(3)
        ->and($story->likes)->toHaveCount(2)
        ->and($story->bookmarks)->toHaveCount(1)
        ->and($story->status)->toBeInstanceOf(StoryStatus::class);
});

it('casts the story status to an enum with a label', function () {
    $story = Story::factory()->complete()->create();

    expect($story->status)->toBe(StoryStatus::Complete)
        ->and($story->status->label())->toBe('完成')
        ->and(StoryStatus::Idea->label())->toBe('アイデア')
        ->and(StoryStatus::Draft->label())->toBe('下書き');
});

it('only returns completed stories through the complete scope', function () {
    Story::factory()->complete()->count(2)->create();
    Story::factory()->idea()->count(2)->create();
    Story::factory()->draft()->count(3)->create();

    expect(Story::complete()->count())->toBe(2);
});

it('scopes readable stories for guests to completed stories only', function () {
    Story::factory()->complete()->count(2)->create();
    Story::factory()->idea()->count(1)->create();
    Story::factory()->draft()->count(1)->create();

    expect(Story::readableBy(null)->count())->toBe(2);
});

it('scopes readable stories for logged-in users to completed, idea, and own drafts', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    Story::factory()->complete()->count(2)->create();
    Story::factory()->idea()->count(1)->create();
    Story::factory()->for($user)->draft()->create();
    Story::factory()->for($other)->draft()->create();

    expect(Story::readableBy($user)->count())->toBe(4);
});

it('prevents a user from liking the same story twice', function () {
    $story = Story::factory()->create();
    $user = User::factory()->create();

    Like::factory()->for($story)->for($user)->create();

    expect(fn () => Like::factory()->for($story)->for($user)->create())
        ->toThrow(QueryException::class);
});

it('exposes stories, likes and bookmarks from a user', function () {
    $user = User::factory()
        ->has(Story::factory()->count(2))
        ->has(Like::factory()->count(1))
        ->has(Bookmark::factory()->count(1))
        ->create();

    expect($user->stories)->toHaveCount(2)
        ->and($user->likes)->toHaveCount(1)
        ->and($user->bookmarks)->toHaveCount(1);
});
