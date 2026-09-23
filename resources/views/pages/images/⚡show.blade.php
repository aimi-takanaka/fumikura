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
     * Open the comments modal for a story on this topic.
     */
    public function openComments(int $storyId): void
    {
        $story = $this->image->stories()
            ->readableBy(auth()->user())
            ->findOrFail($storyId);

        $this->dispatch('open-story-comments', storyId: $story->id);
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
    <a href="{{ route('topics.index') }}" class="btn btn-ghost btn-sm mb-4" wire:navigate>
        ← お題一覧へ戻る
    </a>

    <div class="card card-border bg-base-100 overflow-hidden">
        <figure class="relative aspect-video bg-base-200">
            @if ($image->url())
                <img
                    src="{{ $image->url() }}"
                    alt="{{ $image->title }}"
                    class="size-full object-contain"
                />
            @else
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-base-content/20" />
            @endif
        </figure>

        <div class="card-body p-5">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <h1 class="text-2xl font-bold">{{ $image->title }}</h1>
                    @if ($image->hasCredit())
                        <p class="mt-1 text-sm text-base-content/60">
                            {{ $image->credit_type->creditPrefix() }}{{ $image->credit_author }}　
                            @if ($image->creditServiceUrl())
                                <a
                                    href="{{ $image->creditServiceUrl() }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center gap-1 font-medium text-primary underline underline-offset-2 hover:opacity-80"
                                >
                                    <span>{{ $image->credit_service }}</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-3.5 shrink-0" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                    </svg>
                                    <span class="sr-only">（外部サイトへ）</span>
                                </a>
                            @else
                                {{ $image->credit_service }}
                            @endif
                        </p>
                    @endif
                    <p class="mt-1 text-base-content/70">投稿者：{{ $image->user?->name ?? '不明' }}</p>
                </div>

                @if (auth()->user()?->is_admin)
                    <a href="{{ route('admin.images.edit', $image) }}" class="btn btn-ghost btn-sm shrink-0" wire:navigate>
                        編集
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="mt-10">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-xl font-bold">ストーリー（{{ $stories->count() }}）</h2>

            @auth
                <a href="{{ route('stories.create', $image) }}" class="btn btn-primary btn-sm" wire:navigate>
                    ストーリーを書く
                </a>
            @endauth
        </div>

        @guest
            <p class="mb-4 text-sm text-base-content/70">
                ※ ログインすると、アイデア投稿も読めるようになります。
            </p>
        @endguest

        @if ($stories->isEmpty())
            <div role="alert" class="alert">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="size-6 shrink-0 stroke-current">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <div>
                    <h3 class="font-bold">まだストーリーがありません</h3>
                    <div class="text-sm">このお題への最初のストーリーを書いてみましょう。</div>
                </div>
            </div>
        @else
            <div class="flex flex-col gap-4">
                @foreach ($stories as $story)
                    <div class="card card-border bg-base-100" wire:key="story-{{ $story->id }}">
                        <div class="card-body">
                            <a
                                href="{{ route('stories.show', $story) }}"
                                wire:navigate
                                class="block transition hover:opacity-90"
                            >
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0">
                                        <h3 class="truncate text-lg font-bold">{{ $story->title }}</h3>
                                        <div class="mt-1 flex items-center gap-2">
                                            <x-user-avatar :user="$story->user" />
                                            <span class="text-sm text-base-content/70">{{ $story->user?->name ?? '不明' }}</span>
                                        </div>
                                    </div>

                                    <x-story-status-badge :status="$story->status" />
                                </div>

                                <x-story-read-more :body="$story->body" :lines="3" class="mt-3" />
                            </a>

                            <div class="mt-4 flex items-center gap-2">
                                @auth
                                    <button
                                        type="button"
                                        wire:click="toggleLike({{ $story->id }})"
                                        aria-label="いいね"
                                        @class([
                                            'btn btn-sm gap-1',
                                            'btn-primary' => $story->is_liked,
                                            'btn-ghost' => ! $story->is_liked,
                                        ])
                                    >
                                        <span aria-hidden="true">{{ $story->is_liked ? '♥' : '♡' }}</span>
                                        <span>{{ $story->likes_count }}</span>
                                    </button>
                                @else
                                    <span class="inline-flex items-center gap-1 text-sm text-base-content/70" aria-label="いいね {{ $story->likes_count }}件">
                                        <span aria-hidden="true">♡</span>
                                        <span>{{ $story->likes_count }}</span>
                                    </span>
                                @endauth
                                <button
                                    type="button"
                                    wire:click="openComments({{ $story->id }})"
                                    class="btn btn-ghost btn-sm gap-1"
                                    aria-label="コメント {{ $story->comments_count }}件"
                                >
                                    <span aria-hidden="true">💬</span>
                                    <span>{{ $story->comments_count }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <livewire:story-comments-modal />
</div>
