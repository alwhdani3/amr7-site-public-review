<flux:header
    container
    wire:cloak
    class="sticky top-0 z-50 border-b border-slate-200 bg-white/80 backdrop-blur-md dark:border-neutral-800 dark:bg-neutral-900/80"
>
    <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

    <a href="{{ route('dashboard') }}" class="ms-2 me-5 flex items-center space-x-2 rtl:space-x-reverse lg:ms-0" wire:navigate>
        <x-app-logo class="size-8" />
    </a>

    <flux:navbar class="-mb-px max-lg:hidden">
        <flux:navbar.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
            {{ __('Dashboard') }}
        </flux:navbar.item>
    </flux:navbar>

    <flux:spacer />

    <flux:navbar class="me-1.5 space-x-0.5 rtl:space-x-reverse py-0!">
        <flux:tooltip :content="__('Search')" position="bottom">
            <flux:navbar.item class="!h-10 [&>div>svg]:size-5" icon="magnifying-glass" href="#" :label="__('Search')" />
        </flux:tooltip>
    </flux:navbar>

    <flux:dropdown position="top" align="end">
        <flux:profile class="cursor-pointer" :initials="auth()->user()->initials()" />

        <flux:menu>
            <flux:menu.radio.group>
                <div class="p-0 text-sm font-normal">
                    <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                        <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                            <span class="flex h-full w-full items-center justify-center rounded-lg bg-slate-200 text-slate-700 dark:bg-neutral-700 dark:text-white">
                                {{ auth()->user()->initials() }}
                            </span>
                        </span>

                        <div class="grid flex-1 text-start text-sm leading-tight">
                            <span class="truncate font-semibold text-slate-900 dark:text-white">{{ auth()->user()->name }}</span>
                            <span class="truncate text-xs text-slate-500 dark:text-neutral-400">{{ auth()->user()->email }}</span>
                        </div>
                    </div>
                </div>
            </flux:menu.radio.group>

            @if (Route::has('profile.edit'))
                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                        {{ __('Settings') }}
                    </flux:menu.item>
                </flux:menu.radio.group>
            @endif

            <flux:menu.separator />

            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                    {{ __('Log Out') }}
                </flux:menu.item>
            </form>
        </flux:menu>
    </flux:dropdown>
</flux:header>

<flux:sidebar
    stashable
    sticky
    wire:cloak
    class="lg:hidden border-e border-slate-200 bg-slate-50 dark:border-neutral-800 dark:bg-neutral-900"
>
    <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

    <a href="{{ route('dashboard') }}" class="ms-1 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
        <x-app-logo class="size-8" />
    </a>

    <flux:navlist variant="outline">
        <flux:navlist.group :heading="__('Platform')">
            <flux:navlist.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                {{ __('Dashboard') }}
            </flux:navlist.item>
        </flux:navlist.group>
    </flux:navlist>
</flux:sidebar>