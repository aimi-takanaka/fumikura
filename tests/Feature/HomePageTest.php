<?php

use App\Models\Image;
use App\Models\Story;
use App\Models\User;

it('lets guests view the home page with image titles', function () {
    $image = Image::factory()->create(['title' => '海辺の写真']);

    $this->get('/')
        ->assertOk()
        ->assertSee('お題一覧')
        ->assertSee('海辺の写真');
});

it('counts only completed stories for each image', function () {
    $image = Image::factory()->create();
    Story::factory()->for($image)->complete()->count(2)->create();
    Story::factory()->for($image)->draft()->count(3)->create();

    $this->get('/')
        ->assertOk()
        ->assertSee('完成 2 編');
});

it('shows an empty state when there are no images', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('まだお題がありません');
});

it('shows login and register links to guests', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('ログイン')
        ->assertSee('新規登録');
});

it('shows the dashboard link to authenticated users', function () {
    $this->actingAs(User::factory()->create());

    $this->get('/')
        ->assertOk()
        ->assertSee('ダッシュボード');
});
