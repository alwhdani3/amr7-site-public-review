@php
    $isRtl = app()->getLocale() === 'ar';
    $locale = app()->getLocale() === 'en' ? 'en' : 'ar';
@endphp

<div class="min-h-screen bg-slate-50/50 font-['Tajawal'] pb-24 selection:bg-[#1FA7A2] selection:text-white" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
    @if(!empty($officialPageSchema))
        <script type="application/ld+json">{!! json_encode($officialPageSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    @endif

    @if(!empty($officialFaqSchema))
        <script type="application/ld+json">{!! json_encode($officialFaqSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    @endif

    {{-- Hero Section with Ambient Glow --}}
    <section class="relative pt-32 pb-24 overflow-hidden bg-white">
        {{-- Blobs (استخدام start و end للاتجاهات المنطقية) --}}
        <div class="absolute top-0 start-1/2 -translate-x-1/2 rtl:translate-x-1/2 w-full max-w-7xl h-full pointer-events-none opacity-40">
            <div class="absolute top-[-10%] start-[10%] w-96 h-96 bg-[#1FA7A2] rounded-full mix-blend-multiply filter blur-[120px] animate-blob"></div>
            <div class="absolute top-[20%] end-[10%] w-96 h-96 bg-teal-200 rounded-full mix-blend-multiply filter blur-[120px] animate-blob animation-delay-2000"></div>
        </div>

        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9IiNlNTRmNWMiIGZpbGwtb3BhY2l0eT0iMC4yIi8+PC9zdmc+')] [mask-image:linear-gradient(to_bottom,white,transparent)] pointer-events-none opacity-50"></div>

        <div class="container mx-auto px-4 relative z-10">
            <div class="max-w-4xl mx-auto text-center">
                <div class="inline-flex items-center gap-2.5 px-5 py-2 rounded-full bg-slate-50 border border-slate-100 text-[#1FA7A2] text-sm font-bold mb-8 shadow-sm animate__animated animate__fadeInDown">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-[#1FA7A2]"></span>
                    </span>
                    {{ __('Discover Our Services') }}
                </div>

                @if(($isPlatformRoute ?? false) && !empty($activePlatformName))
                    <h1 class="text-4xl md:text-6xl lg:text-7xl font-black text-slate-900 mb-6 tracking-tight leading-tight">
                        <span class="relative z-10 text-transparent bg-clip-text bg-gradient-to-r from-[#1FA7A2] to-emerald-400">
                            {{ __(':platform — services for Saudi establishments', ['platform' => $activePlatformName]) }}
                        </span>
                    </h1>

                    <p class="text-slate-500 text-lg md:text-xl mb-12 max-w-2xl mx-auto font-medium leading-relaxed">
                        {{ __('Browse all :platform services available through Amr 7 in Saudi Arabia.', ['platform' => $activePlatformName]) }}
                    </p>
                @else
                    <h1 class="text-4xl md:text-6xl lg:text-7xl font-black text-slate-900 mb-6 tracking-tight leading-tight">
                        {{ __('Services Catalog') }}
                        <span class="relative inline-block mt-2 md:mt-0">
                            <span class="relative z-10 text-transparent bg-clip-text bg-gradient-to-r from-[#1FA7A2] to-emerald-400">
                                {{ __('Amr Seven') }}
                            </span>
                        </span>
                    </h1>

                    <p class="text-slate-500 text-lg md:text-xl mb-12 max-w-2xl mx-auto font-medium leading-relaxed">
                        {{ __('Explore a wide range of professional services tailored to elevate your business.') }}
                    </p>
                @endif

                {{-- Search Bar --}}
                <div class="relative max-w-3xl mx-auto mb-14 group z-20 animate__animated animate__fadeInUp">
                    <div class="absolute -inset-1.5 bg-gradient-to-r from-[#1FA7A2] to-teal-300 rounded-[2.5rem] blur opacity-25 group-hover:opacity-40 transition duration-500"></div>
                    <div class="relative flex items-center bg-white/90 backdrop-blur-xl border border-white/60 shadow-xl rounded-[2rem] p-2.5 focus-within:ring-4 focus-within:ring-[#1FA7A2]/10 transition-all">
                        <div class="flex items-center justify-center w-14 text-slate-400 group-focus-within:text-[#1FA7A2] transition-colors">
                            <i class="fas fa-search text-xl" aria-hidden="true"></i>
                        </div>

                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            class="w-full h-14 bg-transparent border-none text-slate-800 font-bold text-lg placeholder-slate-400 focus:outline-none focus:ring-0 px-2"
                            placeholder="{{ __('Search for a service, keyword, or platform...') }}"
                            aria-label="{{ __('Search services') }}"
                        >

                        <div wire:loading wire:target="search" class="absolute end-6">
                            <div class="w-6 h-6 border-2 border-[#1FA7A2]/20 border-t-[#1FA7A2] rounded-full animate-spin"></div>
                        </div>
                    </div>
                </div>

                {{-- Categories --}}
                <div class="inline-flex flex-wrap justify-center gap-2 p-2 bg-slate-50/80 backdrop-blur-md border border-slate-100 rounded-3xl animate__animated animate__fadeInUp animate__delay-1s">
                    @foreach($this->categories as $cat)
                        @php
                            $catName = $locale === 'en'
                                ? ($cat->name_en ?? $cat->name_ar ?? '')
                                : ($cat->name_ar ?? $cat->name_en ?? '');
                        @endphp

                        <button
                            wire:click="setCategory({{ $cat->id }})"
                            class="px-6 py-2.5 rounded-2xl text-sm font-bold transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-[#1FA7A2]/20
                            {{ $activeCategoryId == $cat->id
                                ? 'bg-white text-[#1FA7A2] shadow-md border border-slate-100 scale-100'
                                : 'text-slate-500 hover:text-slate-800 hover:bg-white/60 border border-transparent scale-100 hover:-translate-y-0.5' }}"
                        >
                            {{ $catName }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <div class="container mx-auto px-4 mt-12">

        {{-- Platforms --}}
        <div class="flex justify-center mb-14 animate__animated animate__fadeIn">
            @if($this->platforms->count() > 0)
                <div class="flex flex-wrap justify-center gap-3">
                    <button
                        wire:click="clearPlatform"
                        class="px-5 py-2.5 rounded-xl text-sm font-bold transition-all duration-300 border focus:outline-none
                        {{ is_null($activePlatformId)
                            ? 'border-[#1FA7A2]/30 text-[#1FA7A2] bg-[#1FA7A2]/5 shadow-sm'
                            : 'border-slate-200/60 bg-white text-slate-500 hover:border-slate-300 hover:bg-slate-50 hover:text-slate-800 shadow-sm' }}"
                    >
                        {{ __('All Platforms') }}
                    </button>

                    @foreach($this->platforms as $platform)
                        @php
                            $platformName = $locale === 'en'
                                ? ($platform->name_en ?? $platform->name_ar ?? '')
                                : ($platform->name_ar ?? $platform->name_en ?? '');
                        @endphp

                        <button
                            wire:click="setPlatform({{ $platform->id }})"
                            class="px-5 py-2.5 rounded-xl text-sm font-bold transition-all duration-300 border focus:outline-none
                            {{ $activePlatformId == $platform->id
                                ? 'border-[#1FA7A2]/30 text-[#1FA7A2] bg-[#1FA7A2]/5 shadow-sm'
                                : 'border-slate-200/60 bg-white text-slate-500 hover:border-slate-300 hover:bg-slate-50 hover:text-slate-800 shadow-sm' }}"
                        >
                            {{ $platformName }}
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        @include('services.partials.official-content', ['officialContent' => $officialContent ?? null])

        {{-- Loading State Grid --}}
        <div wire:loading.flex wire:target="setCategory,setPlatform,clearPlatform,search" class="justify-center w-full py-24">
            <div class="flex flex-col items-center gap-5">
                <div class="relative w-20 h-20">
                    <div class="absolute inset-0 border-4 border-slate-100 rounded-full"></div>
                    <div class="absolute inset-0 border-4 border-[#1FA7A2] rounded-full border-t-transparent animate-spin"></div>
                </div>
                <span class="text-[#1FA7A2] text-sm font-black tracking-widest uppercase animate-pulse">{{ __('Curating Services...') }}</span>
            </div>
        </div>

        {{-- Services Grid --}}
        <div wire:loading.remove wire:target="setCategory,setPlatform,clearPlatform,search" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
            @forelse($services as $service)
                @php
                    $platformName = $service->platform
                        ? ($locale === 'en'
                            ? ($service->platform->name_en ?? $service->platform->name_ar ?? '')
                            : ($service->platform->name_ar ?? $service->platform->name_en ?? ''))
                        : __('General');

                    $title = $locale === 'en'
                        ? ($service->title_en ?? $service->title_ar ?? '')
                        : ($service->title_ar ?? $service->title_en ?? '');

                    $excerpt = $locale === 'en'
                        ? ($service->excerpt_en ?? $service->excerpt_ar ?? '')
                        : ($service->excerpt_ar ?? $service->excerpt_en ?? '');
                @endphp

                <article class="group relative bg-white rounded-[2.5rem] p-3 border border-slate-100 shadow-sm hover:shadow-[0_20px_40px_-15px_rgba(35,109,111,0.2)] hover:border-[#1FA7A2]/20 transition-all duration-500 flex flex-col h-full transform hover:-translate-y-2">
                    <div class="relative h-52 rounded-[2rem] bg-gradient-to-br from-slate-50 to-slate-100 overflow-hidden flex items-center justify-center mb-5 group-hover:from-[#1FA7A2]/5 group-hover:to-teal-50 transition-colors duration-500">
                        {{-- Platform Badge --}}
                        <div class="absolute top-4 start-4 z-10">
                            <span class="backdrop-blur-md bg-white/80 px-3 py-1.5 rounded-xl text-slate-700 text-[11px] font-black tracking-wider shadow-sm border border-white flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#1FA7A2]"></span>
                                {{ $platformName }}
                            </span>
                        </div>

                        {{-- Service Image / Fallback --}}
                        <div class="w-24 h-24 relative z-10 transform group-hover:scale-110 group-hover:rotate-3 transition-all duration-500 ease-out">
                            <img
                                src="{{ $service->icon ? asset('storage/' . $service->icon) : asset('images/service-placeholder.png') }}"
                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                class="w-full h-full object-contain drop-shadow-xl"
                                alt="{{ $title }}"
                                loading="lazy"
                                decoding="async"
                            >
                            <div class="hidden w-full h-full items-center justify-center bg-white rounded-2xl shadow-md rotate-3 group-hover:rotate-6 transition-transform">
                                <i class="fas fa-layer-group text-3xl text-[#1FA7A2]" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>

                    <div class="px-3 pb-3 flex-grow flex flex-col">
                        <h3 class="text-xl font-black text-slate-800 mb-3 line-clamp-2 group-hover:text-[#1FA7A2] transition-colors duration-300" title="{{ $title }}">
                            <a href="{{ route('services.show', ['service' => $service->slug]) }}" wire:navigate class="focus:outline-none">
                                <span class="absolute inset-0 z-20"></span>
                                {{ $title }}
                            </a>
                        </h3>

                        <p class="text-sm font-medium text-slate-500 mb-6 line-clamp-3 leading-relaxed flex-grow">
                            {{ \Illuminate\Support\Str::limit($excerpt, 110) }}
                        </p>

                        <div class="pt-4 border-t border-slate-50 mt-auto relative z-30">
                            <div class="relative flex items-center justify-between w-full p-3 rounded-2xl bg-slate-50 group-hover:bg-[#1FA7A2] transition-all duration-300 overflow-hidden">
                                <span class="relative z-10 font-bold text-slate-700 group-hover:text-white text-sm px-2 transition-colors">
                                    {{ __('View Details') }}
                                </span>
                                <div class="relative z-10 w-9 h-9 rounded-full bg-white flex items-center justify-center shadow-sm group-hover:shadow-md transform transition-all group-hover:scale-110">
                                    <i class="fas fa-arrow-right rtl:-scale-x-100 text-[#1FA7A2] text-sm" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-full py-24 text-center">
                    <div class="inline-flex items-center justify-center w-32 h-32 rounded-full bg-white border border-slate-100 shadow-sm mb-8 relative">
                        <div class="absolute inset-0 bg-[#1FA7A2]/5 rounded-full animate-ping opacity-50"></div>
                        <i class="fas {{ ($isPlatformRoute ?? false) && !empty($activePlatformName) ? 'fa-hourglass-half' : 'fa-search' }} text-[#1FA7A2]/30 text-5xl" aria-hidden="true"></i>
                    </div>

                    @if(($isPlatformRoute ?? false) && !empty($activePlatformName))
                        <h3 class="text-3xl font-black text-slate-800 mb-3">{{ __(':platform services are being prepared', ['platform' => $activePlatformName]) }}</h3>
                        <p class="text-slate-500 text-lg font-medium mb-8 max-w-xl mx-auto">{{ __('Our team is finalizing the list of :platform services on Amr 7. In the meantime, browse our full catalog or contact our team to request a specific service.', ['platform' => $activePlatformName]) }}</p>

                        <div class="flex flex-wrap items-center justify-center gap-3">
                            <a href="{{ route('services.index') }}"
                               class="inline-flex items-center gap-3 px-8 py-4 font-black text-white bg-[#1FA7A2] border-2 border-[#1FA7A2] rounded-2xl hover:bg-[#1a5254] hover:border-[#1a5254] hover:shadow-xl hover:shadow-[#1FA7A2]/20 transition-all duration-300 transform hover:-translate-y-1 focus:outline-none">
                                <i class="fas fa-th-large" aria-hidden="true"></i>
                                {{ __('Browse all services') }}
                            </a>
                            <a href="{{ route('contact.index') }}"
                               class="inline-flex items-center gap-3 px-8 py-4 font-black text-[#1FA7A2] bg-white border-2 border-[#1FA7A2]/10 rounded-2xl hover:bg-[#1FA7A2] hover:text-white hover:border-[#1FA7A2] hover:shadow-xl hover:shadow-[#1FA7A2]/20 transition-all duration-300 transform hover:-translate-y-1 focus:outline-none">
                                <i class="fas fa-envelope" aria-hidden="true"></i>
                                {{ __('Contact our team') }}
                            </a>
                        </div>
                    @else
                        <h3 class="text-3xl font-black text-slate-800 mb-3">{{ __('No services found') }}</h3>
                        <p class="text-slate-500 text-lg font-medium mb-8 max-w-md mx-auto">{{ __('We couldn\'t find any services matching your criteria. Try adjusting your filters or search term.') }}</p>

                        <button
                            wire:click="$set('search', '')"
                            class="inline-flex items-center gap-3 px-8 py-4 font-black text-[#1FA7A2] bg-white border-2 border-[#1FA7A2]/10 rounded-2xl hover:bg-[#1FA7A2] hover:text-white hover:border-[#1FA7A2] hover:shadow-xl hover:shadow-[#1FA7A2]/20 transition-all duration-300 transform hover:-translate-y-1 focus:outline-none"
                        >
                            <i class="fas fa-redo rtl:-scale-x-100" aria-hidden="true"></i>
                            {{ __('Reset Search') }}
                        </button>
                    @endif
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="mt-16 flex justify-center w-full relative z-30">
            {{ $services->links() }}
        </div>

    </div>
</div>
