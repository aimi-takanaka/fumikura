<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiProofreadingService
{
    /**
     * Ask Gemini to check the given text for typos only.
     */
    public function checkTypos(string $body): string
    {
        $apiKey = config('services.gemini.key');

        if (blank($apiKey)) {
            throw new RuntimeException('Gemini API key is not configured.');
        }

        $model = config('services.gemini.model');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

        try {
            $response = $this->httpClient()
                ->withHeaders([
                    'x-goog-api-key' => $apiKey,
                ])
                ->post($url, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $this->buildTypoCheckPrompt($body)],
                            ],
                        ],
                    ],
                ]);
        } catch (ConnectionException $exception) {
            throw new RuntimeException($exception->getMessage(), previous: $exception);
        }

        if ($response->failed()) {
            throw new RuntimeException('Gemini API request failed.');
        }

        $text = data_get($response->json(), 'candidates.0.content.parts.0.text');

        if (! is_string($text) || trim($text) === '') {
            throw new RuntimeException('Gemini API returned an empty response.');
        }

        return trim($text);
    }

    /**
     * Build an HTTP client configured for outbound API requests.
     */
    private function httpClient(): PendingRequest
    {
        $client = Http::timeout(30);

        $caBundle = config('services.http.ca_bundle');

        if (is_string($caBundle) && $caBundle !== '' && file_exists($caBundle)) {
            return $client->withOptions(['verify' => $caBundle]);
        }

        return $client;
    }

    /**
     * Build the typo-check prompt for Gemini.
     */
    private function buildTypoCheckPrompt(string $body): string
    {
        return <<<PROMPT
あなたは日本語の校正者です。以下の文章について、誤字・脱字・タイプミスのみを指摘してください。
表現の改善や文体の添削はしないでください。
問題がなければ「誤字・脱字は見つかりませんでした。」とだけ答えてください。
問題がある場合は、箇条書きで簡潔に指摘してください。原文全体を書き直さないでください。

---
{$body}
---
PROMPT;
    }
}
