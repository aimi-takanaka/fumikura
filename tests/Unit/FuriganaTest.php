<?php

use App\Support\Furigana;

it('converts kanji furigana markers to ruby html', function () {
    $html = Furigana::toHtml('灯台《とうだい》の光');

    expect($html)
        ->toContain('<ruby>灯台<rp>（</rp><rt>とうだい</rt><rp>）</rp></ruby>')
        ->toContain('の光')
        ->not->toContain('《');
});

it('supports an explicit pipe for non-kanji bases', function () {
    expect(Furigana::toHtml('彼は｜カタカナ《かたかな》を書いた'))
        ->toContain('<ruby>カタカナ<rp>（</rp><rt>かたかな</rt><rp>）</rp></ruby>');
});

it('escapes html in plain text and readings', function () {
    expect(Furigana::toHtml('<script>alert(1)</script>'))
        ->toBe('&lt;script&gt;alert(1)&lt;/script&gt;');

    expect(Furigana::toHtml('漢字《<img>》'))
        ->toContain('<rt>&lt;img&gt;</rt>')
        ->not->toContain('<rt><img></rt>');
});

it('strips furigana markers to plain text', function () {
    expect(Furigana::toPlain('灯台《とうだい》の｜光《ひかり》'))
        ->toBe('灯台の光');
});
