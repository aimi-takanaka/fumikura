<?php

use App\Models\Bookmark;
use App\StoryStatus;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.public')] #[Title('お気に入り')] class extends Component
{
    /**
     * Remove one of the current user's bookmarks.
     */
    public function removeBookmark(int $bookmarkId): void
    {
        $bookmark = auth()->user()->bookmarks()->findOrFail($bookmarkId);

        $bookmark->delete();

        Flux::toast('お気に入りから外しました。', variant: 'success');
    }

    /**
     * Data passed to the view.
     *
     * @return array<string, mixed>
     */
    public function with(): array
    {
        return [
            'bookmarks' => auth()->user()->bookmarks()
                ->with(['story.user', 'story.image'])
                ->latest()
                ->get(),
        ];
    }
}; ?>

<div>
    <flux:heading size="xl">お気に入り</flux:heading>
    <flux:text class="mt-1">保存したストーリーをここから読み返せます。</flux:text>

    <flux:separator class="my-6" />

    @if ($bookmarks->isEmpty())
        <flux:callout icon="bookmark">
            <flux:callout.heading>まだお気に入りがありません</flux:callout.heading>
            <flux:callout.text>ストーリー詳細ページの「保存」ボタンから追加できます。</flux:callout.text>
        </flux:callout>
    @else
        <div class="flex flex-col gap-4">
            @foreach ($bookmarks as $bookmark)
                <flux:card wire:key="bookmark-{{ $bookmark->id }}">
                    <div class="flex items-start justify-between gap-4">
                        <a href="{{ route('stories.show', $bookmark->story) }}" wire:navigate class="min-w-0 transition hover:opacity-80">
                            <div class="flex items-center gap-2">
                                @if ($bookmark->story->status === StoryStatus::Complete)
                                    <flux:badge size="sm" color="lime">完成</flux:badge>
                                @else
                                    <flux:badge size="sm" color="zinc">散文・アイデア</flux:badge>
                                @endif
                            </div>
                            <flux:heading size="lg" class="mt-2 truncate">{{ $bookmark->story->title }}</flux:heading>
                            <flux:text size="sm" class="mt-1">
                                {{ $bookmark->story->user?->name ?? '不明' }}
                                ・お題「{{ $bookmark->story->image->title }}」
                            </flux:text>
                            <flux:text class="mt-2 line-clamp-2">{{ $bookmark->story->body }}</flux:text>
                        </a>

                        <flux:button
                            wire:click="removeBookmark({{ $bookmark->id }})"
                            wire:confirm="お気に入りから外しますか？"
                            variant="ghost"
                            size="sm"
                            icon="bookmark-slash"
                            inset
                        />
                    </div>
                </flux:card>
            @endforeach
        </div>
    @endif
</div>
