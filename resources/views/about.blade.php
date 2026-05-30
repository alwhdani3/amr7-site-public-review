@extends('layouts.app')

@php
    $locale = app()->getLocale();
    $isAr   = $locale === 'ar';

    if (class_exists(\Artesaos\SEOTools\Facades\SEOTools::class)) {
        $title = __('about.seo_title');
        $description = __('about.seo_description');

        $canonical = url($isAr ? '/about' : '/en/about');

        \Artesaos\SEOTools\Facades\SEOTools::setTitle($title);
        \Artesaos\SEOTools\Facades\SEOTools::setDescription($description);
        \Artesaos\SEOTools\Facades\SEOTools::setCanonical($canonical);
        \Artesaos\SEOTools\Facades\SEOTools::opengraph()->setTitle($title);
        \Artesaos\SEOTools\Facades\SEOTools::opengraph()->setDescription($description);
        \Artesaos\SEOTools\Facades\SEOTools::opengraph()->setUrl($canonical);
        \Artesaos\SEOTools\Facades\SEOTools::opengraph()->addProperty('type', 'website');
        \Artesaos\SEOTools\Facades\SEOTools::opengraph()->addProperty('locale', $isAr ? 'ar_SA' : 'en_US');
        \Artesaos\SEOTools\Facades\SEOTools::opengraph()->addImage(asset('brand/amr7/amr7-og-image-1200x630.png'));
        \Artesaos\SEOTools\Facades\SEOTools::twitter()->setTitle($title);
        \Artesaos\SEOTools\Facades\SEOTools::twitter()->setDescription($description);
        \Artesaos\SEOTools\Facades\SEOTools::metatags()->setKeywords($isAr
            ? ['شركة آمر سبعة لحلول الأعمال', 'آمر سبعة', 'من نحن', 'تأسيس شركات السعودية', 'حوكمة', 'امتثال', 'الرياض']
            : ['Amr Seven Business Solutions', 'Amr 7', 'about us', 'company formation Saudi Arabia', 'governance', 'compliance', 'Riyadh']
        );
    }

    // Build the Organization schema as a PHP array so Blade never touches
    // its "@context"/"@type" keys. Renders below via json_encode — same
    // pattern as faq/vision/contact/services blades.
    $organizationSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => $isAr ? 'شركة آمر سبعة لحلول الأعمال' : 'Amr Seven Business Solutions',
        'url' => url('/'),
        'logo' => asset('brand/amr7/amr7-logo-lockup-light.png'),
        'description' => $isAr
            ? 'خدمات تأسيس الشركات والامتثال والحوكمة في السعودية'
            : 'Company formation, compliance and governance services in Saudi Arabia',
        'address' => [
            '@type' => 'PostalAddress',
            'addressLocality' => $isAr ? 'الرياض' : 'Riyadh',
            'addressCountry' => 'SA',
        ],
        'contactPoint' => [
            '@type' => 'ContactPoint',
            'telephone' => '+966-920017083',
            'contactType' => 'customer service',
        ],
    ];
@endphp

@push('head')
<script type="application/ld+json">{!! json_encode($organizationSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endpush

@section('content')
    {{-- إضافة x-data و x-init لضمان تشغيل السكربت مع Livewire --}}
    <div x-data="aboutPageAnimations" x-init="initPage()" class="min-h-screen bg-slate-50 font-['Tajawal'] pt-24 overflow-x-hidden" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
        
        {{-- Hero Section --}}
        <section class="relative py-20 lg:py-32 overflow-hidden">
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-[#1FA7A2]/5 blur-[100px] rounded-full mix-blend-multiply"></div>
                <div class="absolute bottom-0 left-0 w-[600px] h-[600px] bg-amber-500/5 blur-[100px] rounded-full mix-blend-multiply"></div>
            </div>

            <div class="container mx-auto px-4 relative z-10 text-center reveal opacity-0 translate-y-8 transition-all duration-1000">
                <div class="inline-flex items-center gap-2 bg-[#f0fdfa] text-[#1FA7A2] px-6 py-2 rounded-full font-bold text-sm shadow-sm border border-teal-100 mb-6">
                    <i class="fas fa-shield-alt"></i> {{ __('شركة آمر سبعة لحلول الأعمال') }}
                </div>

                <h1 class="text-4xl md:text-6xl font-black text-slate-900 mb-6 leading-tight">
                    {{ __('نبني') }} 
                    <span class="relative inline-block text-[#1FA7A2]">
                        {{ __('هويتك القانونية') }}
                        <svg class="absolute w-full h-3 -bottom-1 left-0 text-teal-200 -z-10 opacity-40" viewBox="0 0 100 10" preserveAspectRatio="none"><path d="M0 5 Q 50 10 100 5" stroke="currentColor" stroke-width="8" fill="none"></path></svg>
                    </span> 
                    {{ __('في السعودية') }}
                </h1>
                
                <p class="text-lg md:text-xl text-slate-500 max-w-3xl mx-auto leading-relaxed">
                    {{ __('في آمر سبعة، لا نكتفي باستخراج السجلات التجارية فحسب؛ بل نصمم هيكلاً قانونياً متكاملاً وممتثلاً للأنظمة، ومتوافقاً مع رؤية 2030 — من الرياض إلى كافة مناطق المملكة.') }}
                </p>
            </div>
        </section>

        {{-- Story Section --}}
        <section class="py-16 bg-white relative">
            <div class="container mx-auto px-4">
                <div class="bg-white rounded-[2.5rem] p-8 md:p-12 shadow-2xl shadow-slate-200/50 border border-slate-100 reveal opacity-0 translate-y-8 transition-all duration-1000 delay-200">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                        <div class="{{ app()->getLocale() == 'ar' ? 'lg:text-right' : 'lg:text-left' }}">
                            <div class="flex items-center gap-3 mb-4">
                                <span class="w-10 h-1 bg-[#1FA7A2] rounded-full"></span>
                                <h6 class="text-[#1FA7A2] font-bold uppercase tracking-wider text-sm">{{ __('قصتنا') }}</h6>
                            </div>
                            <h2 class="text-3xl md:text-4xl font-black text-slate-900 mb-6">
                                {{ __('خبرة تُترجم إلى') }} 
                                <span class="text-[#1FA7A2]">{{ __('نتائج ملموسة') }}</span>
                            </h2>
                            <div class="text-slate-500 text-lg leading-relaxed mb-8 text-justify">
                                {{ __('في آمر سبعة نساعد روّاد الأعمال والشركات على بناء كيان قانوني قوي من البداية. خبرتنا العملية في التأسيس والحوكمة والامتثال تختصر عليك الوقت وتقلّل المخاطر، ونمشي معك خطوة بخطوة من اختيار الكيان إلى اكتمال الإجراءات والتوثيق—باحترافية ووضوح.') }}
                            </div>

                            {{-- Stats --}}
                            <div class="grid grid-cols-3 gap-4 bg-slate-50 p-6 rounded-2xl border border-slate-100">
                                <div class="text-center border-e border-slate-200 rtl:border-l rtl:border-r-0 last:border-0">
                                    <h3 class="text-2xl md:text-3xl font-black text-[#1FA7A2] counter-text" data-target="500">500</h3>
                                    <p class="text-xs font-bold text-slate-400 mt-1">{{ __('شركة مؤسسة') }}</p>
                                </div>
                                <div class="text-center border-e border-slate-200 rtl:border-l rtl:border-r-0 last:border-0">
                                    <h3 class="text-2xl md:text-3xl font-black text-[#1FA7A2] counter-text" data-target="11000">11,000</h3>
                                    <p class="text-xs font-bold text-slate-400 mt-1">{{ __('عميل') }}</p>
                                </div>
                                <div class="text-center">
                                    <h3 class="text-2xl md:text-3xl font-black text-[#1FA7A2] counter-text" data-target="15000">15,000</h3>
                                    <p class="text-xs font-bold text-slate-400 mt-1">{{ __('معاملة') }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="relative group">
                            <div class="absolute inset-0 bg-[#1FA7A2] rounded-3xl rotate-3 opacity-10 group-hover:rotate-6 transition-transform duration-500"></div>
                            <img src="images/about.svg" 
                                 class="relative rounded-3xl shadow-lg w-full h-auto object-cover transform transition-transform duration-500 group-hover:-translate-y-2" 
                                 alt="{{ app()->getLocale() === 'ar' ? 'فريق آمر سبعة في بيئة عمل مكتبية' : 'Amr 7 team in an office setting' }}">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Values Section --}}
        <section class="py-20 bg-slate-50">
            <div class="container mx-auto px-4">
                <div class="text-center mb-16 reveal opacity-0 translate-y-8 transition-all duration-1000">
                    <h2 class="text-3xl font-black text-slate-900 mb-3">{{ __('محركنا الأساسي') }}</h2>
                    <p class="text-slate-500">{{ __('المبادئ التي تقودنا نحو التميز') }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    {{-- Vision --}}
                    <div class="bg-white p-8 rounded-3xl shadow-sm border-b-4 border-[#1FA7A2] hover:-translate-y-2 transition-all duration-300 reveal opacity-0 translate-y-8 delay-100">
                        <div class="w-16 h-16 bg-teal-50 text-[#1FA7A2] rounded-2xl flex items-center justify-center text-2xl mb-6 mx-auto">
                            <i class="fas fa-eye"></i>
                        </div>
                        <h4 class="text-xl font-bold text-slate-900 mb-3 text-center">{{ __('رؤيتنا') }}</h4>
                        <p class="text-slate-500 text-sm text-center leading-relaxed">{{ __('أن نكون الشريك الأكثر موثوقية في المملكة لبناء كيانات قانونية ممتثلة ومهيكلة بعناية.') }}</p>
                    </div>

                    {{-- Mission (Highlighted) --}}
                    <div class="bg-[#1FA7A2] p-8 rounded-3xl shadow-xl transform md:-translate-y-4 hover:-translate-y-6 transition-all duration-300 reveal opacity-0 translate-y-8 delay-200">
                        <div class="w-16 h-16 bg-white/10 text-white rounded-2xl flex items-center justify-center text-2xl mb-6 mx-auto backdrop-blur-sm">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <h4 class="text-xl font-bold text-white mb-3 text-center">{{ __('رسالتنا') }}</h4>
                        <p class="text-teal-100 text-sm text-center leading-relaxed">{{ __('تقديم خدمات تأسيس وامتثال وحوكمة وصياغة عقود بمعايير عالية، عبر إجراءات واضحة وتجربة عميل سلسة.') }}</p>
                    </div>

                    {{-- Values --}}
                    <div class="bg-white p-8 rounded-3xl shadow-sm border-b-4 border-[#1FA7A2] hover:-translate-y-2 transition-all duration-300 reveal opacity-0 translate-y-8 delay-300">
                        <div class="w-16 h-16 bg-teal-50 text-[#1FA7A2] rounded-2xl flex items-center justify-center text-2xl mb-6 mx-auto">
                            <i class="fas fa-gem"></i>
                        </div>
                        <h4 class="text-xl font-bold text-slate-900 mb-3 text-center">{{ __('قيمنا') }}</h4>
                        <p class="text-slate-500 text-sm text-center leading-relaxed">{{ __('الدقة القانونية، الشفافية، السرعة المنضبطة، الالتزام بالأنظمة، وحماية مصالح العميل.') }}</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Why Us Section --}}
        <section class="py-20 bg-white">
            <div class="container mx-auto px-4">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                    
                    {{-- Text Content --}}
                    <div class="lg:col-span-5 reveal opacity-0 translate-y-8 transition-all duration-1000">
                        <h2 class="text-3xl md:text-4xl font-black text-slate-900 mb-6 leading-tight">
                            {{ __('ليش تختار') }} 
                            <span class="text-[#1FA7A2]">{{ __('آمر سبعة') }}</span>
                        </h2>
                        <p class="text-lg text-slate-500 mb-8 leading-relaxed">
                            {{ __('لأننا لا ننجز معاملة فقط—بل نبني لك هيكلًا قانونيًا متكاملًا يقلل المخاطر ويعزز الاستدامة، مع متابعة دقيقة حتى اكتمال كل خطوة.') }}
                        </p>
                        <a href="{{ route('services.index') }}" class="inline-flex items-center gap-3 bg-[#1FA7A2] hover:bg-[#167F7B] text-white font-bold py-4 px-8 rounded-full shadow-lg hover:shadow-xl transition-all duration-300 group">
                            {{ __('تصفح خدماتنا') }}
                            <i class="fas fa-arrow-left transition-transform group-hover:-translate-x-1 rtl:rotate-0 ltr:rotate-180"></i>
                        </a>
                    </div>

                    {{-- Features Grid --}}
                    <div class="lg:col-span-7">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @php
                                $features = [
                                    ['icon' => 'fa-check', 'title' => 'استشارة سريعة وتنفيذ واضح', 'desc' => 'نفهم احتياجك بسرعة ونحوّله لخطة عمل قانونية بخطوات محددة.'],
                                    ['icon' => 'fa-clock', 'title' => 'امتثال وحوكمة', 'desc' => 'نضبط عقود التأسيس والصلاحيات والهيكلة بما يتوافق مع الأنظمة.'],
                                    ['icon' => 'fa-file-contract', 'title' => 'تأسيس يناسب نشاطك', 'desc' => 'نوصي بالكيان الأنسب (مؤسسة/شركة) ونبني هيكل الشراكة.'],
                                    ['icon' => 'fa-user-shield', 'title' => 'متابعة كاملة', 'desc' => 'نتابع معك من بداية الطلب إلى اكتمال الإجراءات والتوثيق.']
                                ];
                            @endphp
                            @foreach($features as $index => $feat)
                                <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100 hover:bg-white hover:shadow-lg hover:border-[#1FA7A2]/20 transition-all duration-300 reveal opacity-0 translate-y-8 delay-{{ ($index+1)*100 }}">
                                    <div class="w-12 h-12 bg-white text-[#1FA7A2] rounded-xl flex items-center justify-center text-xl shadow-sm mb-4">
                                        <i class="fas {{ $feat['icon'] }}"></i>
                                    </div>
                                    <h5 class="font-bold text-slate-900 mb-2">{{ $feat['title'] }}</h5>
                                    <p class="text-slate-500 text-sm leading-relaxed">{{ $feat['desc'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>
            </div>
        </section>
    </div>

    {{-- Javascript (Alpine Logic) --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('aboutPageAnimations', () => ({
                initPage() {
                    // تشغيل الأنميشن فوراً (في حال كانت الصفحة محملة مسبقاً)
                    setTimeout(() => {
                        this.startAnimations();
                    }, 100);

                    // إعادة التشغيل عند التنقل بـ Livewire
                    document.addEventListener('livewire:navigated', () => {
                        this.startAnimations();
                    });
                },

                startAnimations() {
                    // Reveal Animation
                    const observer = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                entry.target.classList.remove('opacity-0', 'translate-y-8');
                                entry.target.classList.add('opacity-100', 'translate-y-0');
                                observer.unobserve(entry.target); // Stop observing once revealed
                            }
                        });
                    }, { threshold: 0.1 });
                    
                    document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

                    // Counters
                    const counterObserver = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                const el = entry.target;
                                const target = parseInt(el.getAttribute('data-target'));
                                let count = 0;
                                const duration = 2000;
                                const increment = target / (duration / 16);
                                
                                const timer = setInterval(() => {
                                    count += increment;
                                    if (count >= target) {
                                        el.innerText = "+" + target.toLocaleString();
                                        clearInterval(timer);
                                    } else {
                                        el.innerText = "+" + Math.ceil(count).toLocaleString();
                                    }
                                }, 16);
                                counterObserver.unobserve(el);
                            }
                        });
                    }, { threshold: 0.5 });

                    document.querySelectorAll('.counter-text').forEach(el => counterObserver.observe(el));
                }
            }));
        });
    </script>
@endsection
