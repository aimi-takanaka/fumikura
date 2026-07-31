<?php

namespace App\Livewire\Concerns;

use App\Services\GeminiProofreadingService;
use App\StoryStatus;
use Illuminate\Support\Facades\RateLimiter;
use RuntimeException;

trait EncouragesStorySubmission
{
    /**
     * Fetch an encouragement message from Fumi for the submitted story.
     */
    protected function fetchFumiEncouragement(
        GeminiProofreadingService $gemini,
        StoryStatus $status,
        string $topicTitle,
        string $body,
    ): ?string {
        if (blank(config('services.gemini.key'))) {
            return null;
        }

        if (mb_strlen($body) > config('services.gemini.max_characters', 5000)) {
            return null;
        }

        $rateLimitKey = 'story-encouragement:'.auth()->id();

        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            return null;
        }

        try {
            $message = $gemini->encourageStory($status, $topicTitle, $body);
        } catch (RuntimeException $exception) {
            report($exception);

            return null;
        }

        RateLimiter::hit($rateLimitKey, 60);

        return $message;
    }

    /**
     * Store Fumi's encouragement message in the session when available.
     */
    protected function flashFumiEncouragement(?string $message): void
    {
        if ($message === null || trim($message) === '') {
            return;
        }

        session()->flash('fumi_message', $message);
    }
}
