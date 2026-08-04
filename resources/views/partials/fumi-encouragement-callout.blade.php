@if (session('fumi_message'))
    @php
        $fumiStatus = \App\StoryStatus::tryFrom((string) session('fumi_status', ''));
        $fumiCommentImage = $fumiStatus?->fumiCommentImage();
    @endphp

    <div
        x-data="{ show: true }"
        x-show="show"
        x-transition
        class="mb-6 flex items-center gap-3 sm:gap-4"
    >
        <div class="fumi-speech min-w-0 flex-1">
            <div class="fumi-speech-bubble">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 px-1">
                        <p class="text-sm font-semibold text-lime-900 dark:text-lime-100">ふみより</p>
                        <p class="mt-1 whitespace-pre-line text-sm leading-relaxed text-lime-950/85 dark:text-lime-50/90">
                            {{ session('fumi_message') }}
                        </p>
                    </div>

                    <button
                        type="button"
                        class="shrink-0 rounded-full p-1 text-lime-800/60 transition hover:bg-lime-100/80 hover:text-lime-900 dark:text-lime-200/70 dark:hover:bg-lime-900/40 dark:hover:text-lime-50"
                        @click="show = false"
                        aria-label="閉じる"
                    >
                        <flux:icon.x-mark variant="micro" />
                    </button>
                </div>
            </div>

            @if ($fumiCommentImage)
                <span class="fumi-speech-tail" aria-hidden="true"></span>
            @endif
        </div>

        @if ($fumiCommentImage)
            <img
                src="{{ asset($fumiCommentImage) }}"
                alt="ふみ"
                class="mb-0.5 h-24 w-auto shrink-0 object-contain sm:h-32"
            />
        @endif
    </div>
@endif
