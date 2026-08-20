<?php

use App\Models\Image;
use App\Models\Story;
use App\Models\User;

it('shows the welcome page at the site root', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('言葉に詰まったら、絵を見よう。')
        ->assertSee('無料ではじめる')
        ->assertDontSee('お題一覧');
});

it('lets guests view the topic list with image titles on the welcome page', function () {
    $image = Image::factory()->create(['title' => '海辺の写真']);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('海辺の写真');
});

it('counts completed and idea stories for each image on the welcome page', function () {
    $image = Image::factory()->create();
    Story::factory()->for($image)->complete()->count(2)->create();
    Story::factory()->for($image)->idea()->count(3)->create();

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('完成2件')
        ->assertSee('アイデア3件');
});

it('shows like counts for each topic on the welcome page', function () {
    $image = Image::factory()->create(['title' => 'いいね付きのお題']);
    $story = Story::factory()->for($image)->complete()->create();
    $story->likes()->create(['user_id' => User::factory()->create()->id]);
    $story->likes()->create(['user_id' => User::factory()->create()->id]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('いいね付きのお題')
        ->assertSee('♡', false)
        ->assertSee('いいね 2件', false);
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

it('shows the my page and how it works sections', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('マイページで、少しずつ育てていける')
        ->assertSee('はじめかた')
        ->assertSee('物語にしなくても参加できる');
});

it('shows experience illustrations on the welcome page', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('こんな経験、ありませんか？')
        ->assertSee('img/welcome/experience-1.jpg', false)
        ->assertSee('img/welcome/experience-2.jpg', false)
        ->assertSee('img/welcome/experience-3.jpg', false);
});

it('shows feature illustrations on the welcome page', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('img/welcome/features-1.PNG', false)
        ->assertSee('img/welcome/features-2.PNG', false)
        ->assertSee('img/welcome/features-3.PNG', false)
        ->assertSee('img/welcome/features-4.PNG', false);
});

it('shows my page illustrations on the welcome page', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('img/welcome/MyPage-1.PNG', false)
        ->assertSee('img/welcome/MyPage-2.PNG', false)
        ->assertSee('img/welcome/MyPage-3.PNG', false);
});

it('shows how it works illustrations on the welcome page', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('img/welcome/How-1.PNG', false)
        ->assertSee('img/welcome/How-2.PNG', false)
        ->assertSee('img/welcome/How-3.PNG', false);
});

it('paginates images when there are more than six on the welcome page', function () {
    Image::factory()->count(7)->create();

    Livewire::test('pages::welcome')
        ->assertSee('7件中 1〜6件を表示', false)
        ->call('nextPage')
        ->assertSee('7件中 7〜7件を表示', false);
});

it('does not show pagination when there are six or fewer images on the welcome page', function () {
    Image::factory()->count(6)->create();

    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('data-pagination', false);
});

it('redirects unverified authenticated users to the verification notice', function () {
    $this->actingAs(User::factory()->unverified()->create());

    $this->get(route('home'))
        ->assertRedirect(route('verification.notice'));
});
