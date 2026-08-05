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

<div>
    @if ($showModal && $story)
        <dialog class="modal modal-open" open>
            <div class="modal-box flex max-h-[min(32rem,calc(100vh-8rem))] max-w-lg flex-col">
                <div class="shrink-0">
                    <h3 class="text-lg font-bold">コメント（{{ $comments->count() }}）</h3>
                    <p class="mt-1 truncate text-sm text-base-content/70">{{ $story->title }}</p>
                </div>

                @auth
                    <div class="mt-4 min-h-0 flex-1 overflow-y-auto">
                        <div class="flex flex-col gap-4">
                            @forelse ($comments as $comment)
                                <div wire:key="modal-comment-{{ $comment->id }}" class="card border border-base-300 bg-base-100">
                                    <div class="card-body gap-3 p-4">
                                        <div class="flex items-start justify-between gap-4">
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-2">
                                                    <x-user-avatar :user="$comment->user" />
                                                    <span class="font-medium">{{ $comment->user?->name ?? '不明' }}</span>
                                                    <span class="text-sm text-base-content/60">{{ $comment->created_at->diffForHumans() }}</span>
                                                </div>
                                                <p class="mt-2 whitespace-pre-line text-sm">{{ $comment->body }}</p>
                                            </div>

                                            @if ($comment->user_id === auth()->id())
                                                <button
                                                    type="button"
                                                    class="btn btn-ghost btn-sm btn-square"
                                                    wire:click="deleteComment({{ $comment->id }})"
                                                    wire:confirm="このコメントを削除しますか？"
                                                    aria-label="コメントを削除"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="h-4 w-4 stroke-current">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                    </svg>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-base-content/70">まだコメントはありません。最初のコメントを書いてみましょう。</p>
                            @endforelse
                        </div>
                    </div>

                    <form wire:submit="addComment" class="mt-4 shrink-0 border-t border-base-300 pt-4">
                        <fieldset class="fieldset">
                            <textarea
                                wire:model="body"
                                placeholder="このストーリーへの感想を書いてみましょう"
                                rows="2"
                                class="textarea textarea-bordered w-full"
                            ></textarea>
                            @error('body')
                                <p class="text-sm text-error">{{ $message }}</p>
                            @enderror
                        </fieldset>
                        <div class="mt-2 flex justify-end">
                            <button type="submit" class="btn btn-primary btn-sm">
                                コメントする
                            </button>
                        </div>
                    </form>
                @else
                    <div role="alert" class="alert mt-4">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="h-6 w-6 shrink-0 stroke-current">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2Zm10-10V7a4 4 0 0 0-8 0v4h8Z" />
                        </svg>
                        <div>
                            <h4 class="font-bold">コメントはログインすると読めます</h4>
                            <p class="text-sm">
                                <a href="{{ route('login') }}" class="link link-primary" wire:navigate>ログイン</a>
                                すると、コメントの閲覧と投稿ができます。
                            </p>
                        </div>
                    </div>
                @endauth
            </div>
            <form method="dialog" class="modal-backdrop">
                <button type="button" wire:click="close">close</button>
            </form>
        </dialog>
    @endif
</div>
