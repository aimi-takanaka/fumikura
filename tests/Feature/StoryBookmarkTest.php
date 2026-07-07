<?php

use App\Models\Story;
use App\Models\User;
use Livewire\Livewire;

it('lets a logged-in user bookmark a story', function () {
    $user = User::factory()->create();
    $story = Story::factory()->complete()->create();

    Livewire::actingAs($user)
        ->test('pages::stories.show', ['story' => $story])
        ->call('toggleBookmark');

    $this->assertDatabaseHas('bookmarks', [
        'story_id' => $story->id,
        'user_id' => $user->id,
    ]);
});

it('lets a user remove their bookmark by toggling again', function () {
    $user = User::factory()->create();
    $story = Story::factory()->complete()->create();

    Livewire::actingAs($user)
        ->test('pages::stories.show', ['story' => $story])
        ->call('toggleBookmark')
        ->call('toggleBookmark');

    $this->assertDatabaseMissing('bookmarks', [
        'story_id' => $story->id,
        'user_id' => $user->id,
    ]);
});

it('does not create duplicate bookmarks for the same user', function () {
    $user = User::factory()->create();
    $story = Story::factory()->complete()->create();

    Livewire::actingAs($user)
        ->test('pages::stories.show', ['story' => $story])
        ->call('toggleBookmark')
        ->call('toggleBookmark')
        ->call('toggleBookmark');

    expect($story->bookmarks()->where('user_id', $user->id)->count())->toBe(1);
});

it('forbids guests from bookmarking a story', function () {
    $story = Story::factory()->complete()->create();

    Livewire::test('pages::stories.show', ['story' => $story])
        ->call('toggleBookmark')
        ->assertStatus(403);

    expect($story->bookmarks()->count())->toBe(0);
});
