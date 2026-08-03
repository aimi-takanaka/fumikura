<?php

namespace App\Livewire\Concerns;

use App\Models\Image;
use App\Models\Story;
use App\Services\GeminiProofreadingService;
use App\StoryStatus;
use Illuminate\Support\Facades\RateLimiter;
use RuntimeException;

trait ProofreadsStoryBody
{
    public ?string $proofreadingResult = null;

    /**
     * @var list<array{viewpoint: string, hook: string, comment: string}>
     */
    public array $anotherStorySuggestions = [];

    /**
     * Clear AI helper results when the body changes.
     */
    public function updatedBody(): void
    {
        $this->proofreadingResult = null;
        $this->anotherStorySuggestions = [];
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
     * Suggest alternate-viewpoint stories using Gemini.
     */
    public function suggestAnotherStories(GeminiProofreadingService $proofreadingService): void
    {
        $this->validate([
            'body' => 'required|string|max:'.config('services.gemini.max_characters', 5000),
        ]);

        $rateLimitKey = 'story-another-suggestions:'.auth()->id();

        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);

            $this->addError('body', "アナザーストーリーの提案は1分あたり5回までです。{$seconds}秒後に再試行してください。");

            return;
        }

        RateLimiter::hit($rateLimitKey, 60);

        try {
            $this->anotherStorySuggestions = $proofreadingService->suggestAnotherStories(
                $this->resolveTopicTitle(),
                $this->body,
            );
        } catch (RuntimeException $exception) {
            report($exception);

            $message = 'アナザーストーリーの提案に失敗しました。しばらくしてから再度お試しください。';

            if (app()->isLocal() && str_contains($exception->getMessage(), 'cURL error 60')) {
                $message = 'アナザーストーリーの提案に失敗しました。PHPのSSL証明書設定（curl.cainfo）を確認してください。';
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

    /**
     * Hide the another-story suggestions panel.
     */
    public function dismissAnotherStorySuggestions(): void
    {
        $this->anotherStorySuggestions = [];
    }

    /**
     * Turn a suggestion into an idea draft on the create form.
     */
    public function startAnotherStoryDraft(int $index): void
    {
        $suggestion = $this->anotherStorySuggestions[$index] ?? null;

        if (! is_array($suggestion)) {
            return;
        }

        $draft = [
            'title' => mb_substr($suggestion['viewpoint'].'の視点', 0, 255),
            'body' => $suggestion['hook'],
            'status' => StoryStatus::Idea->value,
        ];

        if (property_exists($this, 'image') && $this->image instanceof Image) {
            $this->applyAnotherStoryDraft($draft);

            return;
        }

        session()->put('another_story_draft', $draft);

        $this->redirectRoute('stories.create', $this->resolveImage(), navigate: true);
    }

    /**
     * Apply a prefilled another-story idea draft to the create form.
     *
     * @param  array{title: string, body: string, status: string}  $draft
     */
    private function applyAnotherStoryDraft(array $draft): void
    {
        $this->anotherStorySuggestions = [];
        $this->proofreadingResult = null;
        $this->title = $draft['title'];
        $this->status = $draft['status'];
        $this->body = $draft['body'];
    }

    /**
     * Resolve the topic image from the create or edit page component.
     */
    private function resolveImage(): Image
    {
        if (property_exists($this, 'image') && $this->image instanceof Image) {
            return $this->image;
        }

        if (property_exists($this, 'story') && $this->story instanceof Story) {
            return $this->story->image;
        }

        throw new RuntimeException('Unable to resolve the topic image for another story drafts.');
    }

    /**
     * Resolve the topic title from the create or edit page component.
     */
    private function resolveTopicTitle(): string
    {
        return $this->resolveImage()->title;
    }
}
