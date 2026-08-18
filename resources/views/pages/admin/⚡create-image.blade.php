<?php

use App\ImageCreditType;
use App\Models\Image;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.public')] #[Title('お題画像の投稿')] class extends Component
{
    use WithFileUploads;

    public string $title = '';

    public ?string $creditType = null;

    public string $creditAuthor = '';

    public string $creditService = '';

    public $photo = null;

    /**
     * Store the uploaded image and create the record.
     */
    public function save(): void
    {
        if ($this->creditType === '') {
            $this->creditType = null;
        }

        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'creditType' => ['nullable', Rule::enum(ImageCreditType::class)],
            'creditAuthor' => ['required_with:creditType', 'nullable', 'string', 'max:255'],
            'creditService' => ['required_with:creditType', 'nullable', 'string', 'max:255'],
            'photo' => ['required', 'image', 'max:5120'],
        ]);

        $path = $this->photo->store('images', 'public');

        $creditType = isset($validated['creditType'])
            ? ImageCreditType::from($validated['creditType'])
            : null;

        Image::create([
            'title' => $validated['title'],
            'credit_type' => $creditType,
            'credit_author' => $creditType ? $validated['creditAuthor'] : null,
            'credit_service' => $creditType ? $validated['creditService'] : null,
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
                    <span class="label-text">クレジット種別</span>
                </span>
                <select
                    wire:model.live="creditType"
                    class="select select-bordered w-full @error('creditType') select-error @enderror"
                >
                    <option value="">未選択（表記なし）</option>
                    @foreach (ImageCreditType::cases() as $type)
                        <option value="{{ $type->value }}">{{ $type->label() }}</option>
                    @endforeach
                </select>
                @error('creditType')
                    <span class="label-text-alt text-error">{{ $message }}</span>
                @enderror
            </label>

            @if (filled($creditType))
                <label class="form-control w-full">
                    <span class="label">
                        <span class="label-text">作者名</span>
                    </span>
                    <input
                        type="text"
                        wire:model="creditAuthor"
                        placeholder="例: Yamada Taro"
                        class="input input-bordered w-full @error('creditAuthor') input-error @enderror"
                    />
                    @error('creditAuthor')
                        <span class="label-text-alt text-error">{{ $message }}</span>
                    @enderror
                </label>

                <label class="form-control w-full">
                    <span class="label">
                        <span class="label-text">サービス名</span>
                    </span>
                    <input
                        type="text"
                        wire:model="creditService"
                        placeholder="例: Unsplash"
                        class="input input-bordered w-full @error('creditService') input-error @enderror"
                    />
                    @error('creditService')
                        <span class="label-text-alt text-error">{{ $message }}</span>
                    @enderror
                </label>
            @endif

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
