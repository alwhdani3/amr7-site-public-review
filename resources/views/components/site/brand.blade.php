@props([
    'variant' => 'light',
    'href' => url('/'),
    'compact' => false,
    'linked' => true,
])

@php
    $isDark = $variant === 'dark';
    $isCompact = filter_var($compact, FILTER_VALIDATE_BOOL) || $variant === 'compact';
    $isArabic = app()->getLocale() === 'ar';
    $displayName = $isArabic ? 'شركة آمر سبعة لحلول الأعمال' : 'Amr Seven Business Solutions';
    $subLabel = $isArabic ? 'Business Solutions' : 'Saudi Business Services';
    $markSrc = $isDark
        ? asset('brand/amr7/amr7-mark-dark.svg')
        : asset('brand/amr7/amr7-mark-light.svg');
    $labelColor = $isDark ? 'text-white' : 'text-[#0A2540]';
    $subLabelColor = $isDark ? 'text-[#8EDCEF]' : 'text-[#1FA7A2]';
    $markSize = $isCompact ? 'h-9 w-9' : 'h-11 w-11 md:h-12 md:w-12';
    $brandSize = $isCompact ? 'text-[13px] md:text-sm' : 'text-[15px] md:text-lg';
    $englishSize = $isCompact ? 'text-[8px]' : 'text-[9px] md:text-[10px]';
@endphp

@if($linked)
    <a
        href="{{ $href }}"
        wire:navigate
        {{ $attributes->merge([
            'class' => 'inline-flex items-center gap-3 rounded-xl transition-transform duration-200 hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-[#1FA7A2] focus:ring-offset-2',
            'aria-label' => $displayName,
        ]) }}
    >
@else
    <span
        {{ $attributes->merge([
            'class' => 'inline-flex items-center gap-3',
            'aria-label' => $displayName,
        ]) }}
    >
@endif
    <img
        src="{{ $markSrc }}"
        class="{{ $markSize }} shrink-0 object-contain"
        alt=""
        width="48"
        height="48"
        loading="eager"
    >
    <span class="flex min-w-0 flex-col leading-tight">
        <span class="{{ $labelColor }} {{ $brandSize }} font-extrabold tracking-normal whitespace-nowrap">
            {{ $displayName }}
        </span>
        <span class="{{ $subLabelColor }} {{ $englishSize }} font-extrabold uppercase tracking-[0.18em]" dir="ltr">
            {{ $subLabel }}
        </span>
    </span>
@if($linked)
    </a>
@else
    </span>
@endif
