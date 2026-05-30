@extends('layouts.app')

@section('content')
@php
    use Illuminate\Support\Str;

    $locale = app()->getLocale();
    $isAr = $locale === 'ar';

    $title = $isAr
        ? ($service->title_ar ?? '')
        : ($service->title_en ?? $service->title_ar ?? '');

    $excerpt = $isAr
        ? ($service->excerpt_ar ?? '')
        : ($service->excerpt_en ?? $service->excerpt_ar ?? '');

    $content = $isAr
        ? ($service->content_ar ?? '')
        : ($service->content_en ?? $service->content_ar ?? '');

    $platformName = $isAr
        ? ($service->platform->name_ar ?? __('services.general'))
        : ($service->platform->name_en ?? $service->platform->name_ar ?? __('services.general'));

    $iconUrl = !empty($service->icon) ? asset('storage/' . $service->icon) : null;

    $supportPhone = '920017083';
    $whatsPhone = '966505336956';
    $whatsText = $isAr ? ('استفسار عن: ' . $title) : ('Inquiry about: ' . $title);

    $serviceUrl = route('services.show', ['service' => $service->slug]);

    $plainExcerpt = trim(strip_tags((string) $excerpt));
    $plainContent = trim(strip_tags((string) $content));

    $metaDescriptionSource = $plainExcerpt !== ''
        ? $plainExcerpt
        : ($plainContent !== '' ? $plainContent : $title);

    $metaDescription = Str::limit($metaDescriptionSource, 160);

    $providerName = $isAr ? 'شركة آمر سبعة لحلول الأعمال' : 'Amr Seven Business Solutions';

    $requirements = $service->requirements_localized ?? [];
    $conditions = $service->conditions_localized ?? [];
    $steps = $service->steps_localized ?? [];
    $features = $service->features ?? [];

    $hasDesc = filled(trim(strip_tags((string) $content)));
    $hasRequirements = is_array($requirements) && count($requirements) > 0;
    $hasConditions = is_array($conditions) && count($conditions) > 0;
    $hasSteps = is_array($steps) && count($steps) > 0;
    $hasFeatures = is_array($features) && count($features) > 0;

    $tabs = collect([
        'desc' => $hasDesc || $hasFeatures,
        'req' => $hasRequirements,
        'conditions' => $hasConditions,
        'steps' => $hasSteps,
    ])->filter(fn ($visible) => $visible)->keys()->values()->all();

    $defaultTab = $tabs[0] ?? 'desc';

    $serviceSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Service',
        'name' => $title,
        'description' => $metaDescription,
        'url' => $serviceUrl,
        'image' => $iconUrl ?: asset('brand/amr7/amr7-og-image-1200x630.png'),
        'provider' => [
            '@type' => 'Organization',
            'name' => $providerName,
            'url' => url('/'),
            'logo' => asset('brand/amr7/amr7-logo-lockup-light.png'),
        ],
        'areaServed' => [
            '@type' => 'Country',
            'name' => 'Saudi Arabia',
        ],
        'availableLanguage' => ['ar', 'en'],
    ];

    if ((float) ($service->price ?? 0) > 0) {
        $serviceSchema['offers'] = [
            '@type' => 'Offer',
            'priceCurrency' => 'SAR',
            'price' => (string) $service->price,
            'availability' => 'https://schema.org/InStock',
            'url' => $serviceUrl,
        ];
    }

    $breadcrumbSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => $isAr ? 'دليل الخدمات' : 'Services',
                'item' => route('services.index'),
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => $platformName,
                'item' => $service->platform?->slug
                    ? route('services.platform', ['platform' => $service->platform->slug])
                    : route('services.index'),
            ],
            [
                '@type' => 'ListItem',
                'position' => 3,
                'name' => $title,
                'item' => $serviceUrl,
            ],
        ],
    ];

    $imageAlt = $isAr
        ? ($service->title_ar ?: 'أيقونة الخدمة')
        : ($service->title_en ?: $service->title_ar ?: 'Service icon');
@endphp

<script type="application/ld+json">{!! json_encode($serviceSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
<script type="application/ld+json">{!! json_encode($breadcrumbSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@if(!empty($officialFaqSchema))
    <script type="application/ld+json">{!! json_encode($officialFaqSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endif

<div class="bg-slate-50 font-['Tajawal'] pt-32 pb-20 relative" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
    <div class="absolute inset-0 pointer-events-none overflow-hidden z-0">
        <div class="absolute top-0 end-0 w-[500px] h-[500px] bg-[#1FA7A2]/5 blur-[100px] rounded-full"></div>
        <div class="absolute bottom-0 start-0 w-[500px] h-[500px] bg-amber-500/5 blur-[100px] rounded-full"></div>
    </div>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative z-10">

        <nav class="flex text-sm text-slate-500 mb-8" aria-label="Breadcrumb">
            <ol class="inline-flex items-center gap-2">
                <li>
                    <a href="{{ route('services.index') }}" class="hover:text-[#1FA7A2] transition-colors">
                        {{ __('services.catalog') }}
                    </a>
                </li>
                <li class="text-slate-300">/</li>
                <li><span class="text-slate-400">{{ $platformName }}</span></li>
                <li class="text-slate-300">/</li>
                <li class="font-bold text-[#1FA7A2]" aria-current="page">{{ $title }}</li>
            </ol>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <div class="lg:col-span-8 w-full order-2 lg:order-1">

                <div class="bg-white rounded-3xl p-6 md:p-8 shadow-sm border border-slate-200 mb-8 flex flex-col md:flex-row gap-6 items-start">
                    <div class="w-24 h-24 bg-slate-50 rounded-2xl flex items-center justify-center border border-slate-100 flex-shrink-0 p-4">
                        @if($iconUrl)
                            <img src="{{ $iconUrl }}"
                                 class="w-full h-full object-contain"
                                 alt="{{ $imageAlt }}"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                            <i class="fas fa-layer-group text-4xl text-[#1FA7A2]" style="display:none;"></i>
                        @else
                            <i class="fas fa-layer-group text-4xl text-[#1FA7A2]"></i>
                        @endif
                    </div>

                    <div class="flex-1">
                        <h1 class="text-2xl md:text-3xl font-black text-slate-800 mb-3">{{ $title }}</h1>
                        <p class="text-slate-500 leading-relaxed mb-4 text-base">{{ $metaDescription }}</p>

                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center gap-2 bg-[#f0fdfa] text-[#1FA7A2] text-xs font-bold px-3 py-1.5 rounded-lg border border-teal-100">
                                <i class="fas fa-bolt"></i> {{ __('services.instant_execution') }}
                            </span>
                            <span class="inline-flex items-center gap-2 bg-slate-100 text-slate-600 text-xs font-bold px-3 py-1.5 rounded-lg border border-slate-200">
                                <i class="fas fa-shield-alt"></i> {{ __('services.secure_100') }}
                            </span>
                        </div>
                    </div>
                </div>

                <div x-data="{ activeTab: '{{ $defaultTab }}' }" class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">

                    @if(count($tabs) > 0)
                        <div class="flex border-b border-slate-100 overflow-x-auto no-scrollbar bg-slate-50/50">

                            @if($hasDesc || $hasFeatures)
                                <button type="button"
                                        @click="activeTab = 'desc'"
                                        :class="activeTab === 'desc' ? 'text-[#1FA7A2] border-b-2 border-[#1FA7A2] bg-white' : 'text-slate-500 hover:text-slate-700 hover:bg-white'"
                                        class="flex-1 py-4 px-6 text-sm font-bold whitespace-nowrap transition-all">
                                    <i class="fas fa-file-alt ms-2"></i>{{ __('services.description') }}
                                </button>
                            @endif

                            @if($hasRequirements)
                                <button type="button"
                                        @click="activeTab = 'req'"
                                        :class="activeTab === 'req' ? 'text-[#1FA7A2] border-b-2 border-[#1FA7A2] bg-white' : 'text-slate-500 hover:text-slate-700 hover:bg-white'"
                                        class="flex-1 py-4 px-6 text-sm font-bold whitespace-nowrap transition-all">
                                    <i class="fas fa-list-check ms-2"></i>{{ __('services.requirements') }}
                                </button>
                            @endif

                            @if($hasConditions)
                                <button type="button"
                                        @click="activeTab = 'conditions'"
                                        :class="activeTab === 'conditions' ? 'text-[#1FA7A2] border-b-2 border-[#1FA7A2] bg-white' : 'text-slate-500 hover:text-slate-700 hover:bg-white'"
                                        class="flex-1 py-4 px-6 text-sm font-bold whitespace-nowrap transition-all">
                                    <i class="fas fa-circle-exclamation ms-2"></i>{{ __('services.conditions') }}
                                </button>
                            @endif

                            @if($hasSteps)
                                <button type="button"
                                        @click="activeTab = 'steps'"
                                        :class="activeTab === 'steps' ? 'text-[#1FA7A2] border-b-2 border-[#1FA7A2] bg-white' : 'text-slate-500 hover:text-slate-700 hover:bg-white'"
                                        class="flex-1 py-4 px-6 text-sm font-bold whitespace-nowrap transition-all">
                                    <i class="fas fa-stream ms-2"></i>{{ __('services.steps') }}
                                </button>
                            @endif
                        </div>
                    @endif

                    <div class="p-6 md:p-8 min-h-[300px]">

                        @if($hasDesc || $hasFeatures)
                            <div x-show="activeTab === 'desc'" x-transition.opacity @if($defaultTab !== 'desc') style="display:none;" @endif>
                                <h4 class="text-lg font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100">
                                    {{ __('services.service_details') }}
                                </h4>

                                @if($hasDesc)
                                    <div class="prose prose-slate max-w-none prose-a:text-[#1FA7A2] prose-strong:text-slate-800">
                                        {!! $content !!}
                                    </div>
                                @endif

                                @if($hasFeatures)
                                    <div class="mt-8 pt-6 border-t border-slate-100">
                                        <h5 class="text-slate-800 font-bold mb-4">{{ __('services.what_we_offer') }}</h5>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                            @foreach($features as $feature)
                                                <div class="flex items-center gap-3 bg-slate-50 p-3 rounded-xl border border-slate-100">
                                                    <div class="w-6 h-6 rounded-full bg-white text-[#1FA7A2] flex items-center justify-center shadow-sm border border-slate-100 shrink-0 text-xs">
                                                        <i class="fas fa-check"></i>
                                                    </div>
                                                    <span class="text-sm text-slate-700 font-bold">
                                                        {{ is_array($feature) ? ($feature['description'] ?? $feature['title'] ?? '') : $feature }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if($hasRequirements)
                            <div x-show="activeTab === 'req'" x-transition.opacity @if($defaultTab !== 'req') style="display:none;" @endif>
                                <div class="mb-6">
                                    <h5 class="flex items-center gap-2 text-slate-800 font-bold mb-4">
                                        <i class="fas fa-passport text-amber-500 bg-amber-50 p-2 rounded-lg"></i>
                                        {{ __('services.required_documents') }}
                                    </h5>

                                    <ul class="space-y-3">
                                        @foreach($requirements as $req)
                                            <li class="flex items-start gap-3 text-slate-600 text-sm bg-slate-50 p-3 rounded-xl border border-slate-100">
                                                <span class="mt-0.5 w-5 h-5 bg-[#1FA7A2] text-white rounded-full flex items-center justify-center text-[10px] shrink-0">
                                                    <i class="fas fa-check"></i>
                                                </span>
                                                <span class="leading-relaxed font-medium">
                                                    {{ is_array($req) ? ($req['item'] ?? $req['title'] ?? '') : $req }}
                                                </span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif

                        @if($hasConditions)
                            <div x-show="activeTab === 'conditions'" x-transition.opacity @if($defaultTab !== 'conditions') style="display:none;" @endif>
                                <div class="mb-6">
                                    <h5 class="flex items-center gap-2 text-slate-800 font-bold mb-4">
                                        <i class="fas fa-circle-exclamation text-rose-500 bg-rose-50 p-2 rounded-lg"></i>
                                        {{ __('services.conditions') }}
                                    </h5>

                                    <ul class="space-y-3">
                                        @foreach($conditions as $condition)
                                            <li class="flex items-start gap-3 text-slate-600 text-sm bg-slate-50 p-3 rounded-xl border border-slate-100">
                                                <span class="mt-0.5 w-5 h-5 bg-rose-500 text-white rounded-full flex items-center justify-center text-[10px] shrink-0">
                                                    <i class="fas fa-exclamation"></i>
                                                </span>
                                                <span class="leading-relaxed font-medium">
                                                    {{ is_array($condition) ? ($condition['item'] ?? $condition['title'] ?? '') : $condition }}
                                                </span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif

                        @if($hasSteps)
                            <div x-show="activeTab === 'steps'" x-transition.opacity @if($defaultTab !== 'steps') style="display:none;" @endif>
                                <h4 class="text-lg font-bold text-slate-800 mb-6">{{ __('services.customer_journey') }}</h4>

                                <div class="space-y-6 relative border-s-2 border-slate-100 ms-4 ps-8">
                                    @foreach($steps as $step)
                                        <div class="relative">
                                            <div class="absolute start-[-41px] top-0 w-8 h-8 bg-white border-4 border-[#1FA7A2] rounded-full flex items-center justify-center text-xs font-bold text-slate-700 shadow-sm z-10">
                                                {{ $loop->iteration }}
                                            </div>

                                            <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 hover:bg-white hover:shadow-md transition-all duration-300">
                                                <h6 class="font-bold text-slate-800 mb-1">
                                                    {{ $step['title'] ?? $step['step_name'] ?? __('services.step_n', ['n' => $loop->iteration]) }}
                                                </h6>
                                                <p class="text-xs text-slate-500 leading-relaxed">
                                                    {{ $step['description'] ?? '' }}
                                                </p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if(!count($tabs))
                            <div class="text-center py-12 bg-slate-50 rounded-2xl border border-dashed border-slate-300">
                                <i class="fas fa-circle-info text-4xl text-slate-300 mb-3"></i>
                                <p class="text-slate-400 text-sm">{{ __('services.no_details_available') }}</p>
                            </div>
                        @endif

                    </div>
                </div>

                @include('services.partials.official-content', ['officialContent' => $officialContent ?? null])

                <div class="mt-8 pt-6 border-t border-slate-200">
                    <h6 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">{{ __('services.related_keywords') }}:</h6>
                    <div class="flex flex-wrap gap-2">
                        <span class="px-3 py-1 bg-white border border-slate-200 rounded-full text-xs text-slate-500 hover:border-[#1FA7A2] hover:text-[#1FA7A2] transition-colors cursor-default">{{ $title }}</span>
                        <span class="px-3 py-1 bg-white border border-slate-200 rounded-full text-xs text-slate-500 hover:border-[#1FA7A2] hover:text-[#1FA7A2] transition-colors cursor-default">{{ $platformName }}</span>
                        <span class="px-3 py-1 bg-white border border-slate-200 rounded-full text-xs text-slate-500 hover:border-[#1FA7A2] hover:text-[#1FA7A2] transition-colors cursor-default">{{ __('services.business_services') }}</span>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-4 w-full order-1 lg:order-2">
                <div class="sticky top-32 space-y-6">

                    <div class="bg-white rounded-3xl p-6 shadow-xl shadow-slate-200/50 border border-slate-100">
                        <div class="flex justify-between items-center mb-6 pb-4 border-b border-slate-100">
                            <span class="text-slate-500 text-sm font-bold">{{ __('services.service_cost') }}</span>

                            <div class="text-end">
                                @if((float) ($service->price ?? 0) > 0)
                                    <h2 class="text-3xl font-black text-[#1FA7A2]">
                                        {{ $service->price }} <span class="text-sm font-bold text-slate-400">{{ __('services.sar') }}</span>
                                    </h2>
                                @else
                                    <h3 class="text-xl font-bold text-slate-800">{{ __('services.agreement') }}</h3>
                                @endif
                            </div>
                        </div>

                        @if((float) ($service->govt_fees ?? 0) > 0)
                            <div class="bg-amber-50 border border-amber-100 rounded-xl p-3 mb-6 flex justify-between items-center text-xs">
                                <span class="text-amber-800 font-bold">
                                    <i class="fas fa-coins ms-1"></i>{{ __('services.govt_fees_approx') }}
                                </span>
                                <span class="font-bold text-amber-600">{{ $service->govt_fees }} {{ __('services.sar') }}</span>
                            </div>
                        @endif

                        <div class="space-y-4 mb-8">
                            <div class="flex justify-between text-sm items-center">
                                <span class="text-slate-500 flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-teal-50 text-[#1FA7A2] flex items-center justify-center"><i class="far fa-clock"></i></div>
                                    {{ __('services.duration') }}
                                </span>
                                <span class="font-bold text-slate-800 bg-slate-50 px-3 py-1 rounded-lg border border-slate-100">
                                    {{ $service->duration ?? __('services.not_specified') }}
                                </span>
                            </div>

                            <div class="flex justify-between text-sm items-center">
                                <span class="text-slate-500 flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-teal-50 text-[#1FA7A2] flex items-center justify-center"><i class="fas fa-file-invoice"></i></div>
                                    {{ __('services.payment') }}
                                </span>
                                <span class="font-bold text-slate-800 bg-slate-50 px-3 py-1 rounded-lg border border-slate-100">
                                    {{ __('services.after_review') }}
                                </span>
                            </div>
                        </div>

                        <form method="GET" action="{{ route('services.request', ['service_id' => $service->id]) }}" class="mb-4">
                            <button type="submit"
                               class="block w-full py-4 bg-gradient-to-r from-[#1FA7A2] to-[#167F7B] hover:shadow-lg hover:shadow-teal-500/30 text-white font-bold rounded-xl text-center transition-all duration-300 transform hover:-translate-y-1 text-lg">
                                {{ __('services.request_now') }}
                            </button>
                        </form>

                        <div class="grid grid-cols-2 gap-3">
                            <a href="https://wa.me/{{ $whatsPhone }}?text={{ urlencode($whatsText) }}" target="_blank" rel="noopener noreferrer"
                               class="flex items-center justify-center gap-2 py-3 border border-green-500/30 text-green-600 hover:bg-green-50 rounded-xl font-bold text-xs transition-colors">
                                <i class="fab fa-whatsapp text-lg"></i> {{ __('services.whatsapp') }}
                            </a>
                            <a href="tel:{{ $supportPhone }}"
                               class="flex items-center justify-center gap-2 py-3 border border-sky-500/30 text-sky-600 hover:bg-sky-50 rounded-xl font-bold text-xs transition-colors">
                                <i class="fas fa-phone-alt text-lg"></i> {{ __('services.call') }}
                            </a>
                        </div>
                    </div>

                    <div class="bg-[#f0fdfa] rounded-2xl p-5 border border-teal-100 flex items-start gap-4 shadow-sm">
                        <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-[#1FA7A2] shadow-sm flex-shrink-0 border border-teal-50">
                            <i class="fas fa-shield-halved text-lg"></i>
                        </div>
                        <div>
                            <h6 class="font-bold text-[#1FA7A2] text-sm mb-1">{{ __('services.golden_guarantee') }}</h6>
                            <p class="text-xs text-teal-700/70 leading-relaxed">{{ __('services.refund_guarantee') }}</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl p-5 border border-slate-200 text-center shadow-sm">
                        <div class="w-12 h-12 bg-slate-50 rounded-full flex items-center justify-center text-slate-400 mx-auto mb-3">
                            <i class="fas fa-headset text-xl"></i>
                        </div>
                        <h6 class="font-bold text-slate-800 text-sm mb-1">{{ __('services.need_help') }}</h6>
                        <p class="text-xs text-slate-500 mb-3">{{ __('services.team_ready') }}</p>
                        <a href="tel:{{ $supportPhone }}" class="text-[#1FA7A2] font-black text-lg font-mono tracking-wider">{{ $supportPhone }}</a>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection
