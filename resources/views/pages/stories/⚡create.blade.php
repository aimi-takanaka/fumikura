<?php

use App\Models\Image;
use App\StoryStatus;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.public')] #[Title('ストーリーを書く')] class extends Component
{
    public Image $image;

    public string $title = '';

    public string $body = '';

    public string $status = 'draft';

    /**
     * Bind the image the story will belong to.
     */
    public function mount(Image $image): void
    {
        $this->image = $image;
    }

    /**
     * Validate the input and store the new story.
     */
    public function save(): void
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

        session()->flash('status', 'ストーリーを投稿しました。');

        $this->redirectRoute('images.show', $this->image, navigate: true);
    }
}; ?>

<div class="mx-auto max-w-2xl">
    <flux:button :href="route('images.show', $image)" variant="ghost" size="sm" icon="arrow-left" wire:navigate class="mb-4">
        お題へ戻る
    </flux:button>

    <div class="mb-6">
        <flux:heading size="xl">ストーリーを書く</flux:heading>
        <flux:text class="mt-2">お題「{{ $image->title }}」からインスピレーションを受けた物語を綴りましょう。</flux:text>
    </div>

    <flux:card>
        <form wire:submit="save" class="flex flex-col gap-6">
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
                @foreach (\App\StoryStatus::cases() as $storyStatus)
                    <flux:radio value="{{ $storyStatus->value }}" :label="$storyStatus->formLabel()" />
                @endforeach
            </flux:radio.group>

            <div class="flex items-center justify-end gap-3">
                <flux:button :href="route('images.show', $image)" variant="ghost" wire:navigate>キャンセル</flux:button>
                <flux:button type="submit" variant="primary">
                    <span wire:loading.remove wire:target="save">投稿する</span>
                    <span wire:loading wire:target="save">投稿中...</span>
                </flux:button>
            </div>
        </form>
    </flux:card>
</div>
