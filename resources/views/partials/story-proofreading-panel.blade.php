<div>
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
</div>
