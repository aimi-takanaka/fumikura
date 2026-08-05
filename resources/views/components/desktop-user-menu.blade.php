<div {{ $attributes->class('dropdown dropdown-top dropdown-end w-full max-lg:dropdown-end') }}>
    <div tabindex="0" role="button" class="btn btn-ghost w-full justify-start gap-2 px-2" data-test="sidebar-menu-button">
        <div class="avatar">
            <div class="w-8 rounded-full bg-primary/15">
                @if (auth()->user()->avatarUrl())
                    <img src="{{ auth()->user()->avatarUrl() }}" alt="{{ auth()->user()->name }}" />
                @else
                    <span class="flex h-full items-center justify-center text-xs font-semibold text-primary">
                        {{ auth()->user()->initials() }}
                    </span>
                @endif
            </div>
        </div>
        <div class="grid min-w-0 flex-1 text-start text-sm leading-tight">
            <span class="truncate font-medium">{{ auth()->user()->name }}</span>
            <span class="truncate text-base-content/60">{{ auth()->user()->email }}</span>
        </div>
    </div>
    <ul tabindex="0" class="menu dropdown-content z-50 mt-3 w-64 rounded-box border border-base-300 bg-base-100 p-2 shadow-lg">
        <li class="menu-title">
            <span class="truncate">{{ auth()->user()->email }}</span>
        </li>
        <li>
            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <button type="submit" class="w-full text-left" data-test="logout-button">
                    {{ __('Log out') }}
                </button>
            </form>
        </li>
    </ul>
</div>
