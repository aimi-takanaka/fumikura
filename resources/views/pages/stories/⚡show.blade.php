<?php

use App\Models\Story;
use App\StoryStatus;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.public')] class extends Component
{
    public Story $story;

    /**
     * Bind the story, enforcing that drafts require login.
     */
    public function mount(Story $story): void
    {
        abort_if($story->status === StoryStatus::Draft && ! auth()->check(), 403);

        $this->story = $story->load(['user', 'image'])
            ->loadCount(['likes', 'comments']);
    }
}; ?>

<div class="mx-auto max-w-2xl">
    <flux:button :href="route('images.show', $story->image)" variant="ghost" size="sm" icon="arrow-left" wire:navigate class="mb-4">
        お題「{{ $story->image->title }}」へ戻る
    </flux:button>

    <article>
        <div class="flex items-start justify-between gap-4">
            <flux:heading size="xl">{{ $story->title }}</flux:heading>

            @if ($story->status === StoryStatus::Complete)
                <flux:badge color="lime">完成</flux:badge>
            @else
                <flux:badge color="zinc">散文・アイデア</flux:badge>
            @endif
        </div>

        <div class="mt-2 flex items-center gap-3">
            <flux:text size="sm">{{ $story->user?->name ?? '不明' }}</flux:text>
            <flux:text size="sm">{{ $story->created_at->format('Y年n月j日') }}</flux:text>
        </div>

        <flux:separator class="my-6" />

        <div class="leading-relaxed whitespace-pre-line">{{ $story->body }}</div>

        <div class="mt-8 flex items-center gap-4">
            <flux:text class="flex items-center gap-1">
                <flux:icon.heart variant="micro" /> {{ $story->likes_count }}
            </flux:text>
            <flux:text class="flex items-center gap-1">
                <flux:icon.chat-bubble-oval-left variant="micro" /> {{ $story->comments_count }}
            </flux:text>
        </div>
    </article>

    <flux:separator class="my-8" />

    <section>
        <flux:heading size="lg">コメント（{{ $story->comments_count }}）</flux:heading>
        <flux:text class="mt-2" size="sm">コメント機能は次のステップで追加予定です。</flux:text>
    </section>
</div>
