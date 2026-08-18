<?php

use App\Models\Image;
use App\Models\Story;
use App\Models\User;

it('shows the image detail page to guests', function () {
    $image = Image::factory()->create(['title' => '霧の森']);

    $this->get(route('images.show', $image))
        ->assertOk()
        ->assertSee('霧の森');
});

it('shows credit under the title when attribution is set', function () {
    $image = Image::factory()->photoCredit('Sato Hanako', 'Unsplash', 'https://unsplash.com/@sato')->create([
        'title' => '港の朝',
    ]);

    $this->get(route('images.show', $image))
        ->assertOk()
        ->assertSee('港の朝')
        ->assertSee('写真：Sato Hanako')
        ->assertSee('Unsplash')
        ->assertSee('https://unsplash.com/@sato', false)
        ->assertSee('投稿者:');
});

it('links the credit service name when a URL is set', function () {
    $image = Image::factory()->illustrationCredit('Yamada Taro', 'Example Art', 'https://example.com/art')->create();

    $this->get(route('images.show', $image))
        ->assertOk()
        ->assertSee('イラスト：Yamada Taro')
        ->assertSee('Example Art')
        ->assertSee('href="https://example.com/art"', false)
        ->assertSee('target="_blank"', false)
        ->assertSee('外部サイトへ');
});

it('shows plain service text when no URL is set', function () {
    $image = Image::factory()->photoCredit('Sato Hanako', 'Unsplash', null)->create();

    $this->get(route('images.show', $image))
        ->assertOk()
        ->assertSee('写真：Sato Hanako')
        ->assertSee('Unsplash')
        ->assertDontSee('>Unsplash</a>', false);
});

it('hides credit when attribution is not selected', function () {
    $image = Image::factory()->create(['title' => 'クレジットなし']);

    $this->get(route('images.show', $image))
        ->assertOk()
        ->assertSee('クレジットなし')
        ->assertDontSee('イラスト：')
        ->assertDontSee('写真：');
});

it('shows completed stories but hides idea and draft stories from guests', function () {
    $image = Image::factory()->create();
    Story::factory()->for($image)->complete()->create(['title' => '完成した物語']);
    Story::factory()->for($image)->idea()->create(['title' => 'アイデアの物語']);
    Story::factory()->for($image)->draft()->create(['title' => '下書きの物語']);

    $this->get(route('images.show', $image))
        ->assertOk()
        ->assertSee('完成した物語')
        ->assertSee('続きを読む')
        ->assertDontSee('アイデアの物語')
        ->assertDontSee('下書きの物語');
});

it('does not show read more when the full story body is displayed', function () {
    $image = Image::factory()->create();
    Story::factory()->for($image)->complete()->create([
        'title' => '短い物語',
        'body' => 'カメラ目線',
    ]);

    $this->get(route('images.show', $image))
        ->assertOk()
        ->assertSee('カメラ目線', false)
        ->assertDontSee('続きを読む', false);
});

it('shows read more inline after an ellipsis for long story bodies', function () {
    $image = Image::factory()->create();
    Story::factory()->for($image)->complete()->create([
        'title' => '長い物語',
        'body' => str_repeat('あ', 120),
    ]);

    $this->get(route('images.show', $image))
        ->assertOk()
        ->assertSee('...', false)
        ->assertSee('　続きを読む', false);
});

it('shows read more inline after truncated multiline story bodies', function () {
    $image = Image::factory()->create();
    Story::factory()->for($image)->complete()->create([
        'title' => '改行テスト',
        'body' => "1行目\n2行目\n3行目\n4行目",
    ]);

    $this->get(route('images.show', $image))
        ->assertOk()
        ->assertSee('3行目', false)
        ->assertSee('...', false)
        ->assertSee('　続きを読む', false)
        ->assertDontSee('4行目', false);
});

it('applies the character limit after truncating multiline story bodies', function () {
    $image = Image::factory()->create();
    $thirdLine = str_repeat('う', 40);

    Story::factory()->for($image)->complete()->create([
        'title' => '長い改行テスト',
        'body' => str_repeat('あ', 40)."\n".str_repeat('い', 40)."\n".$thirdLine."\n".'4行目',
    ]);

    $this->get(route('images.show', $image))
        ->assertOk()
        ->assertSee('...', false)
        ->assertSee('　続きを読む', false)
        ->assertDontSee($thirdLine, false)
        ->assertDontSee('4行目', false);
});

it('shows completed and idea stories to logged-in users', function () {
    $image = Image::factory()->create();
    Story::factory()->for($image)->complete()->create(['title' => '完成した物語']);
    Story::factory()->for($image)->idea()->create(['title' => 'アイデアの物語']);
    Story::factory()->for($image)->draft()->create(['title' => '下書きの物語']);

    $this->actingAs(User::factory()->create())
        ->get(route('images.show', $image))
        ->assertOk()
        ->assertSee('完成した物語')
        ->assertSee('アイデアの物語')
        ->assertDontSee('下書きの物語');
});

it('shows the authors own draft stories on the image detail page', function () {
    $user = User::factory()->create();
    $image = Image::factory()->create();
    Story::factory()->for($image)->for($user)->draft()->create(['title' => '自分の下書き']);

    $this->actingAs($user)
        ->get(route('images.show', $image))
        ->assertOk()
        ->assertSee('自分の下書き');
});

it('links each topic list card to its image detail page', function () {
    $image = Image::factory()->create();

    $this->get(route('topics.index'))
        ->assertOk()
        ->assertSee(route('images.show', $image));
});

it('returns 404 for a missing image', function () {
    $this->get('/images/999999')
        ->assertNotFound();
});
