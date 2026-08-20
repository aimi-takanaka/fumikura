<section @if ($showAnchor ?? false) id="topics" @endif class="border-b border-base-300 py-8">
    @if ($showHeading ?? true)
        <h2 class="mb-3 text-lg font-bold text-base-content">{{ $heading ?? 'お題一覧' }}</h2>
    @endif

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
                    'btn btn-sm',
                    'btn-secondary' => $sort === $value,
                    'btn-outline' => $sort !== $value,
                ])
            >
                {{ $label }}
            </button>
        @endforeach
    </div>

    @if ($images->isEmpty())
        <div role="alert" class="alert">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="size-6 shrink-0 stroke-current">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <div>
                <h3 class="font-bold">まだお題がありません</h3>
                <div class="text-sm">管理者がお題画像を投稿すると、ここに一覧が表示されます。</div>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($images as $image)
                <a
                    href="{{ route('images.show', $image) }}"
                    wire:key="topic-{{ $image->id }}"
                    wire:navigate
                    class="card card-border bg-base-100 transition hover:shadow-md"
                >
                    <figure class="relative aspect-video overflow-hidden bg-base-200">
                        @if ($image->file_path && Illuminate\Support\Facades\Storage::disk('public')->exists($image->file_path))
                            <img
                                src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($image->file_path) }}"
                                alt="{{ $image->title }}"
                                class="size-full object-cover"
                            />
                        @else
                            <x-placeholder-pattern class="absolute inset-0 size-full stroke-base-content/20" />
                        @endif
                    </figure>
                    <div class="card-body px-3 py-3 text-base leading-relaxed">
                        <div class="truncate font-medium text-base-content">{{ $image->title }}</div>
                        <div class="mt-1 text-base-content/70">
                            <span class="font-bold text-base-content">完成{{ $image->completed_stories_count }}件</span>
                            ・アイデア{{ $image->idea_stories_count }}件
                            ・<x-story-likes-count :count="$image->likes_count" class="font-normal text-base-content/70" />
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-6">
            @include('partials.pagination', ['paginator' => $images])
        </div>
    @endif
</section>
