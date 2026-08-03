<?php

use App\Models\Image;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

new #[Layout('layouts.public')] class extends Component
{
    public Image $image;

    /**
     * Bind the image from the route and load its author.
     */
    public function mount(Image $image): void
    {
        $this->image = $image->load('user');
    }

    /**
     * Re-render the story list after comments change in the modal.
     */
    #[On('comments-updated')]
    public function refreshCommentCounts(): void
    {
        //
    }

    /**
     * Toggle the current user's like on a story in this topic list.
     */
    public function toggleLike(int $storyId): void
    {
        abort_unless(auth()->check(), 403);

        $story = $this->image->stories()
            ->readableBy(auth()->user())
            ->findOrFail($storyId);

        $existingLike = $story->likes()
            ->where('user_id', auth()->id())
            ->first();

        if ($existingLike) {
            $existingLike->delete();
        } else {
            $story->likes()->create([
                'user_id' => auth()->id(),
            ]);
        }
    }

    /**
     * Data passed to the view.
     *
     * @return array<string, mixed>
     */
    public function with(): array
    {
        return [
            'stories' => $this->image->stories()
                ->with('user')
                ->withCount(['likes', 'comments'])
                ->when(
                    auth()->check(),
                    fn ($query) => $query->withExists([
                        'likes as is_liked' => fn ($query) => $query->where('user_id', auth()->id()),
                    ]),
                )
                ->readableBy(auth()->user())
                ->latest()
                ->get(),
        ];
    }
}; ?>

<div>
    <flux:button :href="route('topics.index')" variant="ghost" size="sm" icon="arrow-left" wire:navigate class="mb-4">
        お題一覧へ戻る
    </flux:button>

    <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
        <div class="relative aspect-video bg-zinc-100 dark:bg-zinc-800">
            @if ($image->file_path && Illuminate\Support\Facades\Storage::disk('public')->exists($image->file_path))
                <img
                    src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($image->file_path) }}"
                    alt="{{ $image->title }}"
                    class="size-full object-contain"
                />
            @else
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            @endif
        </div>

        <div class="p-5">
            <flux:heading size="xl">{{ $image->title }}</flux:heading>
            <flux:text class="mt-1">投稿者: {{ $image->user?->name ?? '不明' }}</flux:text>
        </div>
    </div>

    <div class="mt-10">
        <div class="mb-4 flex items-center justify-between">
            <flux:heading size="lg">ストーリー（{{ $stories->count() }}）</flux:heading>

            @auth
                <flux:button :href="route('stories.create', $image)" variant="primary" size="sm" icon="pencil" wire:navigate>ストーリーを書く</flux:button>
            @endauth
        </div>

        @guest
            <flux:text class="mb-4" size="sm">
                ※ ログインすると、アイデア投稿も読めるようになります。
            </flux:text>
        @endguest

        @if ($stories->isEmpty())
            <flux:callout icon="book-open">
                <flux:callout.heading>まだストーリーがありません</flux:callout.heading>
                <flux:callout.text>このお題への最初のストーリーを書いてみましょう。</flux:callout.text>
            </flux:callout>
        @else
            <div class="flex flex-col gap-4">
                @foreach ($stories as $story)
                    <flux:card wire:key="story-{{ $story->id }}">
                        <a
                            href="{{ route('stories.show', $story) }}"
                            wire:navigate
                            class="block transition hover:opacity-90"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <flux:heading size="lg" class="truncate">{{ $story->title }}</flux:heading>
                                    <div class="mt-1 flex items-center gap-2">
                                        <x-user-avatar :user="$story->user" />
                                        <flux:text size="sm">{{ $story->user?->name ?? '不明' }}</flux:text>
                                    </div>
                                </div>

                                <x-story-status-badge :status="$story->status" />
                            </div>

                            <x-story-read-more :body="$story->body" :lines="3" class="mt-3" />
                        </a>

                        <div class="mt-4 flex items-center gap-4">
                            @auth
                                <flux:button
                                    wire:click="toggleLike({{ $story->id }})"
                                    :variant="$story->is_liked ? 'primary' : 'ghost'"
                                    size="sm"
                                    icon="heart"
                                    inset
                                >
                                    {{ $story->likes_count }}
                                </flux:button>
                            @else
                                <flux:text size="sm" class="flex items-center gap-1">
                                    <flux:icon.heart variant="micro" /> {{ $story->likes_count }}
                                </flux:text>
                            @endauth
                            <flux:button
                                wire:click="$dispatch('open-story-comments', { storyId: {{ $story->id }} })"
                                variant="ghost"
                                size="sm"
                                icon="chat-bubble-oval-left"
                                inset
                            >
                                {{ $story->comments_count }}
                            </flux:button>
                        </div>
                    </flux:card>
                @endforeach
            </div>
        @endif
    </div>

    <livewire:story-comments-modal />
</div>
