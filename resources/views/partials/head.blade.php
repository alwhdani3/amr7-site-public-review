<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="theme-color" content="#1FA7A2">

@php
    $locale = app()->getLocale() === 'en' ? 'en' : 'ar';
    $ogLocale = $locale === 'ar' ? 'ar_AR' : 'en_US';

    $currentUrl = url()->current();
    $attributeCanonical = request()->attributes->get('seo_canonical_url');
    $robotsMeta = request()->attributes->get('seo_robots') ?: 'index,follow,max-image-preview:large';
    $currentPath = '/' . ltrim(request()->path(), '/');

    $isEnVersion = $currentPath === '/en' || str_starts_with($currentPath, '/en/');

    $arPath = $isEnVersion
        ? ('/' . ltrim((string) substr($currentPath, 3), '/'))
        : $currentPath;

    $enPath = $isEnVersion
        ? $currentPath
        : ($currentPath === '/' ? '/en' : '/en' . $currentPath);

    $arPath = $arPath === '' ? '/' : (rtrim($arPath, '/') ?: '/');
    $enPath = rtrim($enPath, '/') ?: '/en';

    $arUrl = url($arPath);
    $enUrl = url($enPath);
    $xDefaultUrl = $arUrl;

    $defaultTitle = $locale === 'ar'
        ? 'شركة آمر سبعة لحلول الأعمال'
        : 'Amr Seven Business Solutions';

    $defaultDescription = $locale === 'ar'
        ? 'شركة آمر سبعة لحلول الأعمال — تأسيس شركات، خدمات أعمال، استثمار أجنبي، وحلول امتثال في المملكة العربية السعودية.'
        : 'Amr Seven Business Solutions — company formation, business services, foreign investment, and compliance solutions in Saudi Arabia.';

    $defaultSeoImage = asset('brand/amr7/amr7-og-image-1200x630.png');

    $rawCanonical = class_exists('\Artesaos\SEOTools\Facades\SEOMeta')
        ? \Artesaos\SEOTools\Facades\SEOMeta::getCanonical()
        : null;

    $canonicalUrl = $rawCanonical ?: ($attributeCanonical ?: $currentUrl);
@endphp

@if (class_exists('\Artesaos\SEOTools\Facades\SEOTools'))
    @php
        if (! \Artesaos\SEOTools\Facades\SEOMeta::getCanonical()) {
            \Artesaos\SEOTools\Facades\SEOMeta::setCanonical($canonicalUrl);
        }

        if (! \Artesaos\SEOTools\Facades\SEOMeta::getTitle()) {
            \Artesaos\SEOTools\Facades\SEOMeta::setTitle($defaultTitle);
        }

        if (! \Artesaos\SEOTools\Facades\SEOMeta::getDescription()) {
            \Artesaos\SEOTools\Facades\SEOMeta::setDescription($defaultDescription);
        }

        if ($robotsMeta) {
            \Artesaos\SEOTools\Facades\SEOMeta::setRobots($robotsMeta);
        }

        \Artesaos\SEOTools\Facades\OpenGraph::setUrl(\Artesaos\SEOTools\Facades\SEOMeta::getCanonical() ?: $canonicalUrl);
        \Artesaos\SEOTools\Facades\OpenGraph::setTitle(
            \Artesaos\SEOTools\Facades\SEOMeta::getTitle() ?: $defaultTitle
        );
        \Artesaos\SEOTools\Facades\OpenGraph::setDescription(
            \Artesaos\SEOTools\Facades\SEOMeta::getDescription() ?: $defaultDescription
        );
        \Artesaos\SEOTools\Facades\OpenGraph::addProperty('locale', $ogLocale);
        \Artesaos\SEOTools\Facades\OpenGraph::addProperty(
            'site_name',
            $locale === 'ar' ? 'شركة آمر سبعة لحلول الأعمال' : 'Amr Seven Business Solutions'
        );

        \Artesaos\SEOTools\Facades\TwitterCard::setType('summary_large_image');
        \Artesaos\SEOTools\Facades\TwitterCard::setUrl(\Artesaos\SEOTools\Facades\SEOMeta::getCanonical() ?: $canonicalUrl);
        \Artesaos\SEOTools\Facades\TwitterCard::setTitle(
            \Artesaos\SEOTools\Facades\SEOMeta::getTitle() ?: $defaultTitle
        );
        \Artesaos\SEOTools\Facades\TwitterCard::setDescription(
            \Artesaos\SEOTools\Facades\SEOMeta::getDescription() ?: $defaultDescription
        );
    @endphp

    <meta property="og:image" content="{{ $defaultSeoImage }}">
    <meta name="twitter:image" content="{{ $defaultSeoImage }}">
    {!! \Artesaos\SEOTools\Facades\SEOMeta::generate() !!}
    {!! \Artesaos\SEOTools\Facades\OpenGraph::generate() !!}
    {!! \Artesaos\SEOTools\Facades\TwitterCard::generate() !!}
    @php
        $__jsonLd = \Artesaos\SEOTools\Facades\JsonLd::generate();
    @endphp
    @if($__jsonLd && ! str_contains($__jsonLd, '<?php'))
        {!! $__jsonLd !!}
    @endif
@else
    <title>{{ $defaultTitle }}</title>
    <meta name="description" content="{{ $defaultDescription }}">
    <meta name="robots" content="{{ $robotsMeta }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:title" content="{{ $defaultTitle }}">
    <meta property="og:description" content="{{ $defaultDescription }}">
    <meta property="og:image" content="{{ $defaultSeoImage }}">
    <meta name="twitter:image" content="{{ $defaultSeoImage }}">
    <meta property="og:locale" content="{{ $ogLocale }}">
    <meta property="og:site_name" content="{{ $locale === 'ar' ? 'شركة آمر سبعة لحلول الأعمال' : 'Amr Seven Business Solutions' }}">
@endif

<link rel="alternate" hreflang="ar" href="{{ $arUrl }}">
<link rel="alternate" hreflang="en" href="{{ $enUrl }}">
<link rel="alternate" hreflang="x-default" href="{{ $xDefaultUrl }}">

@stack('head')

<link rel="icon" href="{{ asset('brand/amr7/favicon.ico') }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('brand/amr7/amr7-app-icon-180.png') }}">

<style>
    [x-cloak] { display: none !important; }
</style>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link rel="preload"
      href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Tajawal:wght@300;400;500;700;800&display=swap"
      as="style"
      onload="this.onload=null;this.rel='stylesheet'">

<link rel="preload"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
      as="style"
      onload="this.onload=null;this.rel='stylesheet'"
      data-navigate-track="reload">

<noscript>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Tajawal:wght@300;400;500;700;800&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</noscript>
