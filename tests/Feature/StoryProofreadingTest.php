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
function fakeSuccessfulGeminiResponse(string $text = '誤字・脱字は見つかりませんでした。'): void
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

it('shows the typo check button on the create form', function () {
    fakeSuccessfulGeminiResponse();

    $image = Image::factory()->create();

    Livewire::actingAs(User::factory()->create())
        ->test('pages::stories.create', ['image' => $image])
        ->assertSee('誤字チェック');
});

it('shows typo check results on the create form without changing the body', function () {
    fakeSuccessfulGeminiResponse();

    $image = Image::factory()->create();
    $body = 'こんにちわ';

    Livewire::actingAs(User::factory()->create())
        ->test('pages::stories.create', ['image' => $image])
        ->set('body', $body)
        ->call('checkTypos')
        ->assertHasNoErrors()
        ->assertSet('body', $body)
        ->assertSee('誤字チェック結果')
        ->assertSee('誤字・脱字は見つかりませんでした。');
});

it('shows typo check results on the edit form', function () {
    fakeSuccessfulGeminiResponse();

    $user = User::factory()->create();
    $story = Story::factory()->for($user)->create(['body' => 'こんにちわ']);

    Livewire::actingAs($user)
        ->test('pages::stories.edit', ['story' => $story])
        ->call('checkTypos')
        ->assertHasNoErrors()
        ->assertSet('body', 'こんにちわ')
        ->assertSee('誤字・脱字は見つかりませんでした。');
});

it('requires body text before running typo check', function () {
    fakeSuccessfulGeminiResponse();

    $image = Image::factory()->create();

    Livewire::actingAs(User::factory()->create())
        ->test('pages::stories.create', ['image' => $image])
        ->set('body', '')
        ->call('checkTypos')
        ->assertHasErrors(['body' => 'required']);
});

it('clears typo check results when the body changes', function () {
    fakeSuccessfulGeminiResponse();

    $image = Image::factory()->create();

    Livewire::actingAs(User::factory()->create())
        ->test('pages::stories.create', ['image' => $image])
        ->set('body', 'こんにちわ')
        ->call('checkTypos')
        ->assertSee('誤字チェック結果')
        ->set('body', 'こんにちは')
        ->assertDontSee('誤字チェック結果');
});

it('shows an error when the gemini api fails', function () {
    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response(status: 500),
    ]);

    $image = Image::factory()->create();

    Livewire::actingAs(User::factory()->create())
        ->test('pages::stories.create', ['image' => $image])
        ->set('body', 'テスト本文')
        ->call('checkTypos')
        ->assertSee('誤字チェックに失敗しました');
});

it('rate limits typo checks per user', function () {
    fakeSuccessfulGeminiResponse();

    $user = User::factory()->create();
    $image = Image::factory()->create();

    RateLimiter::clear('story-proofreading:'.$user->id);

    $component = Livewire::actingAs($user)
        ->test('pages::stories.create', ['image' => $image])
        ->set('body', 'テスト本文');

    foreach (range(1, 5) as $attempt) {
        $component->call('checkTypos')->assertHasNoErrors();
    }

    $component
        ->call('checkTypos')
        ->assertHasErrors(['body']);
});
