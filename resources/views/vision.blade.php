@extends('layouts.app')

@php
    $isAr = app()->getLocale() === 'ar';

    $pageTitle = __('Our Vision | Amr 7 Business Solutions');
    $pageDesc  = __('Discover the vision of Amr 7 Business Solutions in building a more organized, compliant, and sustainable future for businesses in Saudi Arabia.');

    \Artesaos\SEOTools\Facades\SEOMeta::setTitle($pageTitle, false);
    \Artesaos\SEOTools\Facades\SEOMeta::setDescription($pageDesc);
    \Artesaos\SEOTools\Facades\SEOMeta::setCanonical(url()->current());
    \Artesaos\SEOTools\Facades\OpenGraph::setUrl(url()->current());
    \Artesaos\SEOTools\Facades\OpenGraph::addProperty('type', 'website');
    \Artesaos\SEOTools\Facades\OpenGraph::addImage(asset('brand/amr7/amr7-og-image-1200x630.png'));
@endphp

@push('head')
    @php
        $visionSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => $pageTitle,
            'url' => url()->current(),
            'description' => $pageDesc,
        ];
    @endphp

    <script type="application/ld+json">
        {!! json_encode($visionSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>

    <style>
        .reveal-elem {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s cubic-bezier(0.5, 0, 0, 1);
        }

        .reveal-elem.active {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
@endpush

@section('content')
<div class="font-['Tajawal']" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
    <section class="relative pt-32 pb-20 lg:py-32 overflow-hidden bg-slate-50">
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute top-0 end-0 w-[600px] h-[600px] bg-[#1FA7A2]/5 blur-[100px] rounded-full mix-blend-multiply"></div>
            <div class="absolute bottom-0 start-0 w-[600px] h-[600px] bg-amber-500/5 blur-[100px] rounded-full mix-blend-multiply"></div>
        </div>

        <div class="container mx-auto px-4 relative z-10 text-center reveal-elem">
            <div class="inline-flex items-center gap-2 bg-white text-[#1FA7A2] px-6 py-2.5 rounded-full font-bold text-sm shadow-md border border-teal-50 mb-8 hover:scale-105 transition-transform cursor-default">
                <i class="fas fa-eye animate-pulse"></i> {{ __('Our Vision') }}
            </div>

            <h1 class="text-4xl md:text-6xl lg:text-7xl font-black text-slate-900 mb-8 leading-[1.2]">
                {{ __('Our Vision For The') }}
                <span class="relative inline-block text-transparent bg-clip-text bg-gradient-to-r from-[#1FA7A2] to-teal-400">
                    {{ __('Future of Business') }}
                    <svg class="absolute w-full h-3 -bottom-2 left-0 text-[#1FA7A2]/20 -z-10" viewBox="0 0 100 10" preserveAspectRatio="none">
                        <path d="M0 5 Q 50 10 100 5" stroke="currentColor" stroke-width="8" fill="none"></path>
                    </svg>
                </span>
                <br>
                {{ __('in Saudi Arabia') }}
            </h1>

            <p class="text-lg md:text-xl text-slate-500 max-w-3xl mx-auto leading-relaxed font-medium">
                {{ __('At Amr 7, we believe that corporate success doesn’t just start with formation, but with building an organized, compliant entity capable of growing with confidence.') }}
            </p>
        </div>
    </section>

    <section class="py-16 bg-white relative overflow-hidden">
        <div class="container mx-auto px-4">
            <div class="bg-gradient-to-br from-white to-slate-50/50 rounded-[2.5rem] p-8 md:p-12 shadow-[0_20px_50px_-12px_rgba(0,0,0,0.05)] border border-slate-100 reveal-elem">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                    <div class="lg:text-start text-center">
                        <div class="flex items-center justify-center lg:justify-start gap-3 mb-6">
                            <span class="w-10 h-1 bg-gradient-to-r from-[#1FA7A2] to-teal-300 rounded-full"></span>
                            <h6 class="text-[#1FA7A2] font-black uppercase tracking-widest text-sm">{{ __('Amr 7 Vision') }}</h6>
                        </div>

                        <h2 class="text-3xl md:text-4xl font-black text-slate-900 mb-6 leading-tight">
                            {{ __('Building a future that is more') }} <br>
                            <span class="text-[#1FA7A2]">{{ __('Organized & Sustainable') }}</span>
                        </h2>

                        <p class="text-slate-500 text-lg leading-relaxed mb-10 text-justify">
                            {{ __('We seek to redefine the business services experience in the Kingdom by providing a professional model that combines speed, accuracy, and a deep understanding of regulations.') }}
                        </p>

                        <div class="grid grid-cols-3 gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                            <div class="text-center border-e border-slate-100 rtl:border-l rtl:border-r-0 last:border-0 hover:-translate-y-1 transition-transform">
                                <h3 class="text-2xl md:text-3xl font-black text-[#1FA7A2]">{{ __('Clarity') }}</h3>
                                <p class="text-xs font-bold text-slate-400 mt-2">{{ __('In Procedures') }}</p>
                            </div>
                            <div class="text-center border-e border-slate-100 rtl:border-l rtl:border-r-0 last:border-0 hover:-translate-y-1 transition-transform">
                                <h3 class="text-2xl md:text-3xl font-black text-[#1FA7A2]">{{ __('Trust') }}</h3>
                                <p class="text-xs font-bold text-slate-400 mt-2">{{ __('In Execution') }}</p>
                            </div>
                            <div class="text-center hover:-translate-y-1 transition-transform">
                                <h3 class="text-2xl md:text-3xl font-black text-[#1FA7A2]">{{ __('Sustainability') }}</h3>
                                <p class="text-xs font-bold text-slate-400 mt-2">{{ __('In Growth') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="relative group">
                        <div class="absolute inset-0 bg-gradient-to-tr from-[#1FA7A2] to-teal-400 rounded-3xl rotate-3 opacity-20 group-hover:rotate-6 transition-transform duration-500"></div>
                        <img src="{{ asset('brand/amr7/amr7-og-image-1200x630.png') }}"
                             loading="lazy"
                             class="relative rounded-3xl shadow-2xl w-full h-[500px] object-cover transform transition-all duration-700 group-hover:-translate-y-2 group-hover:scale-[1.02]"
                             alt="{{ app()->getLocale() === 'ar' ? 'اجتماع عمل يعبّر عن رؤية آمر سبعة' : 'Business meeting representing the Amr 7 vision' }}">
                        
                        <div class="absolute bottom-8 rtl:-right-8 ltr:-left-8 bg-white/90 backdrop-blur-sm p-4 rounded-2xl shadow-xl border border-white flex items-center gap-4 animate-bounce hover:animate-none">
                            <div class="w-12 h-12 bg-amber-500 text-white rounded-full flex items-center justify-center text-xl">
                                <i class="fas fa-rocket"></i>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 font-bold mb-1">{{ __('Towards') }}</p>
                                <p class="text-sm font-black text-slate-800">{{ __('The Top Always') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-20 bg-slate-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16 reveal-elem">
                <h2 class="text-3xl md:text-4xl font-black text-slate-900 mb-4">{{ __('Our Core Pillars') }}</h2>
                <p class="text-slate-500 font-medium">{{ __('The foundations upon which we build our future') }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @php
                    $pillars = [
                        ['icon' => 'fa-crown', 'title' => __('Leadership'), 'desc' => __('To be one of the most influential and trusted entities in the business services sector within the Kingdom.'), 'delay' => '0ms'],
                        ['icon' => 'fa-briefcase', 'title' => __('Empowerment'), 'desc' => __('Empowering entrepreneurs and enterprises to launch with highly efficient legal and operational structures.'), 'delay' => '100ms'],
                        ['icon' => 'fa-microchip', 'title' => __('Smart Transformation'), 'desc' => __('Developing the business services experience through digital solutions that are faster, transparent, and user-friendly.'), 'delay' => '200ms'],
                        ['icon' => 'fa-seedling', 'title' => __('Sustainability'), 'desc' => __('Building long-term relationships with clients based on quality, commitment, and reliable results.'), 'delay' => '300ms'],
                    ];
                @endphp

                @foreach($pillars as $pillar)
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 hover:border-[#1FA7A2]/30 hover:shadow-xl hover:-translate-y-3 transition-all duration-500 group reveal-elem" style="transition-delay: {{ $pillar['delay'] }};">
                    <div class="w-16 h-16 bg-slate-50 group-hover:bg-[#1FA7A2] text-[#1FA7A2] group-hover:text-white rounded-2xl flex items-center justify-center text-2xl mb-6 mx-auto transition-colors duration-500 shadow-sm">
                        <i class="fas {{ $pillar['icon'] }}"></i>
                    </div>
                    <h4 class="text-xl font-bold text-slate-900 mb-4 text-center">{{ $pillar['title'] }}</h4>
                    <p class="text-slate-500 text-sm text-center leading-relaxed">{{ $pillar['desc'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="py-24 bg-white overflow-hidden">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-16 items-center">
                <div class="lg:col-span-5 reveal-elem text-center lg:text-start">
                    <h2 class="text-3xl md:text-5xl font-black text-slate-900 mb-6 leading-tight">
                        {{ __('How We Achieve') }} <br>
                        <span class="text-[#1FA7A2]">{{ __('This Vision') }}</span>
                    </h2>
                    <p class="text-lg text-slate-500 mb-10 leading-relaxed">
                        {{ __('We turn vision into reality through a professional business model combining regulatory knowledge and rapid execution.') }}
                    </p>
                    <a href="{{ route('services.index') }}" class="inline-flex items-center gap-4 bg-slate-900 hover:bg-[#1FA7A2] text-white font-bold py-4 px-8 rounded-2xl shadow-xl hover:shadow-2xl hover:shadow-[#1FA7A2]/30 transition-all duration-300 group">
                        {{ __('Explore Our Services') }}
                        <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center transition-transform group-hover:translate-x-1 rtl:group-hover:-translate-x-1">
                            <i class="fas fa-arrow-{{ $isAr ? 'left' : 'right' }} text-sm"></i>
                        </div>
                    </a>
                </div>

                <div class="lg:col-span-7">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 relative">
                        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[300px] h-[300px] bg-[#1FA7A2]/5 rounded-full blur-3xl -z-10"></div>

                        @php
                            $features = [
                                ['icon' => 'fa-scale-balanced', 'title' => __('Deep Regulatory Understanding'), 'desc' => __('We operate with a genuine understanding of government regulations and procedures.'), 'mt' => 'sm:mt-0'],
                                ['icon' => 'fa-gears', 'title' => __('Professional Execution'), 'desc' => __('We transform needs into clear steps and precise follow-up.'), 'mt' => 'sm:mt-12'],
                                ['icon' => 'fa-users', 'title' => __('Clearer Customer Experience'), 'desc' => __('We provide service built on communication and accessibility.'), 'mt' => 'sm:mt-0'],
                                ['icon' => 'fa-chart-line', 'title' => __('Continuous Development'), 'desc' => __('We develop our services to keep pace with the growth of the Saudi market.'), 'mt' => 'sm:mt-12'],
                            ];
                        @endphp

                        @foreach($features as $feat)
                        <div class="bg-white/80 backdrop-blur-lg p-8 rounded-3xl border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-xl hover:border-[#1FA7A2]/30 transition-all duration-500 reveal-elem {{ $feat['mt'] }}">
                            <div class="w-14 h-14 bg-gradient-to-br from-teal-50 to-slate-50 text-[#1FA7A2] rounded-2xl flex items-center justify-center text-xl shadow-inner border border-white mb-6">
                                <i class="fas {{ $feat['icon'] }}"></i>
                            </div>
                            <h5 class="font-bold text-lg text-slate-900 mb-3">{{ $feat['title'] }}</h5>
                            <p class="text-slate-500 text-sm leading-relaxed">{{ $feat['desc'] }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const reveals = document.querySelectorAll(".reveal-elem");
        
        const revealOptions = {
            threshold: 0.1,
            rootMargin: "0px 0px -50px 0px"
        };

        const revealOnScroll = new IntersectionObserver(function(entries, observer) {
            entries.forEach(entry => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add("active");
                observer.unobserve(entry.target);
            });
        }, revealOptions);

        reveals.forEach(reveal => {
            revealOnScroll.observe(reveal);
        });
    });
</script>
@endpush
