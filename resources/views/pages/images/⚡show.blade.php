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
    <a href="{{ route('topics.index') }}" class="btn btn-ghost btn-sm mb-4" wire:navigate>
        ← お題一覧へ戻る
    </a>

    <div class="card card-border bg-base-100 overflow-hidden">
        <figure class="relative aspect-video bg-base-200">
            @if ($image->file_path && Illuminate\Support\Facades\Storage::disk('public')->exists($image->file_path))
                <img
                    src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($image->file_path) }}"
                    alt="{{ $image->title }}"
                    class="size-full object-contain"
                />
            @else
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-base-content/20" />
            @endif
        </figure>

        <div class="card-body p-5">
            <h1 class="text-2xl font-bold">{{ $image->title }}</h1>
            <p class="mt-1 text-base-content/70">投稿者: {{ $image->user?->name ?? '不明' }}</p>
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
                                        @class([
                                            'btn btn-sm gap-1',
                                            'btn-primary' => $story->is_liked,
                                            'btn-ghost' => ! $story->is_liked,
                                        ])
                                    >
                                        ♥ {{ $story->likes_count }}
                                    </button>
                                @else
                                    <span class="flex items-center gap-1 text-sm text-base-content/70">
                                        ♥ {{ $story->likes_count }}
                                    </span>
                                @endauth
                                <button
                                    type="button"
                                    wire:click="$dispatch('open-story-comments', { storyId: {{ $story->id }} })"
                                    class="btn btn-ghost btn-sm gap-1"
                                >
                                    💬 {{ $story->comments_count }}
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
