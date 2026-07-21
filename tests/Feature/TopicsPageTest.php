<?php

use App\Models\Image;
use App\Models\Story;
use App\Models\User;
use Livewire\Livewire;

it('shows the topic list page', function () {
    $this->get(route('topics.index'))
        ->assertOk()
        ->assertSee('お題一覧');
});

it('lets guests view the topic list with image titles', function () {
    $image = Image::factory()->create(['title' => '海辺の写真']);

    $this->get(route('topics.index'))
        ->assertOk()
        ->assertSee('海辺の写真');
});

it('counts completed and idea stories for each image', function () {
    $image = Image::factory()->create();
    Story::factory()->for($image)->complete()->count(2)->create();
    Story::factory()->for($image)->idea()->count(3)->create();

    $this->get(route('topics.index'))
        ->assertOk()
        ->assertSee('完成2件')
        ->assertSee('アイデア3件');
});

it('shows an empty state when there are no images', function () {
    $this->get(route('topics.index'))
        ->assertOk()
        ->assertSee('まだお題がありません');
});

it('hides removed header links from authenticated users', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('topics.index'))
        ->assertOk()
        ->assertDontSee(route('topics.index'), false)
        ->assertDontSee(route('bookmarks'), false)
        ->assertSee('マイページ');
});

it('hides the image index link in the header from guests', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee(route('topics.index'), false);
});

it('paginates images when there are more than ten', function () {
    Image::factory()->count(11)->create();

    Livewire::test('pages::topics')
        ->assertSee('Showing 1 to 10 of 11 results', false)
        ->call('nextPage')
        ->assertSee('Showing 11 to 11 of 11 results', false);
});

it('does not show pagination when there are ten or fewer images', function () {
    Image::factory()->count(10)->create();

    $this->get(route('topics.index'))
        ->assertOk()
        ->assertDontSee('data-flux-pagination', false);
});

it('redirects the legacy image index route to the topic list', function () {
    $this->get(route('images.index'))
        ->assertRedirect('/topics');
});

it('links each topic card to its image detail page', function () {
    $image = Image::factory()->create();

    $this->get(route('topics.index'))
        ->assertOk()
        ->assertSee(route('images.show', $image));
});

it('sorts images by completed story count', function () {
    $popular = Image::factory()->create(['title' => '人気のお題']);
    $quiet = Image::factory()->create(['title' => '静かなお題']);
    Story::factory()->for($popular)->complete()->count(3)->create();
    Story::factory()->for($quiet)->complete()->count(1)->create();

    $html = Livewire::test('pages::topics')
        ->set('sort', 'most_completed')
        ->html();

    expect(strpos($html, '人気のお題'))->toBeLessThan(strpos($html, '静かなお題'));
});
