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

it('shows feature illustrations on the welcome page', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('img/welcome/features-1.png', false)
        ->assertSee('img/welcome/features-2.png', false)
        ->assertSee('img/welcome/features-3.png', false)
        ->assertSee('img/welcome/features-4.png', false);
});

it('redirects authenticated users to the topic list', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('home'))
        ->assertRedirect(route('topics.index'));
});

it('redirects unverified authenticated users to the verification notice', function () {
    $this->actingAs(User::factory()->unverified()->create());

    $this->get(route('home'))
        ->assertRedirect(route('verification.notice'));
});
