<?php

use App\Models\Image;
use App\Models\Story;
use App\Models\User;
use Livewire\Livewire;

it('shows the welcome page at the site root', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('言葉に詰まったら、絵を見よう。')
        ->assertSee('無料ではじめる')
        ->assertSee('お題一覧');
});

it('lets guests view the topic list with image titles', function () {
    $image = Image::factory()->create(['title' => '海辺の写真']);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('海辺の写真');
});

it('counts completed and draft stories for each image', function () {
    $image = Image::factory()->create();
    Story::factory()->for($image)->complete()->count(2)->create();
    Story::factory()->for($image)->draft()->count(3)->create();

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('完成2件')
        ->assertSee('大喜利3件');
});

it('shows an empty state when there are no images', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('まだお題がありません');
});

it('links to registration from the welcome page', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee(route('register'), false);
});

it('shows login and register links to guests', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('ログイン')
        ->assertSee('新規登録');
});

it('shows the image index link in the header', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('お題一覧');
});

it('shows the my page and how it works sections', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('マイページで、少しずつ育てていける')
        ->assertSee('はじめかた')
        ->assertSee('物語にしなくても参加できる');
});

it('shows the dashboard link to authenticated users', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('ダッシュボード');
});

it('paginates images when there are more than ten', function () {
    Image::factory()->count(11)->create();

    Livewire::test('pages::welcome')
        ->assertSee('Showing 1 to 10 of 11 results', false)
        ->call('nextPage')
        ->assertSee('Showing 11 to 11 of 11 results', false);
});

it('does not show pagination when there are ten or fewer images', function () {
    Image::factory()->count(10)->create();

    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('data-flux-pagination', false);
});

it('redirects the legacy image index route to the topic list', function () {
    $this->get(route('images.index'))
        ->assertRedirect('/#topics');
});

it('links each topic card to its image detail page', function () {
    $image = Image::factory()->create();

    $this->get(route('home'))
        ->assertOk()
        ->assertSee(route('images.show', $image));
});

it('sorts images by completed story count', function () {
    $popular = Image::factory()->create(['title' => '人気のお題']);
    $quiet = Image::factory()->create(['title' => '静かなお題']);
    Story::factory()->for($popular)->complete()->count(3)->create();
    Story::factory()->for($quiet)->complete()->count(1)->create();

    $html = Livewire::test('pages::welcome')
        ->set('sort', 'most_completed')
        ->html();

    expect(strpos($html, '人気のお題'))->toBeLessThan(strpos($html, '静かなお題'));
});
