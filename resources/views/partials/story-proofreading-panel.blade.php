<div>
    <div class="flex flex-wrap gap-4 sm:gap-6">
        <button
            type="button"
            wire:click="checkTypos"
            wire:loading.attr="disabled"
            wire:target="checkTypos"
            class="flex items-center gap-3 rounded-xl p-1 text-left transition hover:bg-zinc-800/5 disabled:cursor-default disabled:opacity-75 dark:hover:bg-white/10"
        >
            <img
                src="{{ asset('img/welcome/check-1.PNG') }}"
                alt=""
                class="h-24 w-auto shrink-0 object-contain sm:h-32"
            />
            <span class="text-sm font-medium text-zinc-800 dark:text-white">
                <span wire:loading.remove wire:target="checkTypos">誤字チェック</span>
                <span wire:loading wire:target="checkTypos">チェック中...</span>
            </span>
        </button>

        <button
            type="button"
            wire:click="suggestAnotherStories"
            wire:loading.attr="disabled"
            wire:target="suggestAnotherStories"
            class="flex items-center gap-3 rounded-xl p-1 text-left transition hover:bg-zinc-800/5 disabled:cursor-default disabled:opacity-75 dark:hover:bg-white/10"
        >
            <img
                src="{{ asset('img/welcome/check-2.PNG') }}"
                alt=""
                class="h-24 w-auto shrink-0 object-contain sm:h-32"
            />
            <span class="text-sm font-medium text-zinc-800 dark:text-white">
                <span wire:loading.remove wire:target="suggestAnotherStories">アナザーストーリーの提案</span>
                <span wire:loading wire:target="suggestAnotherStories">提案中...</span>
            </span>
        </button>
    </div>

    @if ($proofreadingResult)
        <flux:callout class="mt-3">
            <div class="flex items-start gap-4">
                <img
                    src="{{ asset('img/welcome/check-1.PNG') }}"
                    alt="ふみ"
                    class="h-24 w-auto shrink-0 object-contain sm:h-32"
                />
                <div class="min-w-0 flex-1">
                    <flux:callout.heading>誤字チェック結果</flux:callout.heading>
                    <flux:callout.text class="whitespace-pre-line">{{ $proofreadingResult }}</flux:callout.text>

                    <div class="mt-3">
                        <flux:button
                            type="button"
                            wire:click="dismissProofreadingResult"
                            variant="ghost"
                            size="sm"
                        >
                            閉じる
                        </flux:button>
                    </div>
                </div>
            </div>
        </flux:callout>
    @endif

    @if (count($anotherStorySuggestions) > 0)
        <flux:callout color="lime" class="mt-3">
            <div class="flex items-start gap-4">
                <img
                    src="{{ asset('img/welcome/check-2.PNG') }}"
                    alt="ふみ"
                    class="h-24 w-auto shrink-0 object-contain sm:h-32"
                />
                <div class="min-w-0 flex-1">
                    <flux:callout.heading>ふみの提案</flux:callout.heading>
                    <flux:callout.text>
                        <ul class="mt-2 space-y-5">
                            @foreach ($anotherStorySuggestions as $index => $suggestion)
                                <li class="space-y-2">
                                    <p class="font-medium text-zinc-800 dark:text-zinc-100">
                                        「{{ $suggestion['viewpoint'] }}」視点はいかがでしょうか？
                                    </p>
                                    <p class="text-zinc-700 dark:text-zinc-200">{{ $suggestion['hook'] }}</p>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ $suggestion['comment'] }}</p>
                                    <flux:button
                                        type="button"
                                        wire:click="startAnotherStoryDraft({{ $index }})"
                                        variant="primary"
                                        size="sm"
                                        icon="pencil-square"
                                        wire:loading.attr="disabled"
                                        wire:target="startAnotherStoryDraft"
                                    >
                                        この視点で書いてみる
                                    </flux:button>
                                </li>
                            @endforeach
                        </ul>
                    </flux:callout.text>

                    <div class="mt-3">
                        <flux:button
                            type="button"
                            wire:click="dismissAnotherStorySuggestions"
                            variant="ghost"
                            size="sm"
                        >
                            閉じる
                        </flux:button>
                    </div>
                </div>
            </div>
        </flux:callout>
    @endif
</div>
