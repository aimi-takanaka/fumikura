<?php

use App\Models\Image;
use App\Models\Story;
use App\Models\User;

it('shows a completed story to guests', function () {
    $story = Story::factory()->complete()->create(['title' => '夕暮れの記憶']);

    $this->get(route('stories.show', $story))
        ->assertOk()
        ->assertSee('夕暮れの記憶')
        ->assertSee($story->body);
});

it('forbids guests from viewing a draft story', function () {
    $story = Story::factory()->draft()->create();

    $this->get(route('stories.show', $story))
        ->assertForbidden();
});

it('allows logged-in users to view a draft story', function () {
    $story = Story::factory()->draft()->create(['title' => '書きかけの散文']);

    $this->actingAs(User::factory()->create())
        ->get(route('stories.show', $story))
        ->assertOk()
        ->assertSee('書きかけの散文');
});

it('links story cards on the image detail page to the story detail', function () {
    $image = Image::factory()->create();
    $story = Story::factory()->complete()->for($image)->create();

    $this->get(route('images.show', $image))
        ->assertOk()
        ->assertSee(route('stories.show', $story));
});

it('returns 404 for a missing story', function () {
    $this->get('/stories/999999')
        ->assertNotFound();
});
