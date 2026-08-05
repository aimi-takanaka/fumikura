<?php

use App\Livewire\Concerns\EncouragesStorySubmission;
use App\Livewire\Concerns\ProofreadsStoryBody;
use App\Models\Story;
use App\Services\GeminiProofreadingService;
use App\StoryStatus;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.public')] #[Title('ストーリーを編集')] class extends Component
{
    use EncouragesStorySubmission;
    use ProofreadsStoryBody;
    public Story $story;

    public string $title = '';

    public string $body = '';

    public string $status = 'draft';

    /**
     * Bind the story and pre-fill the form, allowing only the author.
     */
    public function mount(Story $story): void
    {
        abort_unless($story->user_id === auth()->id(), 403);

        $this->story = $story;
        $this->title = $story->title;
        $this->body = $story->body;
        $this->status = $story->status->value;
    }

    /**
     * Validate the input and update the story.
     */
    public function update(GeminiProofreadingService $gemini): void
    {
        abort_unless($this->story->user_id === auth()->id(), 403);

        $validated = $this->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'status' => ['required', Rule::enum(StoryStatus::class)],
        ]);

        $this->story->update($validated);

        $status = StoryStatus::from($validated['status']);

        $this->flashFumiEncouragement(
            $this->fetchFumiEncouragement(
                $gemini,
                $status,
                $this->story->image->title,
                $validated['body'],
            ),
            $status,
        );

        session()->flash('status', 'ストーリーを更新しました。');

        $this->redirectRoute('stories.show', $this->story, navigate: true);
    }
}; ?>

<div class="mx-auto max-w-2xl">
    <a href="{{ route('stories.show', $story) }}" class="btn btn-ghost btn-sm mb-4" wire:navigate>
        ストーリーへ戻る
    </a>

    <div class="mb-6">
        <h1 class="text-2xl font-bold">ストーリーを編集</h1>
        <p class="mt-2 text-base-content/70">お題「{{ $story->image->title }}」への投稿を編集します。</p>
    </div>

    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <form wire:submit="update" class="flex flex-col gap-6">
                <div class="fieldset">
                    <label class="label" for="title">
                        <span class="label-text">タイトル</span>
                    </label>
                    <input
                        id="title"
                        type="text"
                        wire:model="title"
                        placeholder="例: 灯台守の最後の夜"
                        class="input input-bordered w-full @error('title') input-error @enderror"
                        required
                    />
                    @error('title')
                        <p class="mt-1 text-sm text-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="fieldset">
                    <label class="label" for="body">
                        <span class="label-text">本文</span>
                    </label>
                    <textarea
                        id="body"
                        wire:model.live="body"
                        placeholder="物語をここに書いてください..."
                        rows="12"
                        class="textarea textarea-bordered w-full @error('body') textarea-error @enderror"
                        required
                    ></textarea>
                    <p class="text-end text-sm text-base-content/60">{{ mb_strlen($body) }} 文字</p>
                    @error('body')
                        <p class="mt-1 text-sm text-error">{{ $message }}</p>
                    @enderror

                    @include('partials.story-proofreading-panel')
                </div>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">ステータス</legend>
                    @foreach (\App\StoryStatus::cases() as $storyStatus)
                        <label class="label cursor-pointer justify-start gap-3 py-1">
                            <input
                                type="radio"
                                wire:model="status"
                                value="{{ $storyStatus->value }}"
                                class="radio radio-primary"
                            />
                            <span class="label-text">{{ $storyStatus->formLabel() }}</span>
                        </label>
                    @endforeach
                    @error('status')
                        <p class="mt-1 text-sm text-error">{{ $message }}</p>
                    @enderror
                </fieldset>

                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('stories.show', $story) }}" class="btn btn-ghost" wire:navigate>キャンセル</a>
                    <button type="submit" class="btn btn-primary">
                        <span wire:loading.remove wire:target="update">更新する</span>
                        <span wire:loading wire:target="update">更新中...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
