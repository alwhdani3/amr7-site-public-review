@props([
    'title',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-2']) }}>
    <flux:heading size="xl" level="1" class="text-slate-900 dark:text-white tracking-tight">
        {{ $title }}
    </flux:heading>

    @if ($description)
        <flux:subheading class="text-slate-500 dark:text-neutral-400">
            {{ $description }}
        </flux:subheading>
    @endif
</div>