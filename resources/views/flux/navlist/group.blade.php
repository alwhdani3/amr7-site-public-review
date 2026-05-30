@props([
    'expandable' => false,
    'expanded' => true,
    'heading' => null,
])

<?php if ($expandable && $heading): ?>

<ui-disclosure
    {{ $attributes->class('group/disclosure') }}
    @if ($expanded === true) open @endif
    data-flux-navlist-group
>
    <button
        type="button"
        class="group/disclosure-button mb-1 flex h-10 w-full items-center rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 lg:h-9 dark:text-slate-400 dark:hover:bg-white/5 dark:hover:text-white transition-colors duration-200"
    >
        <div class="ps-3 pe-4 text-slate-400 group-hover/disclosure-button:text-teal-500 transition-colors">
            <flux:icon.chevron-down class="hidden size-3.5! group-data-open/disclosure-button:block" />
            <flux:icon.chevron-right class="block size-3.5! group-data-open/disclosure-button:hidden rtl:rotate-180" />
        </div>

        <span class="text-sm font-bold leading-none">{{ $heading }}</span>
    </button>

    <div class="relative hidden space-y-0.5 ps-7 data-open:block" @if ($expanded === true) data-open @endif>
        {{-- خط التوصيل العمودي --}}
        <div class="absolute inset-y-1 start-0 ms-[18px] w-px bg-slate-200 dark:bg-slate-700/50"></div>

        {{ $slot }}
    </div>
</ui-disclosure>

<?php elseif ($heading): ?>

<div {{ $attributes->class('block space-y-1 mt-4 first:mt-0') }}>
    <div class="px-3 py-2">
        <div class="text-[11px] font-bold uppercase tracking-wider text-teal-600 dark:text-teal-500/80">
            {{ $heading }}
        </div>
    </div>

    <div>
        {{ $slot }}
    </div>
</div>

<?php else: ?>

<div {{ $attributes->class('block space-y-1') }}>
    {{ $slot }}
</div>

<?php endif; ?>