@props([
    'targets',
    'message' => '処理中です…',
])

<div
    wire:loading.flex
    wire:target="{{ $targets }}"
    class="fixed inset-0 z-[200] items-center justify-center bg-base-300/60"
    role="status"
    aria-live="polite"
    aria-busy="true"
>
    <div class="flex flex-col items-center gap-3 rounded-box border border-base-300 bg-base-100 px-8 py-6 shadow-lg">
        <x-loading-spinner size="lg" />
        <p class="text-sm text-base-content/70">{{ $message }}</p>
    </div>
</div>
