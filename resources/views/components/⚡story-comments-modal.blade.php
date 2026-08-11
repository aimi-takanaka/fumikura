<?php

use App\Models\Comment;
use App\Models\Story;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component
{
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

        Flux::toast('コメントを投稿しました。', variant: 'success');
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

        Flux::toast('コメントを削除しました。', variant: 'success');
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
    <flux:modal wire:model="showModal" @close="close" class="max-w-lg">
        @if ($story)
            <div class="flex max-h-[min(32rem,calc(100vh-8rem))] flex-col">
                <div class="shrink-0">
                    <flux:heading size="lg">コメント（{{ $comments->count() }}）</flux:heading>
                    <flux:text class="mt-1 truncate">{{ $story->title }}</flux:text>
                </div>

                @auth
                    <div class="mt-4 min-h-0 flex-1 overflow-y-auto">
                        <div class="flex flex-col gap-4">
                            @forelse ($comments as $comment)
                                <flux:card wire:key="modal-comment-{{ $comment->id }}" class="p-4">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                                                <x-user-avatar :user="$comment->user" />
                                                <flux:text class="font-medium">{{ $comment->user?->name ?? '不明' }}</flux:text>
                                                <flux:text size="sm">{{ $comment->created_at->diffForHumans() }}</flux:text>
                                            </div>
                                            <flux:text class="mt-2 whitespace-pre-line">{{ $comment->body }}</flux:text>
                                        </div>

                                        @if ($comment->user_id === auth()->id())
                                            <flux:button
                                                wire:click="deleteComment({{ $comment->id }})"
                                                wire:confirm="このコメントを削除しますか？"
                                                variant="ghost"
                                                size="sm"
                                                icon="trash"
                                                inset
                                            />
                                        @endif
                                    </div>
                                </flux:card>
                            @empty
                                <flux:text size="sm">まだコメントはありません。最初のコメントを書いてみましょう。</flux:text>
                            @endforelse
                        </div>
                    </div>

                    <form wire:submit="addComment" class="mt-4 shrink-0 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                        <flux:textarea
                            wire:model="body"
                            placeholder="このストーリーへの感想を書いてみましょう"
                            rows="2"
                        />
                        <div class="mt-2 flex justify-end">
                            <flux:button type="submit" variant="primary" size="sm" icon="paper-airplane">
                                コメントする
                            </flux:button>
                        </div>
                    </form>
                @else
                    <flux:callout icon="lock-closed" class="mt-4">
                        <flux:callout.heading>コメントはログインすると読めます</flux:callout.heading>
                        <flux:callout.text>
                            <flux:link :href="route('login')" wire:navigate>ログイン</flux:link>
                            すると、コメントの閲覧と投稿ができます。
                        </flux:callout.text>
                    </flux:callout>
                @endauth
            </div>
        @endif
    </flux:modal>
</div>
