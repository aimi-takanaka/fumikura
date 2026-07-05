<?php

use App\Models\Image;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.public')] #[Title('お題一覧')] class extends Component
{
    /**
     * Data passed to the view.
     *
     * @return array<string, mixed>
     */
    public function with(): array
    {
        return [
            'images' => Image::query()
                ->with('user')
                ->withCount([
                    'stories as completed_stories_count' => fn (Builder $query) => $query->complete(),
                ])
                ->latest()
                ->get(),
        ];
    }
}; ?>

<div>
    <div class="mb-8">
        <flux:heading size="xl">お題一覧</flux:heading>
        <flux:text class="mt-2">一枚の画像から、いくつもの物語が生まれます。気になるお題を選んでください。</flux:text>
    </div>

    @if ($images->isEmpty())
        <flux:callout icon="photo">
            <flux:callout.heading>まだお題がありません</flux:callout.heading>
            <flux:callout.text>管理者がお題画像を投稿すると、ここに一覧が表示されます。</flux:callout.text>
        </flux:callout>
    @else
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($images as $image)
                <flux:card
                    wire:key="image-{{ $image->id }}"
                    class="overflow-hidden p-0"
                >
                    <div class="relative aspect-video overflow-hidden bg-zinc-100 dark:bg-zinc-800">
                        @if ($image->file_path && Illuminate\Support\Facades\Storage::disk('public')->exists($image->file_path))
                            <img
                                src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($image->file_path) }}"
                                alt="{{ $image->title }}"
                                class="size-full object-cover"
                            />
                        @else
                            <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
                        @endif
                    </div>

                    <div class="p-4">
                        <flux:heading size="lg" class="truncate">{{ $image->title }}</flux:heading>

                        <div class="mt-2 flex items-center justify-between">
                            <flux:text size="sm" class="truncate">
                                投稿者: {{ $image->user?->name ?? '不明' }}
                            </flux:text>
                            <flux:badge size="sm" color="lime">
                                完成 {{ $image->completed_stories_count }} 編
                            </flux:badge>
                        </div>
                    </div>
                </flux:card>
            @endforeach
        </div>
    @endif
</div>
