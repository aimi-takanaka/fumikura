<section @if ($showAnchor ?? false) id="topics" @endif class="border-b border-ai/15 py-8">
    <h2 class="mb-3 text-lg font-bold text-sumi">{{ $heading ?? 'お題一覧' }}</h2>

    <div class="mb-4 flex flex-wrap gap-2">
        @foreach ([
            'latest' => '新着',
            'popular' => '人気',
            'most_completed' => '完成ストーリーが多い順',
        ] as $value => $label)
            <button
                type="button"
                wire:click="$set('sort', '{{ $value }}')"
                @class([
                    'rounded-full border px-3 py-1 text-sm transition',
                    'border-shu bg-shu text-washi-light' => $sort === $value,
                    'border-ai/30 text-ai hover:border-shu/40 hover:text-shu' => $sort !== $value,
                ])
            >
                {{ $label }}
            </button>
        @endforeach
    </div>

    @if ($images->isEmpty())
        <flux:callout icon="photo">
            <flux:callout.heading>まだお題がありません</flux:callout.heading>
            <flux:callout.text>管理者がお題画像を投稿すると、ここに一覧が表示されます。</flux:callout.text>
        </flux:callout>
    @else
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($images as $image)
                <a
                    href="{{ route('images.show', $image) }}"
                    wire:key="topic-{{ $image->id }}"
                    wire:navigate
                    class="block overflow-hidden rounded-lg border border-ai/15 bg-washi-light transition hover:border-ai/40"
                >
                    <div class="relative aspect-video overflow-hidden bg-washi">
                        @if ($image->file_path && Illuminate\Support\Facades\Storage::disk('public')->exists($image->file_path))
                            <img
                                src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($image->file_path) }}"
                                alt="{{ $image->title }}"
                                class="size-full object-cover"
                            />
                        @else
                            <x-placeholder-pattern class="absolute inset-0 size-full stroke-ai/20" />
                        @endif
                    </div>
                    <div class="px-3 py-3 text-base leading-relaxed text-ai">
                        <div class="truncate font-medium text-sumi">{{ $image->title }}</div>
                        <div class="mt-1">
                            <span class="font-bold text-sumi">完成{{ $image->completed_stories_count }}件</span>
                            ・大喜利{{ $image->draft_stories_count }}件
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        @if ($images->hasPages())
            <flux:pagination :paginator="$images" class="mt-6" />
        @endif
    @endif
</section>
