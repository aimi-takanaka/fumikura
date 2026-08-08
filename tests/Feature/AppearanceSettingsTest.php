<?php

use App\Models\User;

it('shows the appearance theme picker to authenticated users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('appearance.edit'))
        ->assertOk()
        ->assertSee('ライト')
        ->assertSee('ダーク')
        ->assertDontSee('システム')
        ->assertDontSee('appearance-theme-system', false)
        ->assertSee('appearance-theme-picker', false)
        ->assertSee('fumikura-dark', false);
});

it('hides the theme toggle from guests and locks the home page to light mode', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('data-test="theme-toggle"', false)
        ->assertSee('canChange: false', false)
        ->assertSee('data-theme', false);
});

it('shows a theme toggle to authenticated users', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('topics.index'))
        ->assertOk()
        ->assertSee('data-test="theme-toggle"', false)
        ->assertSee('canChange: true', false);
});
