<?php

use App\Models\Image;
use App\Models\Story;
use App\Models\User;

it('shows the welcome page at the site root', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('言葉に詰まったら、絵を見よう。')
        ->assertSee('無料ではじめる')
        ->assertSee('お題一覧');
});

it('lets guests view the topic list with image titles on the welcome page', function () {
    $image = Image::factory()->create(['title' => '海辺の写真']);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('海辺の写真');
});

it('counts completed and draft stories for each image on the welcome page', function () {
    $image = Image::factory()->create();
    Story::factory()->for($image)->complete()->count(2)->create();
    Story::factory()->for($image)->draft()->count(3)->create();

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('完成2件')
        ->assertSee('大喜利3件');
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
