<?php

use App\Models\Image;
use App\Models\Story;
use App\Models\User;

it('shows the image detail page to guests', function () {
    $image = Image::factory()->create(['title' => '霧の森']);

    $this->get(route('images.show', $image))
        ->assertOk()
        ->assertSee('霧の森');
});

it('shows completed stories but hides drafts from guests', function () {
    $image = Image::factory()->create();
    Story::factory()->for($image)->complete()->create(['title' => '完成した物語']);
    Story::factory()->for($image)->draft()->create(['title' => '下書きの物語']);

    $this->get(route('images.show', $image))
        ->assertOk()
        ->assertSee('完成した物語')
        ->assertDontSee('下書きの物語');
});

it('shows both completed and draft stories to logged-in users', function () {
    $image = Image::factory()->create();
    Story::factory()->for($image)->complete()->create(['title' => '完成した物語']);
    Story::factory()->for($image)->draft()->create(['title' => '下書きの物語']);

    $this->actingAs(User::factory()->create());

    $this->get(route('images.show', $image))
        ->assertOk()
        ->assertSee('完成した物語')
        ->assertSee('下書きの物語');
});

it('links each home page card to its image detail page', function () {
    $image = Image::factory()->create();

    $this->get(route('images.index'))
        ->assertOk()
        ->assertSee(route('images.show', $image));
});

it('returns 404 for a missing image', function () {
    $this->get('/images/999999')
        ->assertNotFound();
});
