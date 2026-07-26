<?php

it('shows the privacy policy page to guests', function () {
    $this->get(route('privacy'))
        ->assertOk()
        ->assertSee('プライバシーポリシー')
        ->assertSee('第1条（取得する情報）')
        ->assertSee('第11条（お問い合わせ窓口）')
        ->assertSee('Google Gemini API', false);
});

it('links to the privacy policy page from the welcome page footer', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee(route('privacy'), false);
});

it('links to the terms page from the privacy policy page', function () {
    $this->get(route('privacy'))
        ->assertOk()
        ->assertSee(route('terms'), false);
});
