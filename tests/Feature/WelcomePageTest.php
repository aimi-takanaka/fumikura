<?php

it('shows the welcome page at the site root', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('文倉')
        ->assertSee('お題一覧を見る');
});

it('links to the image index from the welcome page', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee(route('images.index'), false);
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
