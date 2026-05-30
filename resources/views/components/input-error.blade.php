@props(['messages'])

@if ($messages)
    <div {{ $attributes->merge(['class' => 'rounded-xl border border-red-500/20 bg-red-50 dark:bg-red-900/10 p-4 shadow-sm']) }}>
        <div class="flex items-center gap-2 mb-2 text-red-700 dark:text-red-400">
            <svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
            </svg>
            <span class="text-sm font-bold tracking-tight">{{ __('Whoops! Something went wrong.') }}</span>
        </div>
        
        <ul class="ms-1 text-sm text-red-600 dark:text-red-300 space-y-1 list-disc list-inside marker:text-red-400">
            @foreach ((array) $messages as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
@endif