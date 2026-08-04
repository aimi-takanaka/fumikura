<?php

use App\Models\Image;
use App\Models\Story;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;

beforeEach(function () {
    config([
        'services.gemini.key' => 'test-key',
        'services.gemini.model' => 'gemini-2.0-flash',
    ]);
});

/**
 * @param  non-empty-string  $text
 */
function fakeFumiEncouragementResponse(string $text = 'その情景、わたしも心に残ったよ！完成おめでとう！'): void
{
    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            ['text' => $text],
                        ],
                    ],
                ],
            ],
        ]),
    ]);
}

it('shows fumi encouragement after creating a completed story', function () {
    fakeFumiEncouragementResponse('灯台の光が印象的だね！完成おめでとう！');

    $image = Image::factory()->create(['title' => '海辺の灯台']);
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::stories.create', ['image' => $image])
        ->set('title', '灯台守の最後の夜')
        ->set('body', 'その夜、灯台の光は消えた。')
        ->set('status', 'complete')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('images.show', $image))
        ->assertSessionHas('fumi_message', '灯台の光が印象的だね！完成おめでとう！')
        ->assertSessionHas('fumi_status', 'complete');

    $this->get(route('images.show', $image))
        ->assertOk()
        ->assertSee('ふみより')
        ->assertSee('灯台の光が印象的だね！完成おめでとう！')
        ->assertSee(asset('img/welcome/comment-1.PNG'), false);
});

it('shows fumi encouragement after updating a draft story', function () {
    fakeFumiEncouragementResponse('この先どうなるのか、わたしも気になるな！');

    $user = User::factory()->create();
    $story = Story::factory()->for($user)->draft()->create([
        'title' => '途中の物語',
        'body' => '扉の向こうで足音が止まった。',
    ]);

    Livewire::actingAs($user)
        ->test('pages::stories.edit', ['story' => $story])
        ->call('update')
        ->assertHasNoErrors()
        ->assertRedirect(route('stories.show', $story))
        ->assertSessionHas('fumi_message', 'この先どうなるのか、わたしも気になるな！')
        ->assertSessionHas('fumi_status', 'draft');

    $this->get(route('stories.show', $story))
        ->assertOk()
        ->assertSee('ふみより')
        ->assertSee(asset('img/welcome/comment-3.PNG'), false);
});

it('still saves the story when fumi encouragement fails', function () {
    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([], 500),
    ]);

    $image = Image::factory()->create();
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::stories.create', ['image' => $image])
        ->set('title', '失敗しても保存')
        ->set('body', '本文です。')
        ->set('status', 'idea')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('images.show', $image))
        ->assertSessionMissing('fumi_message');

    expect(Story::count())->toBe(1);
});

it('skips fumi encouragement when the gemini api key is missing', function () {
    config(['services.gemini.key' => null]);

    $image = Image::factory()->create();
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::stories.create', ['image' => $image])
        ->set('title', 'キーなし')
        ->set('body', '本文です。')
        ->set('status', 'complete')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSessionMissing('fumi_message');

    Http::assertNothingSent();
});

it('rate limits fumi encouragement requests per user', function () {
    fakeFumiEncouragementResponse();

    $image = Image::factory()->create();
    $user = User::factory()->create();

    RateLimiter::clear('story-encouragement:'.$user->id);

    for ($attempt = 0; $attempt < 5; $attempt++) {
        Livewire::actingAs($user)
            ->test('pages::stories.create', ['image' => $image])
            ->set('title', "作品{$attempt}")
            ->set('body', "本文{$attempt}")
            ->set('status', 'complete')
            ->call('save')
            ->assertSessionHas('fumi_message');
    }

    Livewire::actingAs($user)
        ->test('pages::stories.create', ['image' => $image])
        ->set('title', '6作品目')
        ->set('body', '6本目の本文')
        ->set('status', 'complete')
        ->call('save')
        ->assertSessionMissing('fumi_message');
});

it('does not consume the encouragement rate limit when gemini fails', function () {
    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([], 500),
    ]);

    $image = Image::factory()->create();
    $user = User::factory()->create();

    RateLimiter::clear('story-encouragement:'.$user->id);

    Livewire::actingAs($user)
        ->test('pages::stories.create', ['image' => $image])
        ->set('title', '失敗')
        ->set('body', '本文です。')
        ->set('status', 'complete')
        ->call('save')
        ->assertSessionMissing('fumi_message');

    expect(RateLimiter::attempts('story-encouragement:'.$user->id))->toBe(0);
});
