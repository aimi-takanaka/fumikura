<?php

use App\Livewire\Concerns\NotifiesUser;
use App\Models\Comment;
use App\Models\Story;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component
{
    use NotifiesUser;

    public bool $showModal = false;

    public ?Story $story = null;

    #[Validate('required|string|max:2000')]
    public string $body = '';

    /**
     * Open the comments modal for the given story.
     */
    #[On('open-story-comments')]
    public function open(int $storyId): void
    {
        $story = Story::query()->findOrFail($storyId);

        abort_unless($story->isReadableBy(auth()->user()), 403);

        $this->story = $story->load(['user', 'image']);
        $this->reset('body');
        $this->resetValidation();
        $this->showModal = true;

        if (auth()->check()) {
            $this->js(<<<'JS'
                requestAnimationFrame(() => {
                    document.getElementById('modal-comment-body')?.focus({ preventScroll: true });
                });
            JS);
        }
    }

    /**
     * Reset state when the modal closes.
     */
    public function close(): void
    {
        $this->showModal = false;
        $this->story = null;
        $this->reset('body');
        $this->resetValidation();
    }

    /**
     * Post a comment as the logged-in user.
     */
    public function addComment(): void
    {
        abort_unless(auth()->check(), 403);
        abort_unless($this->story instanceof Story, 404);

        $validated = $this->validate();

        $this->story->comments()->create([
            'body' => $validated['body'],
            'user_id' => auth()->id(),
        ]);

        $this->reset('body');

        $this->dispatch('comments-updated');

        $this->notify('コメントを投稿しました。');
    }

    /**
     * Delete one of the current user's own comments.
     */
    public function deleteComment(int $commentId): void
    {
        abort_unless($this->story instanceof Story, 404);

        $comment = $this->story->comments()->findOrFail($commentId);

        abort_unless($comment->user_id === auth()->id(), 403);

        $comment->delete();

        $this->dispatch('comments-updated');

        $this->notify('コメントを削除しました。');
    }

    /**
     * Data passed to the view.
     *
     * @return array<string, mixed>
     */
    public function with(): array
    {
        /** @var Collection<int, Comment> $comments */
        $comments = $this->story instanceof Story
            ? $this->story->comments()->with('user')->latest()->get()
            : collect();

        return [
            'comments' => $comments,
        ];
    }
}; ?>

<div
    x-data
    x-effect="
        if ($wire.showModal) {
            $refs.commentsDialog?.showModal();
        } else if ($refs.commentsDialog?.open) {
            $refs.commentsDialog.close();
        }
    "
>
    <dialog
        x-ref="commentsDialog"
        class="modal"
        wire:ignore.self
        @close="$wire.close()"
    >
        <div class="modal-box flex max-h-[min(36rem,calc(100vh-4rem))] w-full max-w-lg flex-col gap-4">
            @if ($story)
                <div class="shrink-0">
                    <h3 class="text-lg font-bold">💬 コメント（{{ $comments->count() }}）</h3>
                    <p class="mt-1 truncate text-sm text-base-content/70">{{ $story->title }}</p>
                </div>

                <div class="min-h-0 flex-1 overflow-y-auto">
                    <div class="flex flex-col gap-4">
                        @forelse ($comments as $comment)
                            <div wire:key="modal-comment-{{ $comment->id }}" class="card bg-base-200 shadow-sm">
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

                                        @if (auth()->check() && $comment->user_id === auth()->id())
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
                </div>

                @auth
                    <form wire:submit="addComment" class="shrink-0 border-t border-base-300 pt-4">
                        <textarea
                            id="modal-comment-body"
                            wire:model="body"
                            placeholder="このストーリーへの感想を書いてみましょう"
                            rows="3"
                            class="textarea textarea-bordered w-full @error('body') textarea-error @enderror"
                        ></textarea>
                        @error('body')
                            <p class="mt-1 text-sm text-error">{{ $message }}</p>
                        @enderror
                        <div class="mt-2 flex justify-end gap-2">
                            <button type="button" wire:click="close" class="btn btn-ghost btn-sm">
                                閉じる
                            </button>
                            <button type="submit" class="btn btn-primary btn-sm">
                                コメントする
                            </button>
                        </div>
                    </form>
                @else
                    <div role="alert" class="alert alert-info shrink-0">
                        <div>
                            <h4 class="font-bold">コメントするにはログインが必要です</h4>
                            <p class="text-sm">
                                <a href="{{ route('login') }}" class="link link-primary" wire:navigate>ログイン</a>
                                すると、コメントを投稿できます。
                            </p>
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="button" wire:click="close" class="btn btn-ghost btn-sm">
                            閉じる
                        </button>
                    </div>
                @endauth
            @endif
        </div>

        <form method="dialog" class="modal-backdrop">
            <button type="submit" tabindex="-1">close</button>
        </form>
    </dialog>
</div>
