<?php

it('shows the welcome page at the site root', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('言葉に詰まったら、絵を見よう。')
        ->assertSee('無料ではじめる');
});

it('links to the image index from the welcome page header', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee(route('images.index'), false);
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

it('does not show the image grid on the welcome page', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('まだお題がありません');
});

it('shows the how it works section', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('はじめかた')
        ->assertSee('お題の絵を見る')
        ->assertSee('物語を書く');
});
