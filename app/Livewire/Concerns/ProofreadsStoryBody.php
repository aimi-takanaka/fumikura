<?php

namespace App\Livewire\Concerns;

use App\Services\GeminiProofreadingService;
use Illuminate\Support\Facades\RateLimiter;
use RuntimeException;

trait ProofreadsStoryBody
{
    public ?string $proofreadingResult = null;

    /**
     * Clear the proofreading result when the body changes.
     */
    public function updatedBody(): void
    {
        $this->proofreadingResult = null;
    }

    /**
     * Check the story body for typos using Gemini.
     */
    public function checkTypos(GeminiProofreadingService $proofreadingService): void
    {
        $this->validate([
            'body' => 'required|string|max:'.config('services.gemini.max_characters', 5000),
        ]);

        $rateLimitKey = 'story-proofreading:'.auth()->id();

        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);

            $this->addError('body', "誤字チェックは1分あたり5回までです。{$seconds}秒後に再試行してください。");

            return;
        }

        RateLimiter::hit($rateLimitKey, 60);

        try {
            $this->proofreadingResult = $proofreadingService->checkTypos($this->body);
        } catch (RuntimeException $exception) {
            report($exception);

            $message = '誤字チェックに失敗しました。しばらくしてから再度お試しください。';

            if (app()->isLocal() && str_contains($exception->getMessage(), 'cURL error 60')) {
                $message = '誤字チェックに失敗しました。PHPのSSL証明書設定（curl.cainfo）を確認してください。';
            }

            $this->addError('body', $message);
        }
    }

    /**
     * Hide the proofreading result panel.
     */
    public function dismissProofreadingResult(): void
    {
        $this->proofreadingResult = null;
    }
}
