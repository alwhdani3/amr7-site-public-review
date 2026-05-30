<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}"
      class="scroll-smooth">
<head>
    @include('partials.head')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')

    {{-- Phase F: Organization + WebSite JSON-LD (يظهر فقط على الصفحات العامة، dashboard noindex بـ NoIndexSensitivePages middleware) --}}
    @unless(request()->is('dashboard*') || request()->is('amr7*') || request()->is('login*') || request()->is('register*'))
    @php
        $schemaJsonLd = [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'Organization',
                    '@id'   => url('/#organization'),
                    'name'  => 'شركة آمر سبعة لحلول الأعمال',
                    'alternateName' => 'Amr Seven Business Solutions',
                    'url'   => url('/'),
                    'logo'  => asset('brand/amr7/amr7-logo-lockup-light.png'),
                    'sameAs' => array_values(array_filter([
                        config('amr7.social.linkedin'),
                        config('amr7.social.x'),
                        config('amr7.social.instagram'),
                    ])),
                    'contactPoint' => [
                        '@type' => 'ContactPoint',
                        'telephone' => config('amr7.contact.phone', '920017083'),
                        'contactType' => 'customer service',
                        'areaServed' => 'SA',
                        'availableLanguage' => ['Arabic', 'English'],
                    ],
                ],
                [
                    '@type' => 'WebSite',
                    '@id'   => url('/#website'),
                    'url'   => url('/'),
                    'name'  => 'شركة آمر سبعة لحلول الأعمال',
                    'inLanguage' => app()->getLocale() === 'ar' ? 'ar-SA' : 'en',
                    'publisher' => ['@id' => url('/#organization')],
                ],
            ],
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($schemaJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @endunless

@production
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}

        window.__amr7LoadAnalytics = function () {
            if (window.__amr7AnalyticsLoaded) return;
            window.__amr7AnalyticsLoaded = true;

            const s = document.createElement('script');
            s.async = true;
            s.src = 'https://www.googletagmanager.com/gtag/js?id=G-ZD71ZL3JE3';
            document.head.appendChild(s);

            gtag('js', new Date());

            gtag('config', 'G-ZD71ZL3JE3', {
                page_path: window.location.pathname + window.location.search,
                page_title: document.title
            });

            gtag('config', 'AW-17133949830', {
                page_path: window.location.pathname + window.location.search,
                page_title: document.title
            });
        };

        window.addEventListener('load', function () {
            setTimeout(function () {
                window.__amr7LoadAnalytics();
            }, 2500);
        });

        document.addEventListener('click', function () {
            window.__amr7LoadAnalytics();
        }, { once: true });

        document.addEventListener('scroll', function () {
            window.__amr7LoadAnalytics();
        }, { once: true, passive: true });

        document.addEventListener('livewire:navigated', () => {
            if (window.__amr7AnalyticsLoaded && typeof gtag !== 'undefined') {
                gtag('config', 'G-ZD71ZL3JE3', {
                    page_path: window.location.pathname + window.location.search,
                    page_title: document.title
                });

                gtag('config', 'AW-17133949830', {
                    page_path: window.location.pathname + window.location.search,
                    page_title: document.title
                });
            }
        });
    </script>
@endproduction
</head>

<body class="antialiased min-h-screen flex flex-col bg-slate-50 text-slate-900 font-tajawal overflow-x-hidden">

    @php
        $isDashboard = request()->is('dashboard') || request()->is('dashboard/*') || request()->is('en/dashboard*');
    @endphp

    @if($isDashboard)
        {{-- Portal Shell: Sidebar (desktop fixed / mobile drawer) + Topbar + Main + Footer.
             لا روابط جديدة، لا تغيير في auth — كل الـnavigation تذهب إلى /dashboard?section=xxx
             ومزامنة مع Livewire #[Url] على Dashboard::$section. --}}
        @php
            $authedUser = auth()->user();
            $primaryCompany = $authedUser && method_exists($authedUser, 'primaryCompany') ? $authedUser->primaryCompany() : null;
            $isBackofficeViewer = $authedUser && method_exists($authedUser, 'hasBackofficeAccess') && $authedUser->hasBackofficeAccess();
            $activeSection = (string) request()->query('section', 'home');
            $dashboardUrl = route('dashboard');
            // Phase 6: sidebar مقسّم إلى مجموعتين (الإدارة / الخدمات) لتقليل التشتت البصري
            $navGroups = [
                [
                    'label' => __('nav_group_management') ?: 'الإدارة',
                    'items' => [
                        ['key' => 'home',    'label' => __('nav_dashboard_home')  ?: 'الرئيسية',      'icon' => 'fa-house'],
                        ['key' => 'profile', 'label' => __('nav_company_profile') ?: 'ملف المنشأة',   'icon' => 'fa-building'],
                        ['key' => 'users',   'label' => __('nav_company_users')   ?: 'المستخدمون',    'icon' => 'fa-users-cog'],
                    ],
                ],
                [
                    'label' => __('nav_group_services') ?: 'الخدمات',
                    'items' => [
                        ['key' => 'files',      'label' => __('nav_documents_files')    ?: 'الوثائق والملفات',  'icon' => 'fa-folder-open'],
                        ['key' => 'compliance', 'label' => __('nav_compliance')         ?: 'الامتثال',           'icon' => 'fa-check-double'],
                        ['key' => 'requests',   'label' => __('nav_request_service')    ?: 'طلب خدمة',           'icon' => 'fa-clipboard-list'],
                        ['key' => 'request-history', 'label' => __('nav_request_history') ?: 'سجل الطلبات',      'icon' => 'fa-list-check'],
                        // Phase 7: "My package" section — read-only view of the customer's
                        // current subscription / package details.
                        ['key' => 'subscription', 'label' => __('nav_my_package') === 'nav_my_package' ? 'باقتي' : __('nav_my_package'), 'icon' => 'fa-box'],
                        ['key' => 'financial',  'label' => __('nav_financial_requests') ?: 'القوائم المالية',    'icon' => 'fa-chart-line'],
                        ['key' => 'invoices',   'label' => __('nav_invoices')           ?: 'الفواتير',           'icon' => 'fa-file-invoice-dollar'],
                        ['key' => 'tickets',    'label' => __('nav_support_tickets')    ?: 'تذاكر الدعم',        'icon' => 'fa-headset'],
                    ],
                ],
            ];
            if ($isBackofficeViewer) {
                $navGroups[] = [
                    'label' => __('nav_group_backoffice') ?: 'أدوات المراجعة',
                    'items' => [
                        ['key' => 'ai-review', 'label' => __('nav_ai_review') ?: 'مراجعة الذكاء الاصطناعي', 'icon' => 'fa-robot'],
                    ],
                ];
            }

            // Phase B (UI): filter sidebar items by the per-company
            // permissions matrix. admin / owner always pass via
            // canAccessCompanySection (their `effective()` is fullMatrix).
            // Employees see only sections their stored matrix grants. Empty
            // groups (every item filtered out) are dropped so the sidebar
            // stays compact. `ai-review` keeps its own backoffice gating.
            if ($authedUser && method_exists($authedUser, 'canAccessCompanySection')) {
                $activeCompanyId = (int) (session('active_company_id') ?? 0);
                $filtered = [];
                foreach ($navGroups as $group) {
                    $allowedItems = [];
                    foreach ($group['items'] as $item) {
                        $key = $item['key'] ?? '';
                        if ($key === 'ai-review') {
                            // already gated by hasBackofficeAccess() above;
                            // keep as-is here.
                            $allowedItems[] = $item;
                            continue;
                        }
                        if ($authedUser->canAccessCompanySection($key, $activeCompanyId)) {
                            $allowedItems[] = $item;
                        }
                    }
                    if (! empty($allowedItems)) {
                        $group['items'] = $allowedItems;
                        $filtered[] = $group;
                    }
                }
                $navGroups = $filtered;
            }
        @endphp

        <div x-data="{ sidebarOpen: false }" class="flex-1 flex flex-col md:flex-row min-h-0 bg-[#F8FAFC]">

            {{-- Sidebar (desktop) --}}
            <aside class="hidden md:flex md:flex-col md:w-64 md:shrink-0 bg-white border-{{ app()->getLocale() === 'ar' ? 's' : 'e' }} border-[#E8ECEF] sticky top-0 h-screen overflow-y-auto z-30">
                <div class="px-5 py-5 border-b border-[#E8ECEF]">
                    <a href="{{ url('/') }}" class="flex items-center gap-2.5">
                        <img src="{{ asset('brand/amr7/amr7-logo-lockup-light.svg') }}" alt="{{ __('amr7_brand_short') ?: 'شركة آمر سبعة لحلول الأعمال' }}" class="h-9 w-auto" onerror="this.onerror=null;this.src='{{ asset('brand/amr7/amr7-logo-lockup-light.png') }}'">
                        <span class="text-[15px] font-black text-[#0A2540] tracking-tight">{{ __('amr7_brand_short') ?: 'شركة آمر سبعة لحلول الأعمال' }}</span>
                    </a>
                </div>

                <nav class="flex-1 px-3 py-4 space-y-4">
                    @foreach($navGroups as $group)
                        <div>
                            {{-- Phase 6: عنوان المجموعة الصغير — يساعد المسح البصري --}}
                            <div class="px-3 mb-1.5 text-[10px] font-black text-[#64748B] uppercase tracking-widest">
                                {{ $group['label'] }}
                            </div>
                            <div class="space-y-1">
                                @foreach($group['items'] as $item)
                                    @php $isActive = $activeSection === $item['key']; @endphp
                                    <a href="{{ $dashboardUrl }}?section={{ $item['key'] }}"
                                       wire:navigate
                                       class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13px] font-bold transition-all
                                              {{ $isActive
                                                  ? 'bg-[#0A2540] text-white shadow-md shadow-[#0A2540]/15'
                                                  : 'text-[#334155] hover:bg-[#E8ECEF]/45 hover:text-[#0A2540]' }}">
                                        <i class="fas {{ $item['icon'] }} text-[13px] w-4 text-center"></i>
                                        <span class="flex-1 truncate">{{ $item['label'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </nav>

                @auth
                    {{-- Phase 6: quick action أصغر — chip بدلًا من زر ضخم --}}
                    <div class="px-3 py-3 border-t border-[#E8ECEF]">
                        <a href="{{ $dashboardUrl }}?section=compliance"
                           wire:navigate
                           class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-[#1FA7A2]/10 text-[#0A2540] hover:bg-[#1FA7A2]/15 border border-[#1FA7A2]/20 font-bold text-[11px]">
                            <i class="fas fa-bolt text-[10px]"></i>
                            <span>{{ __('quick_action_label') ?: 'إجراء سريع: رفع وثيقة' }}</span>
                        </a>
                    </div>
                @endauth
            </aside>

            {{-- Sidebar (mobile drawer) --}}
            <div x-show="sidebarOpen" x-cloak
                 x-transition.opacity
                 class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-40 md:hidden"
                 x-on:click="sidebarOpen = false"></div>

            <aside x-show="sidebarOpen" x-cloak
                   x-transition:enter="transition ease-out duration-200"
                   x-transition:enter-start="opacity-0 {{ app()->getLocale() === 'ar' ? 'translate-x-full' : '-translate-x-full' }}"
                   x-transition:enter-end="opacity-100 translate-x-0"
                   x-transition:leave="transition ease-in duration-150"
                   x-transition:leave-start="opacity-100 translate-x-0"
                   x-transition:leave-end="opacity-0 {{ app()->getLocale() === 'ar' ? 'translate-x-full' : '-translate-x-full' }}"
                   class="fixed inset-y-0 {{ app()->getLocale() === 'ar' ? 'right-0' : 'left-0' }} w-72 bg-white z-50 md:hidden overflow-y-auto flex flex-col">
                <div class="px-5 py-5 border-b border-[#E8ECEF] flex items-center justify-between">
                    <a href="{{ url('/') }}" class="flex items-center gap-2.5">
                        <img src="{{ asset('brand/amr7/amr7-logo-lockup-light.svg') }}" alt="{{ __('amr7_brand_short') ?: 'شركة آمر سبعة لحلول الأعمال' }}" class="h-9 w-auto" onerror="this.onerror=null;this.src='{{ asset('brand/amr7/amr7-logo-lockup-light.png') }}'">
                        <span class="text-[15px] font-black text-[#0A2540]">{{ __('amr7_brand_short') ?: 'شركة آمر سبعة لحلول الأعمال' }}</span>
                    </a>
                    <button type="button" x-on:click="sidebarOpen = false"
                            class="w-9 h-9 rounded-xl text-slate-500 hover:bg-slate-50 inline-flex items-center justify-center"
                            aria-label="{{ __('menu_close') ?: 'إغلاق القائمة' }}">
                        <i class="fas fa-xmark"></i>
                    </button>
                </div>

                <nav class="flex-1 px-3 py-4 space-y-4">
                    @foreach($navGroups as $group)
                        <div>
                            <div class="px-3 mb-1.5 text-[10px] font-black text-[#64748B] uppercase tracking-widest">
                                {{ $group['label'] }}
                            </div>
                            <div class="space-y-1">
                                @foreach($group['items'] as $item)
                                    @php $isActive = $activeSection === $item['key']; @endphp
                                    <a href="{{ $dashboardUrl }}?section={{ $item['key'] }}"
                                       wire:navigate
                                       x-on:click="sidebarOpen = false"
                                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13px] font-bold transition-all
                                              {{ $isActive
                                                  ? 'bg-[#0A2540] text-white shadow-md shadow-[#0A2540]/15'
                                                  : 'text-[#334155] hover:bg-[#E8ECEF]/45 hover:text-[#0A2540]' }}">
                                        <i class="fas {{ $item['icon'] }} text-[13px] w-4 text-center"></i>
                                        <span class="flex-1 truncate">{{ $item['label'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </nav>
            </aside>

            {{-- Right column: Topbar + Main + Footer --}}
            <div class="flex-1 flex flex-col min-w-0">

                <header class="bg-white/95 border-b border-[#E8ECEF] sticky top-0 z-20 backdrop-blur-sm">
                    <div class="px-4 md:px-6 py-3 flex items-center justify-between gap-3">
                        {{-- Mobile: menu toggle + logo --}}
                        <div class="flex items-center gap-2 md:hidden min-w-0">
                            <button type="button" x-on:click="sidebarOpen = true"
                                    class="w-10 h-10 rounded-xl text-slate-600 hover:bg-slate-50 border border-slate-100 inline-flex items-center justify-center"
                                    aria-label="{{ __('menu_open') ?: 'فتح القائمة' }}">
                                <i class="fas fa-bars"></i>
                            </button>
                            <img src="{{ asset('brand/amr7/amr7-logo-lockup-light.svg') }}" alt="{{ __('amr7_brand_short') ?: 'شركة آمر سبعة لحلول الأعمال' }}" class="h-7 w-auto" onerror="this.onerror=null;this.src='{{ asset('brand/amr7/amr7-logo-lockup-light.png') }}'">
                        </div>

                        {{-- Desktop: page title placeholder (يظل فاضي ليتصرف فيه كل قسم) --}}
                        <div class="hidden md:flex items-center gap-2 min-w-0">
                            @if($primaryCompany)
                                <span class="text-[12px] text-slate-400 font-bold">{{ __('active_company') ?: 'الشركة النشطة' }}:</span>
                                <span class="text-[13px] font-black text-[#0A2540] truncate max-w-[260px]">{{ $primaryCompany->name }}</span>
                            @endif
                        </div>

                        {{-- Topbar right cluster: company switcher + support CTAs + lang + user + logout --}}
                        <div class="flex items-center gap-1.5 md:gap-2">
                            @auth
                                @if($primaryCompany)
                                    <a href="{{ route('company.select') }}"
                                       class="hidden lg:inline-flex items-center gap-2 px-3 py-2 rounded-xl text-[12px] font-bold text-slate-600 hover:bg-slate-50 border border-slate-100"
                                       title="{{ __('switch_company') ?: 'تبديل المنشأة' }}">
                                        <i class="fas fa-arrow-right-arrow-left text-[#1FA7A2] text-[11px]"></i>
                                        <span>{{ __('switch_company') ?: 'تبديل المنشأة' }}</span>
                                    </a>
                                @endif

                                {{-- Etmam-parity: Schedule consultation — opens WhatsApp with a preset message (no Calendly integration). --}}
                                @php
                                    $consultationWhatsapp = preg_replace('/\D/', '', (string) config('amr7.contact.whatsapp', '966505336956'));
                                    $consultationMessage = __('consultation_whatsapp_message') === 'consultation_whatsapp_message'
                                        ? 'السلام عليكم، أود جدولة استشارة مع آمر سبعة.'
                                        : __('consultation_whatsapp_message');
                                    $consultationUrl = $consultationWhatsapp
                                        ? 'https://wa.me/' . $consultationWhatsapp . '?text=' . rawurlencode($consultationMessage)
                                        : 'mailto:' . config('amr7.contact.email', 'info@amr-7.sa');
                                @endphp
                                <a href="{{ $consultationUrl }}"
                                   target="_blank" rel="noopener noreferrer"
                                   class="hidden lg:inline-flex items-center gap-2 px-3 py-2 rounded-xl text-[12px] font-bold text-[#1FA7A2] bg-[#1FA7A2]/8 hover:bg-[#1FA7A2]/15 border border-[#1FA7A2]/15"
                                   title="{{ __('Schedule Consultation') === 'Schedule Consultation' ? 'جدولة استشارة' : __('Schedule Consultation') }}">
                                    <i class="fas fa-calendar-check text-[11px]"></i>
                                    <span>{{ __('Schedule Consultation') === 'Schedule Consultation' ? 'جدولة استشارة' : __('Schedule Consultation') }}</span>
                                </a>

                                {{-- Etmam-parity: Report an issue — opens the create-ticket modal already rendered in the dashboard. --}}
                                <button type="button"
                                        x-data
                                        x-on:click="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'create-ticket' }))"
                                        class="inline-flex items-center gap-2 px-2.5 md:px-3 py-2 rounded-xl text-[12px] font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-100"
                                        title="{{ __('Report an Issue') === 'Report an Issue' ? 'الإبلاغ عن مشكلة' : __('Report an Issue') }}">
                                    <i class="fas fa-circle-exclamation text-[11px]"></i>
                                    <span class="hidden md:inline">{{ __('Report an Issue') === 'Report an Issue' ? 'الإبلاغ عن مشكلة' : __('Report an Issue') }}</span>
                                </button>

                                {{-- Etmam-parity: notification bell — aggregates expiring docs / open tickets / pending requests / unpaid invoices from existing tables, no new migration. --}}
                                @include('partials.notification-bell')

                                <a href="{{ url('/' . (app()->getLocale() === 'ar' ? 'en' : 'ar')) }}"
                                   class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-xs font-bold text-slate-500 hover:bg-slate-50 border border-slate-100"
                                   title="{{ app()->getLocale() === 'ar' ? 'English' : 'العربية' }}">
                                    {{ app()->getLocale() === 'ar' ? 'EN' : 'ع' }}
                                </a>

                                <a href="{{ route('profile.edit') }}"
                                   class="inline-flex items-center gap-2 px-2.5 md:px-3 py-2 rounded-xl text-[13px] font-bold text-slate-700 hover:bg-slate-50 border border-slate-100">
                                    <div class="w-7 h-7 rounded-lg bg-[#0A2540] text-white text-[11px] font-black flex items-center justify-center shrink-0">
                                        {{ mb_substr($authedUser->name ?? 'A', 0, 1) }}
                                    </div>
                                    <span class="hidden md:inline truncate max-w-[140px]">{{ $authedUser->name }}</span>
                                </a>

                                <form action="{{ route('logout') }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-rose-500 hover:bg-rose-50 border border-slate-100"
                                            title="{{ __('logout') ?: 'تسجيل خروج' }}">
                                        <i class="fas fa-arrow-right-from-bracket"></i>
                                    </button>
                                </form>
                            @endauth
                        </div>
                    </div>
                </header>

                <main class="flex-grow py-6 overflow-x-hidden">
                    <div class="w-full max-w-[1440px] mx-auto">
                    @isset($slot)
                        {{ $slot }}
                    @else
                        @yield('content')
                    @endisset
                    </div>
                </main>

                <footer class="border-t border-[#E8ECEF] bg-white mt-auto">
                    <div class="px-4 md:px-6 py-4 flex flex-col md:flex-row md:items-center md:justify-between gap-2 text-[11px]">
                        <p class="text-slate-500 font-medium">
                            © {{ now()->year }} {{ __('amr7_brand_short') ?: 'شركة آمر سبعة لحلول الأعمال' }}.
                            {{ __('all_rights_reserved') ?: 'جميع الحقوق محفوظة.' }}
                        </p>
                        <nav class="flex flex-wrap items-center gap-x-4 gap-y-1 text-slate-500 font-bold">
                            <a href="{{ url('/') }}" class="hover:text-[#0A2540] transition-colors">{{ __('home') ?: 'الرئيسية' }}</a>
                            <a href="{{ Route::has('contact.index') ? route('contact.index') : url('/contact-us') }}" class="hover:text-[#0A2540] transition-colors">{{ __('contact') ?: 'تواصل معنا' }}</a>
                            <a href="{{ $dashboardUrl }}?section=tickets" wire:navigate class="hover:text-[#0A2540] transition-colors">{{ __('support') ?: 'الدعم' }}</a>
                            <a href="{{ Route::has('using.policy') ? route('using.policy') : url('/using-policy') }}" class="hover:text-[#0A2540] transition-colors">{{ __('terms') ?: 'الشروط والأحكام' }}</a>
                            <a href="{{ Route::has('privacy.policy') ? route('privacy.policy') : url('/privacy-policy') }}" class="hover:text-[#0A2540] transition-colors">{{ __('privacy') ?: 'سياسة الخصوصية' }}</a>
                        </nav>
                    </div>
                </footer>

            </div>
        </div>
    @else
        <livewire:navbar />

        <main class="flex-grow pb-24 md:pb-12">
            @isset($slot)
                {{ $slot }}
            @else
                @yield('content')
            @endisset
        </main>

        @include('partials.footer')
    @endif

    {{-- Etmam-parity: الأزرار العائمة (WhatsApp + Call) تظهر داخل /dashboard أيضًا. تُخفى فقط داخل /amr7 (backoffice). --}}
    @unless(request()->is('amr7') || request()->is('amr7/*'))
    @php
        $floatingCall     = $contact['mobile']   ?? config('amr7.contact.mobile', '+966505336956');
        $floatingWhatsapp = $contact['whatsapp'] ?? config('amr7.contact.whatsapp', '966505336956');
    @endphp
    <div class="fixed bottom-6 end-6 z-[9000] flex flex-col gap-3 print:hidden">
        <a href="tel:{{ $floatingCall }}"
           onclick="if(typeof gtag !== 'undefined') gtag('event', 'click', {'event_category': 'Contact', 'event_label': 'Floating Call Button'});"
           class="w-12 h-12 rounded-full bg-blue-600 hover:bg-blue-700 text-white flex items-center justify-center shadow-lg transition hover:scale-110"
           aria-label="Call Us">
            <span class="sr-only">اتصل بنا</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M6.62 10.79a15.05 15.05 0 0 0 6.59 6.59l2.2-2.2a1 1 0 0 1 1.02-.24c1.12.37 2.33.57 3.57.57a1 1 0 0 1 1 1V20a1 1 0 0 1-1 1C10.3 21 3 13.7 3 4a1 1 0 0 1 1-1h3.49a1 1 0 0 1 1 1c0 1.24.2 2.45.57 3.57a1 1 0 0 1-.24 1.02l-2.2 2.2z"/>
            </svg>
        </a>

        <a href="https://wa.me/{{ $floatingWhatsapp }}"
           target="_blank"
           rel="noopener noreferrer"
           onclick="if(typeof gtag !== 'undefined') gtag('event', 'click', {'event_category': 'Contact', 'event_label': 'Floating WhatsApp Button'});"
           class="w-12 h-12 rounded-full bg-[#25D366] hover:bg-[#20b858] text-white flex items-center justify-center shadow-lg transition hover:scale-110"
           aria-label="WhatsApp">
            <span class="sr-only">تواصل عبر الواتساب</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 32 32" fill="currentColor" aria-hidden="true">
                <path d="M19.11 17.29c-.27-.13-1.58-.78-1.82-.87-.24-.09-.41-.13-.58.14-.17.27-.67.87-.82 1.05-.15.18-.3.2-.57.07-.27-.13-1.12-.41-2.14-1.31-.79-.71-1.33-1.58-1.48-1.85-.15-.27-.02-.41.11-.54.12-.12.27-.31.4-.47.13-.16.17-.27.27-.45.09-.18.04-.34-.02-.47-.07-.13-.58-1.4-.8-1.92-.21-.5-.43-.43-.58-.44h-.5c-.18 0-.47.07-.72.34s-.95.93-.95 2.27.98 2.63 1.12 2.81c.13.18 1.92 2.93 4.66 4.11.65.28 1.16.44 1.56.56.66.21 1.27.18 1.75.11.53-.08 1.58-.64 1.8-1.25.22-.61.22-1.13.15-1.25-.07-.11-.24-.18-.5-.31z"/>
                <path d="M16.02 3C8.84 3 3 8.73 3 15.78c0 2.24.6 4.43 1.73 6.35L3 29l7.08-1.83a13.17 13.17 0 0 0 5.94 1.41h.01c7.18 0 13.02-5.73 13.02-12.79C29.05 8.73 23.2 3 16.02 3zm0 23.45h-.01a10.9 10.9 0 0 1-5.55-1.52l-.4-.24-4.2 1.08 1.12-4.08-.26-.42a10.53 10.53 0 0 1-1.63-5.49c0-5.86 4.87-10.63 10.86-10.63 2.9 0 5.63 1.11 7.68 3.14a10.42 10.42 0 0 1 3.18 7.49c0 5.86-4.87 10.63-10.79 10.63z"/>
            </svg>
        </a>
    </div>
    @endunless

    {{-- Customer Service floating chat (hidden in /amr7 backoffice). --}}
    @unless(request()->is('amr7') || request()->is('amr7/*'))
        @livewire('public.customer-service-chat')
    @endunless

    @livewireScripts
    @livewire('notifications')
    @stack('scripts')
</body>
</html>
