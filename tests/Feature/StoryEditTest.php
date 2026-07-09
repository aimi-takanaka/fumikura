<?php

use App\Models\Story;
use App\Models\User;
use App\StoryStatus;
use Livewire\Livewire;

it('redirects guests to login', function () {
    $story = Story::factory()->create();

    $this->get(route('stories.edit', $story))
        ->assertRedirect(route('login'));
});

it('forbids editing a story you do not own', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $story = Story::factory()->for($owner)->create();

    $this->actingAs($intruder)
        ->get(route('stories.edit', $story))
        ->assertForbidden();
});

it('pre-fills the form with the current story values', function () {
    $user = User::factory()->create();
    $story = Story::factory()->for($user)->create([
        'title' => '元のタイトル',
        'body' => '元の本文',
        'status' => StoryStatus::Draft,
    ]);

    Livewire::actingAs($user)
        ->test('pages::stories.edit', ['story' => $story])
        ->assertSet('title', '元のタイトル')
        ->assertSet('body', '元の本文')
        ->assertSet('status', 'draft');
});

it('lets the author update the story including its status', function () {
    $user = User::factory()->create();
    $story = Story::factory()->for($user)->draft()->create();

    Livewire::actingAs($user)
        ->test('pages::stories.edit', ['story' => $story])
        ->set('title', '新しいタイトル')
        ->set('body', '書き直した本文')
        ->set('status', 'complete')
        ->call('update')
        ->assertHasNoErrors()
        ->assertRedirect(route('stories.show', $story));

    $story->refresh();

    expect($story->title)->toBe('新しいタイトル')
        ->and($story->body)->toBe('書き直した本文')
        ->and($story->status)->toBe(StoryStatus::Complete);
});

it('validates the input on update', function () {
    $user = User::factory()->create();
    $story = Story::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test('pages::stories.edit', ['story' => $story])
        ->set('title', '')
        ->set('body', '')
        ->call('update')
        ->assertHasErrors(['title', 'body']);
});

it('shows an edit button to the author on the detail page', function () {
    $user = User::factory()->create();
    $story = Story::factory()->for($user)->complete()->create();

    $this->actingAs($user)
        ->get(route('stories.show', $story))
        ->assertSee(route('stories.edit', $story));
});

it('does not show an edit button to non-authors', function () {
    $author = User::factory()->create();
    $viewer = User::factory()->create();
    $story = Story::factory()->for($author)->complete()->create();

    $this->actingAs($viewer)
        ->get(route('stories.show', $story))
        ->assertDontSee(route('stories.edit', $story));
});
