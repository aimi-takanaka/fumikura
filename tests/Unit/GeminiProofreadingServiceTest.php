<?php

use App\Services\GeminiProofreadingService;
use App\StoryStatus;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config([
        'services.gemini.key' => 'test-key',
        'services.gemini.model' => 'gemini-2.0-flash',
    ]);
});

it('returns the typo check result from gemini', function () {
    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            ['text' => '・「こんにちわ」→「こんにちは」'],
                        ],
                    ],
                ],
            ],
        ]),
    ]);

    $result = app(GeminiProofreadingService::class)->checkTypos('こんにちわ');

    expect($result)->toBe('・「こんにちわ」→「こんにちは」');
});

it('throws when the gemini api key is missing', function () {
    config(['services.gemini.key' => null]);

    app(GeminiProofreadingService::class)->checkTypos('テスト');
})->throws(RuntimeException::class, 'Gemini API key is not configured.');

it('throws when the gemini api request fails', function () {
    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response(['error' => ['message' => 'boom']], 500),
    ]);

    expect(fn () => app(GeminiProofreadingService::class)->checkTypos('テスト'))
        ->toThrow(RuntimeException::class, 'Gemini API request failed. {"error":{"message":"boom"}}');
});

it('uses the configured ca bundle for outbound requests', function () {
    $caBundle = storage_path('app/test-cacert.pem');
    file_put_contents($caBundle, 'test-ca-bundle');

    config(['services.http.ca_bundle' => $caBundle]);

    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            ['text' => '誤字・脱字は見つかりませんでした。'],
                        ],
                    ],
                ],
            ],
        ]),
    ]);

    $result = app(GeminiProofreadingService::class)->checkTypos('テスト');

    expect($result)->toBe('誤字・脱字は見つかりませんでした。');

    unlink($caBundle);
});

it('returns encouragement for a completed story from gemini', function () {
    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            ['text' => '灯台の光が印象的だね！完成おめでとう！'],
                        ],
                    ],
                ],
            ],
        ]),
    ]);

    $result = app(GeminiProofreadingService::class)->encourageStory(
        StoryStatus::Complete,
        '海辺の灯台',
        'その夜、灯台の光は消えた。',
    );

    expect($result)->toBe('灯台の光が印象的だね！完成おめでとう！');
});

it('builds status-specific encouragement prompts for gemini', function () {
    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            ['text' => 'ふみより'],
                        ],
                    ],
                ],
            ],
        ]),
    ]);

    app(GeminiProofreadingService::class)->encourageStory(
        StoryStatus::Idea,
        '森の小道',
        '鹿が道標を見上げている。',
    );

    Http::assertSent(function ($request) {
        $prompt = data_get($request->data(), 'contents.0.parts.0.text');

        return is_string($prompt)
            && str_contains($prompt, 'ウサギの「ふみ」')
            && str_contains($prompt, '【お題タイトル】森の小道')
            && str_contains($prompt, '【アイデア投稿】鹿が道標を見上げている。');
    });
});

it('returns another story suggestions from gemini json', function () {
    $payload = json_encode([
        'suggestions' => [
            [
                'viewpoint' => 'ヒロインの妹',
                'hook' => '彼女は窓の外で全部見ていた',
                'comment' => '同じ夜を、妹の目で読むと景色ががらっと変わりそうだよ！',
            ],
        ],
    ], JSON_UNESCAPED_UNICODE);

    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            ['text' => $payload],
                        ],
                    ],
                ],
            ],
        ]),
    ]);

    $result = app(GeminiProofreadingService::class)->suggestAnotherStories(
        '雨上がりの駅',
        '彼はホームで傘を差し出した。',
    );

    expect($result)->toHaveCount(1)
        ->and($result[0]['viewpoint'])->toBe('ヒロインの妹')
        ->and($result[0]['hook'])->toBe('彼女は窓の外で全部見ていた')
        ->and($result[0]['comment'])->toBe('同じ夜を、妹の目で読むと景色ががらっと変わりそうだよ！');

    Http::assertSent(function ($request) {
        $prompt = data_get($request->data(), 'contents.0.parts.0.text');
        $mimeType = data_get($request->data(), 'generationConfig.responseMimeType');

        return is_string($prompt)
            && str_contains($prompt, '別の視点から見た物語のアイデア')
            && str_contains($prompt, '【お題タイトル】雨上がりの駅')
            && str_contains($prompt, '【完成した物語】彼はホームで傘を差し出した。')
            && $mimeType === 'application/json';
    });
});

it('parses fenced json for another story suggestions', function () {
    $payload = <<<'JSON'
```json
{
  "suggestions": [
    {
      "viewpoint": "駅の猫",
      "hook": "ベンチの下から見ていた夜",
      "comment": "人間には見えない細部が、猫視点だと浮かび上がりそうだね！"
    }
  ]
}
```
JSON;

    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            ['text' => $payload],
                        ],
                    ],
                ],
            ],
        ]),
    ]);

    $result = app(GeminiProofreadingService::class)->suggestAnotherStories('雨上がりの駅', '物語本文');

    expect($result)->toHaveCount(1)
        ->and($result[0]['viewpoint'])->toBe('駅の猫');
});

it('limits another story suggestions to two items', function () {
    $payload = json_encode([
        'suggestions' => [
            ['viewpoint' => 'A', 'hook' => 'フック1', 'comment' => 'コメント1'],
            ['viewpoint' => 'B', 'hook' => 'フック2', 'comment' => 'コメント2'],
            ['viewpoint' => 'C', 'hook' => 'フック3', 'comment' => 'コメント3'],
        ],
    ], JSON_UNESCAPED_UNICODE);

    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            ['text' => $payload],
                        ],
                    ],
                ],
            ],
        ]),
    ]);

    $result = app(GeminiProofreadingService::class)->suggestAnotherStories('お題', '本文');

    expect($result)->toHaveCount(2)
        ->and($result[0]['viewpoint'])->toBe('A')
        ->and($result[1]['viewpoint'])->toBe('B');
});
