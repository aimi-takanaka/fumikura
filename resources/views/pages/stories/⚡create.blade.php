<?php

use App\Livewire\Concerns\EncouragesStorySubmission;
use App\Livewire\Concerns\ProofreadsStoryBody;
use App\Models\Image;
use App\Services\GeminiProofreadingService;
use App\StoryStatus;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.public')] #[Title('ストーリーを書く')] class extends Component
{
    use EncouragesStorySubmission;
    use ProofreadsStoryBody;
    public Image $image;

    public string $title = '';

    public string $body = '';

    public string $status = 'draft';

    public bool $skipBrowserDraftRestore = false;

    /**
     * Bind the image the story will belong to.
     */
    public function mount(Image $image): void
    {
        $this->image = $image;

        $draft = session()->pull('another_story_draft');

        if (! is_array($draft)) {
            return;
        }

        $this->title = (string) ($draft['title'] ?? '');
        $this->body = (string) ($draft['body'] ?? '');
        $this->status = (string) ($draft['status'] ?? StoryStatus::Idea->value);
        $this->skipBrowserDraftRestore = true;
    }

    /**
     * localStorage key for this topic's unsaved create form.
     */
    public function browserDraftKey(): string
    {
        return 'fumikura.story-create.'.$this->image->id.'.u'.auth()->id();
    }

    /**
     * Validate the input and store the new story.
     */
    public function save(GeminiProofreadingService $gemini): void
    {
        $validated = $this->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'status' => ['required', Rule::enum(StoryStatus::class)],
        ]);

        $this->image->stories()->create([
            ...$validated,
            'user_id' => auth()->id(),
        ]);

        $status = StoryStatus::from($validated['status']);

        $this->flashFumiEncouragement(
            $this->fetchFumiEncouragement(
                $gemini,
                $status,
                $this->image->title,
                $validated['body'],
            ),
            $status,
        );

        $this->js('localStorage.removeItem('.json_encode($this->browserDraftKey()).')');

        session()->flash('status', 'ストーリーを投稿しました。');

        $this->redirectRoute('images.show', $this->image, navigate: true);
    }
}; ?>

<div
    class="mx-auto max-w-2xl"
    x-data="storyComposerDraft({
        key: @js($this->browserDraftKey()),
        skipRestore: @js($skipBrowserDraftRestore),
    })"
>
    <a href="{{ route('images.show', $image) }}" class="btn btn-ghost btn-sm mb-4" wire:navigate>
        お題へ戻る
    </a>

    <div class="mb-6">
        <h1 class="text-2xl font-bold">ストーリーを書く</h1>
        <p class="mt-2 text-base-content/70">お題「{{ $image->title }}」からインスピレーションを受けた物語を綴りましょう。</p>
        <p class="mt-1 text-sm text-base-content/60">入力内容はこのブラウザに自動で一時保存されます（リロードしても復元できます）。</p>
    </div>

    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <form wire:submit="save" class="flex flex-col gap-6">
                <div class="fieldset">
                    <label class="label" for="title">
                        <span class="label-text">タイトル</span>
                    </label>
                    <input
                        id="title"
                        type="text"
                        wire:model.live.debounce.500ms="title"
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
                    <p class="mt-1 text-sm text-base-content/60">
                        フリガナの例: 漢字《かんじ》 ／ ｜言葉《ことば》
                    </p>
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
                    <a href="{{ route('images.show', $image) }}" class="btn btn-ghost" wire:navigate>キャンセル</a>
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">投稿する</span>
                        <span wire:loading wire:target="save" class="inline-flex items-center gap-2">
                            <x-loading-spinner size="sm" class="text-primary-content" />
                            投稿中...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @include('partials.action-loading-overlay', [
        'targets' => 'save,checkTypos,suggestAnotherStories',
        'message' => '処理中です…しばらくお待ちください',
    ])
</div>
