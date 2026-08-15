<?php

use App\Livewire\Concerns\NotifiesUser;
use App\Models\Story;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Layout('layouts.public')] class extends Component
{
    use NotifiesUser;

    public Story $story;

    #[Validate('required|string|max:2000')]
    public string $body = '';

    /**
     * Bind the story, enforcing that drafts require login.
     */
    public function mount(Story $story): void
    {
        abort_unless($story->isReadableBy(auth()->user()), 403);

        $this->story = $story->load(['user', 'image']);
    }

    /**
     * Toggle the current user's like on this story.
     */
    public function toggleLike(): void
    {
        abort_unless(auth()->check(), 403);

        $existingLike = $this->story->likes()
            ->where('user_id', auth()->id())
            ->first();

        if ($existingLike) {
            $existingLike->delete();
        } else {
            $this->story->likes()->create([
                'user_id' => auth()->id(),
            ]);
        }
    }

    /**
     * Toggle the current user's bookmark on this story.
     */
    public function toggleBookmark(): void
    {
        abort_unless(auth()->check(), 403);

        $existingBookmark = $this->story->bookmarks()
            ->where('user_id', auth()->id())
            ->first();

        if ($existingBookmark) {
            $existingBookmark->delete();
        } else {
            $this->story->bookmarks()->create([
                'user_id' => auth()->id(),
            ]);
        }
    }

    /**
     * Post a comment as the logged-in user.
     */
    public function addComment(): void
    {
        abort_unless(auth()->check(), 403);

        $validated = $this->validate();

        $this->story->comments()->create([
            'body' => $validated['body'],
            'user_id' => auth()->id(),
        ]);

        $this->reset('body');

        $this->notify('コメントを投稿しました。');
    }

    /**
     * Delete one of the current user's own comments.
     */
    public function deleteComment(int $commentId): void
    {
        $comment = $this->story->comments()->findOrFail($commentId);

        abort_unless($comment->user_id === auth()->id(), 403);

        $comment->delete();

        $this->notify('コメントを削除しました。');
    }

    /**
     * Delete the story, allowing only its author.
     */
    public function deleteStory(): void
    {
        abort_unless($this->story->user_id === auth()->id(), 403);

        $image = $this->story->image;

        $this->story->delete();

        session()->flash('status', 'ストーリーを削除しました。');

        $this->redirectRoute('images.show', $image, navigate: true);
    }

    /**
     * Data passed to the view.
     *
     * @return array<string, mixed>
     */
    public function with(): array
    {
        return [
            'comments' => $this->story->comments()
                ->with('user')
                ->latest()
                ->get(),
            'likesCount' => $this->story->likes()->count(),
            'isLiked' => auth()->check()
                && $this->story->likes()->where('user_id', auth()->id())->exists(),
            'isBookmarked' => auth()->check()
                && $this->story->bookmarks()->where('user_id', auth()->id())->exists(),
        ];
    }
}; ?>

<div class="mx-auto max-w-2xl">
    <a href="{{ route('images.show', $story->image) }}" class="btn btn-ghost btn-sm mb-4" wire:navigate>
        お題「{{ $story->image->title }}」へ戻る
    </a>

    <article>
        <div class="flex items-start justify-between gap-4">
            <h1 class="text-2xl font-bold">{{ $story->title }}</h1>

            <x-story-status-badge :status="$story->status" />
        </div>

        <div class="mt-2 flex items-center gap-3">
            <x-user-avatar :user="$story->user" />
            <span class="text-sm">{{ $story->user?->name ?? '不明' }}</span>
            <span class="text-sm text-base-content/60">{{ $story->created_at->format('Y年n月j日') }}</span>

            @if ($story->user_id === auth()->id())
                <div class="ml-auto flex items-center gap-2">
                    <a href="{{ route('stories.edit', $story) }}" class="btn btn-ghost btn-sm" wire:navigate>
                        編集
                    </a>
                    <button
                        type="button"
                        wire:click="deleteStory"
                        wire:confirm="このストーリーを削除しますか？この操作は取り消せません。"
                        class="btn btn-ghost btn-sm"
                    >
                        削除
                    </button>
                </div>
            @endif
        </div>

        <div class="divider my-6"></div>

        <div class="mb-3 flex justify-end">
            <x-story-body-font-picker />
        </div>

        <x-story-body :body="$story->body" class="leading-relaxed" />

        <div class="mt-8 flex items-center gap-4">
            @auth
                <button
                    type="button"
                    wire:click="toggleLike"
                    aria-label="いいね"
                    @class([
                        'btn btn-sm gap-1',
                        'btn-primary' => $isLiked,
                        'btn-ghost' => ! $isLiked,
                    ])
                >
                    <span aria-hidden="true">{{ $isLiked ? '♥' : '♡' }}</span>
                    <span>{{ $likesCount }}</span>
                </button>
            @else
                <span class="inline-flex items-center gap-1 text-sm text-base-content/70" aria-label="いいね {{ $likesCount }}件">
                    <span aria-hidden="true">♡</span>
                    <span>{{ $likesCount }}</span>
                </span>
            @endauth

            @auth
                <button
                    type="button"
                    class="btn btn-ghost btn-sm gap-1"
                    aria-label="コメント {{ $comments->count() }}件"
                    x-on:click="
                        const el = document.getElementById('comment-body');
                        el?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        el?.focus({ preventScroll: true });
                    "
                >
                    <span aria-hidden="true">💬</span>
                    <span>{{ $comments->count() }}</span>
                </button>
            @else
                <a
                    href="#comments"
                    class="inline-flex items-center gap-1 text-sm text-base-content/70"
                    aria-label="コメント {{ $comments->count() }}件"
                >
                    <span aria-hidden="true">💬</span>
                    <span>{{ $comments->count() }}</span>
                </a>
            @endauth

            @auth
                <button
                    type="button"
                    wire:click="toggleBookmark"
                    @class([
                        'btn btn-sm ml-auto',
                        'btn-primary' => $isBookmarked,
                        'btn-ghost' => ! $isBookmarked,
                    ])
                >
                    {{ $isBookmarked ? 'お気に入り済み' : 'お気に入りに追加' }}
                </button>
            @endauth
        </div>
    </article>

    <div class="divider my-8"></div>

    <section id="comments">
        <h2 class="text-xl font-bold">コメント（{{ $comments->count() }}）</h2>

        @auth
            <form wire:submit="addComment" class="mt-4">
                <textarea
                    id="comment-body"
                    wire:model="body"
                    placeholder="このストーリーへの感想を書いてみましょう"
                    rows="3"
                    class="textarea textarea-bordered w-full @error('body') textarea-error @enderror"
                ></textarea>
                @error('body')
                    <p class="mt-1 text-sm text-error">{{ $message }}</p>
                @enderror
                <div class="mt-2 flex justify-end">
                    <button type="submit" class="btn btn-primary btn-sm">
                        コメントする
                    </button>
                </div>
            </form>

            <div class="mt-6 flex flex-col gap-4">
                @forelse ($comments as $comment)
                    <div wire:key="comment-{{ $comment->id }}" class="card bg-base-100 shadow">
                        <div class="card-body p-4">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <x-user-avatar :user="$comment->user" />
                                        <span class="font-medium">{{ $comment->user?->name ?? '不明' }}</span>
                                        <span class="text-sm text-base-content/60">{{ $comment->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="mt-2 whitespace-pre-line">{{ $comment->body }}</p>
                                </div>

                                @if ($comment->user_id === auth()->id())
                                    <button
                                        type="button"
                                        wire:click="deleteComment({{ $comment->id }})"
                                        wire:confirm="このコメントを削除しますか？"
                                        class="btn btn-ghost btn-sm shrink-0"
                                    >
                                        削除
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-base-content/60">まだコメントはありません。最初のコメントを書いてみましょう。</p>
                @endforelse
            </div>
        @else
            <div role="alert" class="alert alert-info mt-4">
                <div>
                    <h3 class="font-bold">コメントはログインすると読めます</h3>
                    <p class="text-sm">
                        <a href="{{ route('login') }}" class="link link-primary" wire:navigate>ログイン</a>
                        すると、コメントの閲覧と投稿ができます。
                    </p>
                </div>
            </div>
        @endauth
    </section>
</div>
