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

it('forbids guests from viewing an idea story', function () {
    $story = Story::factory()->idea()->create();

    $this->get(route('stories.show', $story))
        ->assertForbidden();
});

it('allows logged-in users to view an idea story', function () {
    $story = Story::factory()->idea()->create(['title' => 'ひらめきの一行']);

    $this->actingAs(User::factory()->create())
        ->get(route('stories.show', $story))
        ->assertOk()
        ->assertSee('ひらめきの一行');
});

it('forbids guests from viewing a draft story', function () {
    $story = Story::factory()->draft()->create();

    $this->get(route('stories.show', $story))
        ->assertForbidden();
});

it('allows the author to view their draft story', function () {
    $user = User::factory()->create();
    $story = Story::factory()->for($user)->draft()->create(['title' => '書きかけの下書き']);

    $this->actingAs($user)
        ->get(route('stories.show', $story))
        ->assertOk()
        ->assertSee('書きかけの下書き');
});

it('forbids other logged-in users from viewing a draft story', function () {
    $story = Story::factory()->draft()->create(['title' => '他人の下書き']);

    $this->actingAs(User::factory()->create())
        ->get(route('stories.show', $story))
        ->assertForbidden();
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

it('shows status labels on the story detail page', function () {
    $story = Story::factory()->idea()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('stories.show', $story))
        ->assertOk()
        ->assertSee('アイデア');
});
