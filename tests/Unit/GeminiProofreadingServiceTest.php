<?php

use App\Services\GeminiProofreadingService;
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
        'generativelanguage.googleapis.com/*' => Http::response([], 500),
    ]);

    app(GeminiProofreadingService::class)->checkTypos('テスト');
})->throws(RuntimeException::class, 'Gemini API request failed.');

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
