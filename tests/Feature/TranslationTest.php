<?php

it('translates common header labels to japanese', function () {
    app()->setLocale('ja');

    expect(__('Settings'))->toBe('設定')
        ->and(__('Log out'))->toBe('ログアウト');
});

it('uses japanese as the default application locale', function () {
    expect(config('app.locale'))->toBe('ja');
});
