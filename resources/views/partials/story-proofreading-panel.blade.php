<div>
    <div class="flex flex-wrap gap-4 sm:gap-6">
        <button
            type="button"
            wire:click="checkTypos"
            wire:loading.attr="disabled"
            wire:target="checkTypos"
            class="flex items-center gap-3 rounded-xl p-1 text-left transition hover:bg-base-200 disabled:cursor-default disabled:opacity-75"
        >
            <img
                src="{{ asset('img/welcome/check-1.PNG') }}"
                alt=""
                class="h-24 w-auto shrink-0 object-contain sm:h-32"
            />
            <span class="text-sm font-medium">
                <span wire:loading.remove wire:target="checkTypos">誤字チェック</span>
                <span wire:loading wire:target="checkTypos">チェック中...</span>
            </span>
        </button>

        <button
            type="button"
            wire:click="suggestAnotherStories"
            wire:loading.attr="disabled"
            wire:target="suggestAnotherStories"
            class="flex items-center gap-3 rounded-xl p-1 text-left transition hover:bg-base-200 disabled:cursor-default disabled:opacity-75"
        >
            <img
                src="{{ asset('img/welcome/check-2.PNG') }}"
                alt=""
                class="h-24 w-auto shrink-0 object-contain sm:h-32"
            />
            <span class="text-sm font-medium">
                <span wire:loading.remove wire:target="suggestAnotherStories">アナザーストーリーの提案</span>
                <span wire:loading wire:target="suggestAnotherStories">提案中...</span>
            </span>
        </button>
    </div>

    @if ($proofreadingResult)
        <div role="alert" class="alert mt-3">
            <div class="flex w-full items-start gap-4">
                <img
                    src="{{ asset('img/welcome/check-1.PNG') }}"
                    alt="ふみ"
                    class="h-24 w-auto shrink-0 object-contain sm:h-32"
                />
                <div class="min-w-0 flex-1">
                    <h3 class="font-bold">誤字チェック結果</h3>
                    <p class="mt-1 whitespace-pre-line text-sm">{{ $proofreadingResult }}</p>

                    <div class="mt-3">
                        <button
                            type="button"
                            wire:click="dismissProofreadingResult"
                            class="btn btn-ghost btn-sm"
                        >
                            閉じる
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if (count($anotherStorySuggestions) > 0)
        <div role="alert" class="alert alert-success mt-3">
            <div class="flex w-full items-start gap-4">
                <img
                    src="{{ asset('img/welcome/check-2.PNG') }}"
                    alt="ふみ"
                    class="h-24 w-auto shrink-0 object-contain sm:h-32"
                />
                <div class="min-w-0 flex-1">
                    <h3 class="font-bold">ふみの提案</h3>
                    <div class="mt-2">
                        <ul class="space-y-5">
                            @foreach ($anotherStorySuggestions as $index => $suggestion)
                                <li class="space-y-2">
                                    <p class="font-medium">
                                        「{{ $suggestion['viewpoint'] }}」視点はいかがでしょうか？
                                    </p>
                                    <p class="text-sm">{{ $suggestion['hook'] }}</p>
                                    <p class="text-sm opacity-80">{{ $suggestion['comment'] }}</p>
                                    <button
                                        type="button"
                                        wire:click="startAnotherStoryDraft({{ $index }})"
                                        class="btn btn-primary btn-sm"
                                        wire:loading.attr="disabled"
                                        wire:target="startAnotherStoryDraft"
                                    >
                                        この視点で書いてみる
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="mt-3">
                        <button
                            type="button"
                            wire:click="dismissAnotherStorySuggestions"
                            class="btn btn-ghost btn-sm"
                        >
                            閉じる
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
