<?php

use App\Models\Story;
use App\Models\User;
use Livewire\Livewire;

it('lets the author delete their own story', function () {
    $user = User::factory()->create();
    $story = Story::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test('pages::stories.show', ['story' => $story])
        ->call('deleteStory')
        ->assertRedirect(route('images.show', $story->image));

    expect(Story::find($story->id))->toBeNull();
});

it('forbids deleting a story you do not own', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $story = Story::factory()->for($owner)->create();

    Livewire::actingAs($intruder)
        ->test('pages::stories.show', ['story' => $story])
        ->call('deleteStory')
        ->assertForbidden();

    expect(Story::find($story->id))->not->toBeNull();
});

it('shows a delete button to the author on the detail page', function () {
    $user = User::factory()->create();
    $story = Story::factory()->for($user)->complete()->create();

    $this->actingAs($user)
        ->get(route('stories.show', $story))
        ->assertSee('deleteStory');
});

it('does not show a delete button to non-authors', function () {
    $author = User::factory()->create();
    $viewer = User::factory()->create();
    $story = Story::factory()->for($author)->complete()->create();

    $this->actingAs($viewer)
        ->get(route('stories.show', $story))
        ->assertDontSee('deleteStory');
});
