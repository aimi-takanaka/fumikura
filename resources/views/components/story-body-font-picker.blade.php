@props([
    'layout' => 'dropdown',
])

@php
    $fonts = [
        'noto' => [
            'label' => 'Noto Sans JP（標準）',
            'sample' => 'あいうえお ABC 物語を書く',
            'style' => "font-family: 'Noto Sans JP', sans-serif",
        ],
        'zen-maru' => [
            'label' => 'Zen Maru Gothic（丸ゴシック）',
            'sample' => 'あいうえお ABC 物語を書く',
            'style' => "font-family: 'Zen Maru Gothic', sans-serif",
        ],
        'kaisei-tokumin' => [
            'label' => 'Kaisei Tokumin（明朝）',
            'sample' => 'あいうえお ABC 物語を書く',
            'style' => "font-family: 'Kaisei Tokumin', serif",
        ],
    ];
@endphp

<div {{ $attributes->class('inline-flex') }} x-data="storyBodyFont()">
    @if ($layout === 'list')
        <div class="flex w-full flex-col gap-2" role="listbox" aria-label="表示フォント">
            @foreach ($fonts as $value => $font)
                <button
                    type="button"
                    role="option"
                    class="w-full rounded-xl border px-4 py-3 text-left transition"
                    x-bind:class="font === '{{ $value }}'
                        ? 'border-shu bg-shu/5 ring-1 ring-shu/30'
                        : 'border-ai/15 hover:border-ai/30 hover:bg-washi-light/80'"
                    x-bind:aria-selected="font === '{{ $value }}'"
                    x-on:click="setFont('{{ $value }}')"
                    style="{{ $font['style'] }}"
                    data-test="site-font-option-{{ $value }}"
                >
                    <span class="block text-sm font-medium text-sumi">{{ $font['label'] }}</span>
                    <span class="mt-1 block text-sm text-ai/80">{{ $font['sample'] }}</span>
                </button>
            @endforeach
        </div>
    @else
        <flux:dropdown position="bottom" align="end">
            <flux:button variant="ghost" size="sm" icon="language" data-test="story-body-font-picker">
                フォント変更
            </flux:button>

            <flux:menu>
                @foreach ($fonts as $value => $font)
                    <flux:menu.item
                        as="button"
                        type="button"
                        class="w-full cursor-pointer"
                        x-bind:class="font === '{{ $value }}' ? 'font-medium' : ''"
                        x-on:click="setFont('{{ $value }}')"
                        style="{{ $font['style'] }}"
                    >
                        {{ $font['label'] }}
                    </flux:menu.item>
                @endforeach
            </flux:menu>
        </flux:dropdown>
    @endif
</div>
