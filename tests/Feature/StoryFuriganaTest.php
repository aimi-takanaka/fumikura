<?php

use App\Models\Story;
use App\Models\User;

it('renders story furigana as ruby markup on the detail page', function () {
    $story = Story::factory()->complete()->create([
        'body' => "灯台《とうだい》守の夜\n｜カタカナ《かたかな》も使える",
    ]);

    $this->get(route('stories.show', $story))
        ->assertOk()
        ->assertSee('<ruby>灯台<rp>（</rp><rt>とうだい</rt><rp>）</rp></ruby>', false)
        ->assertSee('<ruby>カタカナ<rp>（</rp><rt>かたかな</rt><rp>）</rp></ruby>', false)
        ->assertDontSee('《とうだい》');
});

it('shows furigana writing help on the create form', function () {
    $story = Story::factory()->complete()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('stories.create', $story->image))
        ->assertOk()
        ->assertSee('フリガナの例: 漢字《かんじ》', false);
});
