<?php

use App\Models\Image;
use App\StoryStatus;
use Livewire\Attributes\Layout;
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
                ->unless(auth()->check(), fn ($query) => $query->complete())
                ->latest()
                ->get(),
        ];
    }
}; ?>

<div>
    <flux:button :href="route('home')" variant="ghost" size="sm" icon="arrow-left" wire:navigate class="mb-4">
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
                ※ ログインすると、下書き（散文・アイデア）も読めるようになります。
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
                    <a
                        href="{{ route('stories.show', $story) }}"
                        wire:key="story-{{ $story->id }}"
                        wire:navigate
                        class="block transition hover:opacity-90"
                    >
                    <flux:card>
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <flux:heading size="lg" class="truncate">{{ $story->title }}</flux:heading>
                                <flux:text size="sm" class="mt-1">{{ $story->user?->name ?? '不明' }}</flux:text>
                            </div>

                            @if ($story->status === StoryStatus::Complete)
                                <flux:badge size="sm" color="lime">完成</flux:badge>
                            @else
                                <flux:badge size="sm" color="zinc">散文・アイデア</flux:badge>
                            @endif
                        </div>

                        <flux:text class="mt-3 line-clamp-3">{{ $story->body }}</flux:text>

                        <div class="mt-4 flex items-center gap-4">
                            <flux:text size="sm" class="flex items-center gap-1">
                                <flux:icon.heart variant="micro" /> {{ $story->likes_count }}
                            </flux:text>
                            <flux:text size="sm" class="flex items-center gap-1">
                                <flux:icon.chat-bubble-oval-left variant="micro" /> {{ $story->comments_count }}
                            </flux:text>
                        </div>
                    </flux:card>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
