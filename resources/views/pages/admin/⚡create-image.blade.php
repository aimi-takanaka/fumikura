<?php

use App\Models\Image;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.public')] #[Title('お題画像の投稿')] class extends Component
{
    use WithFileUploads;

    #[Validate('required|string|max:255')]
    public string $title = '';

    #[Validate('required|image|max:5120')]
    public $photo = null;

    /**
     * Store the uploaded image and create the record.
     */
    public function save(): void
    {
        $this->validate();

        $path = $this->photo->store('images', 'public');

        Image::create([
            'title' => $this->title,
            'file_path' => $path,
            'user_id' => auth()->id(),
        ]);

        session()->flash('status', 'お題画像を投稿しました。');

        $this->redirectRoute('topics.index', navigate: true);
    }
}; ?>

<div class="mx-auto max-w-xl">
    <div class="mb-6">
        <flux:heading size="xl">お題画像の投稿</flux:heading>
        <flux:text class="mt-2">ユーザーが創作するためのお題画像をアップロードします。</flux:text>
    </div>

    <flux:card>
        <form wire:submit="save" class="flex flex-col gap-6">
            <flux:input
                wire:model="title"
                label="タイトル"
                placeholder="例: 夕暮れの海辺"
                required
            />

            <flux:field>
                <flux:label>お題画像</flux:label>

                <input
                    type="file"
                    wire:model="photo"
                    accept="image/*"
                    class="block w-full text-sm text-zinc-600 file:mr-4 file:rounded-md file:border-0 file:bg-zinc-100 file:px-4 file:py-2 file:text-sm file:font-medium hover:file:bg-zinc-200 dark:text-zinc-300 dark:file:bg-zinc-700 dark:hover:file:bg-zinc-600"
                />

                <flux:description>JPEG / PNG などの画像、最大5MBまで。</flux:description>
                <flux:error name="photo" />
            </flux:field>

            <div wire:loading wire:target="photo" class="text-sm text-zinc-500">
                アップロード中...
            </div>

            @if ($photo && $photo->isPreviewable())
                <div class="overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <img src="{{ $photo->temporaryUrl() }}" alt="プレビュー" class="max-h-64 w-full object-contain" />
                </div>
            @endif

            <div class="flex items-center justify-end gap-3">
                <flux:button :href="route('topics.index')" variant="ghost" wire:navigate>キャンセル</flux:button>
                <flux:button type="submit" variant="primary">
                    <span wire:loading.remove wire:target="save">投稿する</span>
                    <span wire:loading wire:target="save">投稿中...</span>
                </flux:button>
            </div>
        </form>
    </flux:card>
</div>
