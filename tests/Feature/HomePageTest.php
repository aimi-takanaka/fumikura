<?php

use App\Models\Image;
use App\Models\Story;
use App\Models\User;
use Livewire\Livewire;

it('lets guests view the home page with image titles', function () {
    $image = Image::factory()->create(['title' => '海辺の写真']);

    $this->get(route('images.index'))
        ->assertOk()
        ->assertSee('お題一覧')
        ->assertSee('海辺の写真');
});

it('counts only completed stories for each image', function () {
    $image = Image::factory()->create();
    Story::factory()->for($image)->complete()->count(2)->create();
    Story::factory()->for($image)->draft()->count(3)->create();

    $this->get(route('images.index'))
        ->assertOk()
        ->assertSee('完成 2 編');
});

it('shows an empty state when there are no images', function () {
    $this->get(route('images.index'))
        ->assertOk()
        ->assertSee('まだお題がありません');
});

it('shows login and register links to guests', function () {
    $this->get(route('images.index'))
        ->assertOk()
        ->assertSee('ログイン')
        ->assertSee('新規登録');
});

it('shows the dashboard link to authenticated users', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('images.index'))
        ->assertOk()
        ->assertSee('ダッシュボード');
});

it('shows a logout control to authenticated users', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('images.index'))
        ->assertOk()
        ->assertSee('data-test="logout-button"', false)
        ->assertSee('Log out');
});

it('paginates images when there are more than ten', function () {
    Image::factory()->count(11)->create();

    Livewire::test('pages::home')
        ->assertSee('Showing 1 to 10 of 11 results', false)
        ->call('nextPage')
        ->assertSee('Showing 11 to 11 of 11 results', false);
});

it('does not show pagination when there are ten or fewer images', function () {
    Image::factory()->count(10)->create();

    $this->get(route('images.index'))
        ->assertOk()
        ->assertDontSee('data-flux-pagination', false);
});
