<div>
    <div class="flex flex-wrap gap-2">
        <flux:button
            type="button"
            wire:click="checkTypos"
            variant="ghost"
            size="sm"
            icon="magnifying-glass"
            wire:loading.attr="disabled"
            wire:target="checkTypos"
        >
            <span wire:loading.remove wire:target="checkTypos">誤字チェック</span>
            <span wire:loading wire:target="checkTypos">チェック中...</span>
        </flux:button>

        <flux:button
            type="button"
            wire:click="suggestAnotherStories"
            variant="ghost"
            size="sm"
            icon="sparkles"
            wire:loading.attr="disabled"
            wire:target="suggestAnotherStories"
        >
            <span wire:loading.remove wire:target="suggestAnotherStories">アナザーストーリーの提案</span>
            <span wire:loading wire:target="suggestAnotherStories">提案中...</span>
        </flux:button>
    </div>

    @if ($proofreadingResult)
        <flux:callout class="mt-3">
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
        </flux:callout>
    @endif

    @if (count($anotherStorySuggestions) > 0)
        <flux:callout color="lime" class="mt-3">
            <flux:callout.heading>アナザーストーリーの提案</flux:callout.heading>
            <flux:callout.text>
                <ul class="mt-2 space-y-4">
                    @foreach ($anotherStorySuggestions as $suggestion)
                        <li class="space-y-1">
                            <p class="font-medium text-zinc-800 dark:text-zinc-100">視点：{{ $suggestion['viewpoint'] }}</p>
                            <p class="text-zinc-700 dark:text-zinc-200">「{{ $suggestion['hook'] }}」</p>
                            <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ $suggestion['comment'] }}</p>
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
        </flux:callout>
    @endif
</div>
