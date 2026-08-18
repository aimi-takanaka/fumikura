<?php

use App\ImageCreditType;
use App\Models\Image;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.public')] #[Title('お題画像の編集')] class extends Component
{
    public Image $image;

    public string $title = '';

    public ?string $creditType = null;

    public string $creditAuthor = '';

    public string $creditService = '';

    /**
     * Prefill the form from the existing image.
     */
    public function mount(Image $image): void
    {
        $this->image = $image;
        $this->title = $image->title;
        $this->creditType = $image->credit_type?->value;
        $this->creditAuthor = $image->credit_author ?? '';
        $this->creditService = $image->credit_service ?? '';
    }

    /**
     * Update title and credit attribution for an existing image.
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
        ]);

        $creditType = isset($validated['creditType'])
            ? ImageCreditType::from($validated['creditType'])
            : null;

        $this->image->update([
            'title' => $validated['title'],
            'credit_type' => $creditType,
            'credit_author' => $creditType ? $validated['creditAuthor'] : null,
            'credit_service' => $creditType ? $validated['creditService'] : null,
        ]);

        session()->flash('status', 'お題画像を更新しました。');

        $this->redirectRoute('images.show', $this->image, navigate: true);
    }
}; ?>

<div class="mx-auto max-w-xl">
    <div class="mb-6">
        <h1 class="text-2xl font-bold">お題画像の編集</h1>
        <p class="mt-2 text-base-content/70">タイトルやクレジット（作者・サービス）を編集できます。</p>
    </div>

    <div class="card card-border bg-base-100">
        <form wire:submit="save" class="card-body flex flex-col gap-6">
            @if ($image->url())
                <div class="overflow-hidden rounded-box border border-base-300">
                    <img src="{{ $image->url() }}" alt="{{ $image->title }}" class="max-h-64 w-full object-contain" />
                </div>
            @endif

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

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('images.show', $image) }}" class="btn btn-ghost" wire:navigate>キャンセル</a>
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">保存する</span>
                    <span wire:loading wire:target="save" class="inline-flex items-center gap-2">
                        <x-loading-spinner size="sm" class="text-primary-content" />
                        保存中...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>
