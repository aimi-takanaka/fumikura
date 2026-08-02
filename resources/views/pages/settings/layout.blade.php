<div class="flex items-start max-md:flex-col">
    <div class="me-10 flex w-full flex-col pb-4 md:w-[220px]">
        <flux:navlist aria-label="{{ __('Settings') }}" class="text-ai">
            <flux:navlist.item :href="route('profile.edit')" wire:navigate>{{ __('Profile') }}</flux:navlist.item>
            <flux:navlist.item :href="route('security.edit')" wire:navigate>{{ __('Security') }}</flux:navlist.item>
            <flux:navlist.item :href="route('appearance.edit')" wire:navigate>{{ __('Appearance') }}</flux:navlist.item>
        </flux:navlist>

        <flux:button
            :href="route('mypage')"
            variant="ghost"
            size="sm"
            icon="arrow-left"
            wire:navigate
            class="mt-6 self-start text-ai hover:text-shu"
            data-test="settings-back-to-mypage"
        >
            マイページに戻る
        </flux:button>
    </div>

    <flux:separator class="border-ai/15 md:hidden" />

    <div class="flex-1 self-stretch max-md:pt-6">
        <flux:heading class="text-sumi">{{ $heading ?? '' }}</flux:heading>
        <flux:subheading class="text-sumi/70">{{ $subheading ?? '' }}</flux:subheading>

        <div class="mt-5 w-full max-w-lg">
            {{ $slot }}
        </div>
    </div>
</div>
