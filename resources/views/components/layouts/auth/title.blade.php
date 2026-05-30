@props([
    'title' => '',
    'description' => null,
])

<div {{ $attributes->class(['flex flex-col gap-2 text-center']) }}>
    <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-transparent bg-clip-text bg-gradient-to-br from-slate-900 via-slate-800 to-teal-800 dark:from-white dark:via-slate-200 dark:to-teal-300">
        {{ $title }}
    </h1>

    @if(filled($description))
        <p class="text-sm text-slate-500 dark:text-slate-300 font-medium">
            {{ $description }}
        </p>
    @endif

    @if(trim($slot) !== '')
        <div class="text-sm text-slate-500 dark:text-slate-300 font-medium">
            {{ $slot }}
        </div>
    @endif
</div>
