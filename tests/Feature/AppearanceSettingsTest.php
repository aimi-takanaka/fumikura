<?php

use App\Models\User;

it('shows the appearance theme picker to authenticated users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('appearance.edit'))
        ->assertOk()
        ->assertSee('ライト')
        ->assertSee('ダーク')
        ->assertSee('システム')
        ->assertSee('appearance-theme-picker', false)
        ->assertSee('fumikura-dark', false);
});

it('shows a theme toggle in the public layout', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('theme-toggle', false);
});
