<?php

it('shows the terms of service page to guests', function () {
    $this->get(route('terms'))
        ->assertOk()
        ->assertSee('利用規約')
        ->assertSee('第1条（適用）')
        ->assertSee('第14条（準拠法・裁判管轄）')
        ->assertSee('Google Gemini API', false);
});

it('links to the terms page from the welcome page footer', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee(route('terms'), false);
});
