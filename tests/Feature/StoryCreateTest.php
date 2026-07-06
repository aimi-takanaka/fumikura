<?php

use App\Models\Image;
use App\Models\Story;
use App\Models\User;
use App\StoryStatus;
use Livewire\Livewire;

it('redirects guests to login', function () {
    $image = Image::factory()->create();

    $this->get(route('stories.create', $image))
        ->assertRedirect(route('login'));
});

it('lets logged-in users view the create form', function () {
    $image = Image::factory()->create();
    $this->actingAs(User::factory()->create());

    $this->get(route('stories.create', $image))
        ->assertOk()
        ->assertSee('ストーリーを書く');
});

it('creates a story tied to the image and the author', function () {
    $image = Image::factory()->create();
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::stories.create', ['image' => $image])
        ->set('title', '灯台守の最後の夜')
        ->set('body', 'その夜、灯台の光は消えた。')
        ->set('status', 'complete')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('images.show', $image));

    $story = Story::first();

    expect($story)->not->toBeNull()
        ->and($story->title)->toBe('灯台守の最後の夜')
        ->and($story->image_id)->toBe($image->id)
        ->and($story->user_id)->toBe($user->id)
        ->and($story->status)->toBe(StoryStatus::Complete);
});

it('requires a title, body and valid status', function () {
    $image = Image::factory()->create();
    $this->actingAs(User::factory()->create());

    Livewire::test('pages::stories.create', ['image' => $image])
        ->set('title', '')
        ->set('body', '')
        ->set('status', 'invalid-status')
        ->call('save')
        ->assertHasErrors(['title', 'body', 'status']);
});

it('shows the newly created story on the image detail page', function () {
    $image = Image::factory()->create();
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::stories.create', ['image' => $image])
        ->set('title', '見える物語')
        ->set('body', '本文です。')
        ->set('status', 'complete')
        ->call('save');

    $this->get(route('images.show', $image))
        ->assertOk()
        ->assertSee('見える物語');
});
