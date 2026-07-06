<?php

use App\Models\Comment;
use App\Models\Story;
use App\Models\User;
use Livewire\Livewire;

it('hides comments from guests', function () {
    $story = Story::factory()->complete()->create();
    Comment::factory()->for($story)->create(['body' => '秘密のコメント本文']);

    $this->get(route('stories.show', $story))
        ->assertOk()
        ->assertDontSee('秘密のコメント本文')
        ->assertSee('コメントはログインすると読めます');
});

it('lets a logged-in user post a comment', function () {
    $user = User::factory()->create();
    $story = Story::factory()->complete()->create();

    Livewire::actingAs($user)
        ->test('pages::stories.show', ['story' => $story])
        ->set('body', 'とても良いストーリーでした')
        ->call('addComment')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('comments', [
        'story_id' => $story->id,
        'user_id' => $user->id,
        'body' => 'とても良いストーリーでした',
    ]);
});

it('validates that the comment body is required', function () {
    $user = User::factory()->create();
    $story = Story::factory()->complete()->create();

    Livewire::actingAs($user)
        ->test('pages::stories.show', ['story' => $story])
        ->set('body', '')
        ->call('addComment')
        ->assertHasErrors(['body' => 'required']);

    expect(Comment::count())->toBe(0);
});

it('allows a user to delete their own comment', function () {
    $user = User::factory()->create();
    $story = Story::factory()->complete()->create();
    $comment = Comment::factory()->for($story)->for($user)->create();

    Livewire::actingAs($user)
        ->test('pages::stories.show', ['story' => $story])
        ->call('deleteComment', $comment->id);

    expect(Comment::find($comment->id))->toBeNull();
});

it('forbids deleting another user\'s comment', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $story = Story::factory()->complete()->create();
    $comment = Comment::factory()->for($story)->for($owner)->create();

    Livewire::actingAs($intruder)
        ->test('pages::stories.show', ['story' => $story])
        ->call('deleteComment', $comment->id)
        ->assertStatus(403);

    expect(Comment::find($comment->id))->not->toBeNull();
});
