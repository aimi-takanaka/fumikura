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
                    class="btn btn-outline h-auto min-h-0 w-full justify-start py-3 font-normal"
                    x-bind:class="font === '{{ $value }}' ? 'btn-primary' : ''"
                    x-bind:aria-selected="font === '{{ $value }}'"
                    x-on:click="setFont('{{ $value }}')"
                    style="{{ $font['style'] }}"
                    data-test="site-font-option-{{ $value }}"
                >
                    <span class="block text-left">
                        <span class="block text-sm font-medium">{{ $font['label'] }}</span>
                        <span class="mt-1 block text-sm opacity-70">{{ $font['sample'] }}</span>
                    </span>
                </button>
            @endforeach
        </div>
    @else
        <div class="dropdown dropdown-end">
            <div tabindex="0" role="button" class="btn btn-ghost btn-sm" data-test="story-body-font-picker">
                フォント変更
            </div>

            <ul tabindex="0" class="menu dropdown-content z-50 mt-2 w-64 rounded-box border border-base-300 bg-base-100 p-2 shadow-lg">
                @foreach ($fonts as $value => $font)
                    <li>
                        <button
                            type="button"
                            class="w-full cursor-pointer"
                            x-bind:class="font === '{{ $value }}' ? 'font-medium' : ''"
                            x-on:click="setFont('{{ $value }}')"
                            style="{{ $font['style'] }}"
                        >
                            {{ $font['label'] }}
                        </button>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
