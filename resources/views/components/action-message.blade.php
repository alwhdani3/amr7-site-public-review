@props(['on'])

<div x-data="{ shown: false, timeout: null }"
     x-on:{{ $on }}.window="clearTimeout(timeout); shown = true; timeout = setTimeout(() => { shown = false }, 2000)"
     x-show="shown"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform translate-y-2"
     x-transition:enter-end="opacity-100 transform translate-y-0"
     x-transition:leave="transition ease-in duration-500"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     style="display: none;"
    {{ $attributes->merge(['class' => 'flex items-center gap-2 text-sm font-bold text-teal-600 dark:text-teal-400']) }}>
    
    <div class="flex h-5 w-5 items-center justify-center rounded-full bg-teal-100 dark:bg-teal-900/50">
        <svg class="size-3.5 text-teal-600 dark:text-teal-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
        </svg>
    </div>

    <span>{{ $slot->isEmpty() ? __('Saved.') : $slot }}</span>
</div>