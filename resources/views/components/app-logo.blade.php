<div class="flex aspect-square size-10 items-center justify-center rounded-xl bg-slate-900 dark:bg-white shadow-md shadow-slate-200/50 dark:shadow-none transition-transform hover:scale-105">
    <x-app-logo-icon class="size-6 fill-current text-white dark:text-slate-900" />
</div>

<div class="ms-3 grid flex-1 text-start">
    <span class="truncate text-sm font-bold leading-tight text-slate-900 dark:text-white">
        {{ config('app.name', 'Amr Seven Business Solutions') }}
    </span>
    <span class="truncate text-[10px] font-medium uppercase tracking-wider text-slate-500 dark:text-neutral-400">
        {{ __('Platform') }}
    </span>
</div>