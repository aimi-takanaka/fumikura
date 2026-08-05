@php
    $navItems = [
        ['route' => 'profile.edit', 'label' => __('Profile')],
        ['route' => 'security.edit', 'label' => __('Security')],
        ['route' => 'appearance.edit', 'label' => __('Appearance')],
    ];
@endphp

<div class="flex items-stretch max-md:flex-col">
    <div class="flex w-full flex-col pb-4 md:me-10 md:w-56 md:shrink-0 md:border-e md:border-base-300 md:pe-10">
        <ul class="menu rounded-box bg-base-100 p-0" aria-label="{{ __('Settings') }}">
            @foreach ($navItems as $item)
                <li>
                    <a
                        href="{{ route($item['route']) }}"
                        wire:navigate
                        @class(['active' => request()->routeIs($item['route'])])
                    >
                        {{ $item['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>

        <a
            href="{{ route('mypage') }}"
            wire:navigate
            class="btn btn-ghost btn-sm mt-6 self-start"
            data-test="settings-back-to-mypage"
        >
            ← マイページに戻る
        </a>
    </div>

    <div class="divider md:hidden"></div>

    <div class="flex-1 max-md:pt-6">
        <h2 class="text-xl font-semibold text-base-content">{{ $heading ?? '' }}</h2>
        <p class="mt-1 text-base-content/70">{{ $subheading ?? '' }}</p>

        <div class="mt-5 w-full max-w-lg">
            {{ $slot }}
        </div>
    </div>
</div>
