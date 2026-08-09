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
        <h1 class="text-2xl font-bold">お題画像の投稿</h1>
        <p class="mt-2 text-base-content/70">ユーザーが創作するためのお題画像をアップロードします。</p>
    </div>

    <div class="card card-border bg-base-100">
        <form wire:submit="save" class="card-body flex flex-col gap-6">
            <label class="form-control w-full">
                <span class="label">
                    <span class="label-text">タイトル</span>
                </span>
                <input
                    type="text"
                    wire:model="title"
                    placeholder="例: 夕暮れの海辺"
                    class="input input-bordered w-full @error('title') input-error @enderror"
                    required
                />
                @error('title')
                    <span class="label-text-alt text-error">{{ $message }}</span>
                @enderror
            </label>

            <label class="form-control w-full">
                <span class="label">
                    <span class="label-text">お題画像</span>
                </span>
                <input
                    type="file"
                    wire:model="photo"
                    accept="image/*"
                    class="file-input file-input-bordered w-full @error('photo') file-input-error @enderror"
                />
                <span class="label-text-alt mt-1">JPEG / PNG などの画像、最大5MBまで。</span>
                @error('photo')
                    <span class="label-text-alt text-error">{{ $message }}</span>
                @enderror
            </label>

            <div wire:loading wire:target="photo" class="text-sm text-base-content/60">
                アップロード中...
            </div>

            @if ($photo && $photo->isPreviewable())
                <div class="overflow-hidden rounded-box border border-base-300">
                    <img src="{{ $photo->temporaryUrl() }}" alt="プレビュー" class="max-h-64 w-full object-contain" />
                </div>
            @endif

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('topics.index') }}" class="btn btn-ghost" wire:navigate>キャンセル</a>
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
