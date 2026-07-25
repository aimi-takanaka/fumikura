<?php

use App\Models\Comment;
use App\Models\Image;
use App\Models\Story;
use App\Models\User;
use Livewire\Livewire;

it('opens the comments modal for a readable story', function () {
    $story = Story::factory()->complete()->create(['title' => 'モーダルテスト']);

    Livewire::test('story-comments-modal')
        ->dispatch('open-story-comments', storyId: $story->id)
        ->assertSet('showModal', true)
        ->assertSee('モーダルテスト')
        ->assertSee('コメント（0）');
});

it('shows a login callout to guests in the comments modal', function () {
    $story = Story::factory()->complete()->create();
    Comment::factory()->for($story)->create(['body' => '秘密のコメント本文']);

    Livewire::test('story-comments-modal')
        ->dispatch('open-story-comments', storyId: $story->id)
        ->assertSet('showModal', true)
        ->assertSee('コメントはログインすると読めます')
        ->assertDontSee('秘密のコメント本文');
});

it('shows comments to logged-in users in the modal', function () {
    $user = User::factory()->create();
    $story = Story::factory()->complete()->create();
    Comment::factory()->for($story)->create(['body' => '公開コメント本文']);

    Livewire::actingAs($user)
        ->test('story-comments-modal')
        ->dispatch('open-story-comments', storyId: $story->id)
        ->assertSee('公開コメント本文');
});

it('lets a logged-in user post a comment from the modal', function () {
    $user = User::factory()->create();
    $story = Story::factory()->complete()->create();

    Livewire::actingAs($user)
        ->test('story-comments-modal')
        ->dispatch('open-story-comments', storyId: $story->id)
        ->set('body', 'モーダルから投稿')
        ->call('addComment')
        ->assertHasNoErrors()
        ->assertSee('モーダルから投稿');

    $this->assertDatabaseHas('comments', [
        'story_id' => $story->id,
        'user_id' => $user->id,
        'body' => 'モーダルから投稿',
    ]);
});

it('shows a comment button on the image detail page', function () {
    $image = Image::factory()->create();
    Story::factory()->for($image)->complete()->create();

    $this->get(route('images.show', $image))
        ->assertOk()
        ->assertSee('open-story-comments', false);
});

it('refreshes comment counts on the image detail page after posting', function () {
    $user = User::factory()->create();
    $image = Image::factory()->create();
    $story = Story::factory()->for($image)->complete()->create();

    Livewire::actingAs($user)
        ->test('pages::images.show', ['image' => $image])
        ->assertSee('0', false);

    Livewire::actingAs($user)
        ->test('story-comments-modal')
        ->dispatch('open-story-comments', storyId: $story->id)
        ->set('body', '新しいコメント')
        ->call('addComment');

    Livewire::actingAs($user)
        ->test('pages::images.show', ['image' => $image])
        ->assertSee('1', false);
});
