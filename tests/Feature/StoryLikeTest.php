<?php

use App\Models\Story;
use App\Models\User;
use Livewire\Livewire;

it('lets a logged-in user like a story', function () {
    $user = User::factory()->create();
    $story = Story::factory()->complete()->create();

    Livewire::actingAs($user)
        ->test('pages::stories.show', ['story' => $story])
        ->call('toggleLike');

    $this->assertDatabaseHas('likes', [
        'story_id' => $story->id,
        'user_id' => $user->id,
    ]);
});

it('lets a user remove their like by toggling again', function () {
    $user = User::factory()->create();
    $story = Story::factory()->complete()->create();

    Livewire::actingAs($user)
        ->test('pages::stories.show', ['story' => $story])
        ->call('toggleLike')
        ->call('toggleLike');

    $this->assertDatabaseMissing('likes', [
        'story_id' => $story->id,
        'user_id' => $user->id,
    ]);
});

it('does not create duplicate likes for the same user', function () {
    $user = User::factory()->create();
    $story = Story::factory()->complete()->create();

    Livewire::actingAs($user)
        ->test('pages::stories.show', ['story' => $story])
        ->call('toggleLike')
        ->call('toggleLike')
        ->call('toggleLike');

    expect($story->likes()->where('user_id', $user->id)->count())->toBe(1);
});

it('reflects the like count and state in the view', function () {
    $user = User::factory()->create();
    $story = Story::factory()->complete()->create();

    Livewire::actingAs($user)
        ->test('pages::stories.show', ['story' => $story])
        ->assertSet('story.id', $story->id)
        ->call('toggleLike')
        ->assertSee('1');
});

it('forbids guests from liking a story', function () {
    $story = Story::factory()->complete()->create();

    Livewire::test('pages::stories.show', ['story' => $story])
        ->call('toggleLike')
        ->assertStatus(403);

    expect($story->likes()->count())->toBe(0);
});
