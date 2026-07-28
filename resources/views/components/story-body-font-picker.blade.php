<div x-data="storyBodyFont()" class="inline-flex">
    <flux:dropdown position="bottom" align="end">
        <flux:button variant="ghost" size="sm" icon="language" data-test="story-body-font-picker">
            フォント変更
        </flux:button>

        <flux:menu>
            <flux:menu.item
                as="button"
                type="button"
                class="w-full cursor-pointer"
                x-bind:class="font === 'noto' ? 'font-medium' : ''"
                x-on:click="setFont('noto')"
            >
                Noto Sans JP（標準）
            </flux:menu.item>
            <flux:menu.item
                as="button"
                type="button"
                class="w-full cursor-pointer"
                x-bind:class="font === 'zen-maru' ? 'font-medium' : ''"
                x-on:click="setFont('zen-maru')"
            >
                Zen Maru Gothic（丸ゴシック）
            </flux:menu.item>
            <flux:menu.item
                as="button"
                type="button"
                class="w-full cursor-pointer"
                x-bind:class="font === 'kaisei-tokumin' ? 'font-medium' : ''"
                x-on:click="setFont('kaisei-tokumin')"
            >
                Kaisei Tokumin（明朝）
            </flux:menu.item>
        </flux:menu>
    </flux:dropdown>
</div>
