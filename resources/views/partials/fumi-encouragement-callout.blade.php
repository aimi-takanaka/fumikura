@if (session('fumi_message'))
    @php
        $fumiStatus = \App\StoryStatus::tryFrom((string) session('fumi_status', ''));
        $fumiCommentImage = $fumiStatus?->fumiCommentImage();
    @endphp

    <div
        x-data="{ show: true }"
        x-show="show"
        x-transition
        class="mb-6 flex items-end gap-3 sm:gap-4"
    >
        <div class="relative min-w-0 flex-1">
            <div class="fumi-cloud-bubble border border-lime-300/80 bg-lime-50 px-5 py-4 shadow-[0_8px_24px_-12px_rgba(101,163,13,0.45)] sm:px-6 sm:py-5 dark:border-lime-700/60 dark:bg-lime-950/40 dark:shadow-[0_8px_24px_-12px_rgba(54,83,20,0.55)]">
                <div class="relative z-10 flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-lime-900 dark:text-lime-100">ふみより</p>
                        <p class="mt-1 whitespace-pre-line text-sm leading-relaxed text-lime-950/85 dark:text-lime-50/90">
                            {{ session('fumi_message') }}
                        </p>
                    </div>

                    <button
                        type="button"
                        class="shrink-0 rounded-full p-1 text-lime-800/60 transition hover:bg-lime-100 hover:text-lime-900 dark:text-lime-200/70 dark:hover:bg-lime-900/50 dark:hover:text-lime-50"
                        @click="show = false"
                        aria-label="閉じる"
                    >
                        <flux:icon.x-mark variant="micro" />
                    </button>
                </div>
            </div>

            @if ($fumiCommentImage)
                <span
                    class="fumi-cloud-tail absolute -right-2 bottom-6 size-4 border border-lime-300/80 bg-lime-50 dark:border-lime-700/60 dark:bg-lime-950/40"
                    aria-hidden="true"
                ></span>
                <span
                    class="fumi-cloud-tail absolute -right-3.5 bottom-4 size-2.5 border border-lime-300/80 bg-lime-50 dark:border-lime-700/60 dark:bg-lime-950/40"
                    aria-hidden="true"
                    style="animation-delay: 0.35s"
                ></span>
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
