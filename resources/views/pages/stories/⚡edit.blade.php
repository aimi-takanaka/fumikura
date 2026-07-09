<?php

use App\Models\Story;
use App\StoryStatus;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.public')] #[Title('ストーリーを編集')] class extends Component
{
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
    public function update(): void
    {
        abort_unless($this->story->user_id === auth()->id(), 403);

        $validated = $this->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'status' => ['required', Rule::enum(StoryStatus::class)],
        ]);

        $this->story->update($validated);

        session()->flash('status', 'ストーリーを更新しました。');

        $this->redirectRoute('stories.show', $this->story, navigate: true);
    }
}; ?>

<div class="mx-auto max-w-2xl">
    <flux:button :href="route('stories.show', $story)" variant="ghost" size="sm" icon="arrow-left" wire:navigate class="mb-4">
        ストーリーへ戻る
    </flux:button>

    <div class="mb-6">
        <flux:heading size="xl">ストーリーを編集</flux:heading>
        <flux:text class="mt-2">お題「{{ $story->image->title }}」への投稿を編集します。</flux:text>
    </div>

    <flux:card>
        <form wire:submit="update" class="flex flex-col gap-6">
            <flux:input
                wire:model="title"
                label="タイトル"
                placeholder="例: 灯台守の最後の夜"
                required
            />

            <flux:textarea
                wire:model="body"
                label="本文"
                placeholder="物語をここに書いてください..."
                rows="12"
                required
            />

            <flux:radio.group wire:model="status" label="ステータス">
                <flux:radio value="complete" label="完成（誰でも読めます）" />
                <flux:radio value="draft" label="散文・アイデア（下書き / ログインユーザーのみ）" />
            </flux:radio.group>

            <div class="flex items-center justify-end gap-3">
                <flux:button :href="route('stories.show', $story)" variant="ghost" wire:navigate>キャンセル</flux:button>
                <flux:button type="submit" variant="primary">
                    <span wire:loading.remove wire:target="update">更新する</span>
                    <span wire:loading wire:target="update">更新中...</span>
                </flux:button>
            </div>
        </form>
    </flux:card>
</div>
