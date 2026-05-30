<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

new class extends Component
{
    public function logout(): void
    {
        Auth::guard('web')->logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        $home = LaravelLocalization::getLocalizedURL(
            LaravelLocalization::getCurrentLocale(),
            '/',
            [],
            false
        );

        $this->redirect($home, navigate: false);
    }

    private function onlyDigits(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?: '';
    }

    private function formatSaudiMobile(string $value): string
    {
        $digits = $this->onlyDigits($value);

        if (str_starts_with($digits, '966') && strlen($digits) >= 12) {
            $digits = '0' . substr($digits, 3);
        }

        if (strlen($digits) === 10) {
            return substr($digits, 0, 3) . ' ' . substr($digits, 3, 3) . ' ' . substr($digits, 6, 4);
        }

        return $value;
    }

    public function with(): array
    {
        $locale = LaravelLocalization::getCurrentLocale();
        $isAr   = $locale === 'ar';
        $dir    = $isAr ? 'rtl' : 'ltr';

        $targetLocale = $isAr ? 'en' : 'ar';
        $currentPath  = '/' . ltrim(request()->path(), '/');

        $sensitivePaths = [
            '/forgot-password',
            '/financial-statements/portal',
            '/en/forgot-password',
            '/en/financial-statements/portal',
        ];

        $isSensitivePage = in_array($currentPath, $sensitivePaths, true);

        $switchUrl = $isSensitivePage
            ? ($targetLocale === 'ar' ? url('/') : url('/en'))
            : LaravelLocalization::getLocalizedURL($targetLocale, null, [], true);

        $homeUrl = LaravelLocalization::getLocalizedURL($locale, '/', [], false);

        $contact = config('amr7.contact', []);
        $labels  = config('amr7.labels', []);
        $social  = config('amr7.social_links', []);

        $aboutItems = [
            ['name' => __('about_us'), 'url' => route('about'), 'icon' => 'fa-users'],
            ['name' => __('vision'), 'url' => route('vision'), 'icon' => 'fa-eye'],
            ['name' => __('bank_accounts'), 'url' => route('banks.index'), 'icon' => 'fa-building-columns'],
        ];

        $fallbackPlatforms = collect(config('amr7.fallback_platforms', []));
        $labelsById        = config('amr7.platform_labels_by_id', []);

        $useDbPlatforms     = (bool) config('amr7.nav.use_db_platforms', true);
        $platformCategoryId = config('amr7.nav.platform_category_id');

        $cacheKey = "nav_platforms_{$platformCategoryId}_{$locale}";
        $dbPlatforms = collect();

        if ($useDbPlatforms) {
            $dbPlatforms = Cache::remember($cacheKey, 3600, function () use ($platformCategoryId) {
                try {
                    $q = DB::table('service_platforms')
                        ->select('id', 'slug', 'name_ar', 'name_en')
                        ->where('is_active', 1);

                    if (! empty($platformCategoryId)) {
                        $q->where('service_category_id', (int) $platformCategoryId);
                    }

                    return collect($q->orderBy('id')->get());
                } catch (\Throwable $e) {
                    return collect();
                }
            });
        }

        $menuPlatforms = $dbPlatforms->isNotEmpty()
            ? $dbPlatforms->map(function ($p) use ($labelsById, $locale) {
                $name = $locale === 'ar' ? ($p->name_ar ?? '') : ($p->name_en ?? ($p->name_ar ?? ''));
                if (isset($labelsById[$p->id][$locale])) {
                    $name = $labelsById[$p->id][$locale];
                }
                return (object) ['id' => (int) $p->id, 'slug' => (string) ($p->slug ?? ''), 'name' => (string) $name];
            })
            : $fallbackPlatforms->map(function ($p) use ($locale) {
                return (object) [
                    'id'   => (int) ($p['id'] ?? 0),
                    'slug' => (string) ($p['slug'] ?? ''),
                    'name' => (string) ($p['name'][$locale] ?? ($p['name']['en'] ?? '')),
                ];
            });

        $menuPlatforms = $menuPlatforms->filter(fn ($p) => ! empty($p->slug))->values();

        $mapUrl = trim((string) ($contact['map_url'] ?? ''));
        if ($mapUrl === '') {
            $mapQuery = trim((string) ($contact['map_query'] ?? 'Riyadh'));
            $mapUrl   = 'https://www.google.com/maps/search/?api=1&query=' . urlencode($mapQuery ?: 'Riyadh');
        }

        $phone        = (string) ($contact['phone'] ?? '920017083');
        $mobileRaw    = (string) ($contact['mobile'] ?? '+966505336956');
        $whatsappRaw  = (string) ($contact['whatsapp'] ?? '966505336956');

        return [
            'dir'            => $dir,
            'locale'         => $locale,
            'switchUrl'      => $switchUrl,
            'homeUrl'        => $homeUrl,
            'langLabel'      => $isAr ? 'English' : 'العربية',
            'mapUrl'         => $mapUrl,
            'contact'        => $contact,
            'email'          => (string) ($contact['email'] ?? 'info@amr-7.sa'),
            'phone'          => $phone,
            'phoneTel'       => $this->onlyDigits($phone) ?: $phone,
            'mobileRaw'      => $mobileRaw,
            'mobileTel'      => $this->onlyDigits($mobileRaw) ?: $mobileRaw,
            'mobileDisplay'  => $this->formatSaudiMobile($mobileRaw),
            'waDigits'       => $this->onlyDigits($whatsappRaw) ?: '966505336956',
            'socialLinks'    => $social,
            'aboutItems'     => $aboutItems,
            'menuPlatforms'  => $menuPlatforms,
            'servicesLabel'  => __('Business Services'),
            'socialIcons'    => [
                'haraj'     => ['set' => 'fas', 'icon' => 'shopping-bag'],
                'linkedin'  => ['set' => 'fab', 'icon' => 'linkedin-in'],
                'x'         => ['set' => 'fab', 'icon' => 'x-twitter'],
                'instagram' => ['set' => 'fab', 'icon' => 'instagram'],
                'tiktok'    => ['set' => 'fab', 'icon' => 'tiktok'],
            ],
        ];
    }
}; ?>

<div class="font-['Tajawal'] relative z-50">
    {{-- شريط التواصل العلوي --}}
    <div class="hidden lg:block relative bg-slate-900 text-slate-300 text-xs z-50 transition-colors">
        <div class="container mx-auto px-4 flex justify-between items-center py-2.5">
            <div class="flex items-center gap-4">
                <a href="{{ $mapUrl }}" target="_blank" rel="noopener noreferrer" class="flex items-center hover:text-white transition-colors duration-300 group">
                    <i class="fas fa-location-dot text-[#1FA7A2] group-hover:text-emerald-400 ms-1.5 transition-colors" aria-hidden="true"></i>
                    <span class="font-medium">{{ __('Location') }}: {{ __('Riyadh - Al Nafl District') }}</span>
                </a>
                <span class="h-3 w-px bg-slate-700"></span>
                <a href="mailto:{{ $email }}" class="flex items-center hover:text-white transition-colors duration-300 group">
                    <i class="fas fa-envelope text-[#1FA7A2] group-hover:text-emerald-400 ms-1.5 transition-colors" aria-hidden="true"></i>
                    <span class="font-sans tracking-wide">{{ $email }}</span>
                </a>
                <span class="h-3 w-px bg-slate-700"></span>
                <a href="tel:{{ $phoneTel }}" class="flex items-center font-bold text-white hover:text-emerald-400 transition-colors duration-300 group">
                    <i class="fas fa-headset text-[#1FA7A2] group-hover:text-emerald-400 ms-1.5 transition-colors" aria-hidden="true"></i>
                    <span class="font-sans tracking-widest" dir="ltr">{{ $phone }}</span>
                </a>
                <span class="h-3 w-px bg-slate-700"></span>
                <a href="tel:{{ $mobileTel }}" class="flex items-center font-bold text-white hover:text-emerald-400 transition-colors duration-300 group" dir="ltr">
                    <i class="fas fa-phone-alt text-[#1FA7A2] group-hover:text-emerald-400 ms-1.5 transition-colors" aria-hidden="true"></i>
                    <span class="font-sans tracking-wide" dir="ltr">{{ $mobileDisplay }}</span>
                </a>
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2 ps-4" dir="ltr">
                    @foreach($socialIcons as $key => $meta)
                        @if(!empty($socialLinks[$key]))
                            <a href="{{ $socialLinks[$key] }}" target="_blank" rel="noopener noreferrer"
                               class="text-slate-400 hover:text-white hover:-translate-y-0.5 transition-all duration-300 px-1" aria-label="{{ $key }}">
                                <i class="{{ $meta['set'] }} fa-{{ $meta['icon'] }}" aria-hidden="true"></i>
                            </a>
                        @endif
                    @endforeach
                </div>

                <span class="h-3 w-px bg-slate-700"></span>

                <a href="{{ $switchUrl }}" rel="nofollow" class="flex items-center text-xs font-bold hover:text-white transition-colors duration-300 group">
                    {{ $langLabel }} <i class="fas fa-globe ms-1.5 text-[#1FA7A2] group-hover:text-emerald-400 transition-colors" aria-hidden="true"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- القائمة الرئيسية (مع تأثير Glassmorphism) --}}
    <nav x-data="{ mobileMenuOpen: false }" class="bg-white/90 backdrop-blur-lg shadow-sm sticky top-0 z-40 w-full border-b border-slate-100/50 transition-all duration-300" dir="{{ $dir }}">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center h-24 lg:h-28">

                {{-- الشعار --}}
                <a class="flex-shrink-0 ms-4 transition-transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-[#1FA7A2] rounded-lg" href="{{ $homeUrl }}" wire:navigate aria-label="{{ app()->getLocale() === 'ar' ? 'شركة آمر سبعة لحلول الأعمال' : 'Amr Seven Business Solutions' }}">
                    <x-site.brand :linked="false" />
                </a>

                {{-- زر الموبايل --}}
                <button @click="mobileMenuOpen = !mobileMenuOpen" type="button" class="lg:hidden p-2.5 rounded-xl text-slate-600 bg-slate-50 hover:bg-[#1FA7A2] hover:text-white focus:outline-none transition-all" aria-label="{{ __('Toggle Menu') }}">
                    <i class="fas fa-bars text-xl" x-show="!mobileMenuOpen" aria-hidden="true"></i>
                    <i class="fas fa-times text-xl" x-show="mobileMenuOpen" style="display: none;" aria-hidden="true"></i>
                </button>

                {{-- روابط الديسكتوب --}}
                <div class="hidden lg:flex lg:items-center lg:gap-2">
                    <a href="{{ $homeUrl }}" wire:navigate class="text-[15px] font-bold text-slate-700 hover:text-[#1FA7A2] hover:bg-slate-50 rounded-lg px-4 py-2 transition-all">
                        {{ __('Home') }}
                    </a>

                    {{-- قائمة من نحن --}}
                    <div x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" class="relative group">
                        <button class="flex items-center gap-1.5 text-[15px] font-bold text-slate-700 hover:text-[#1FA7A2] hover:bg-slate-50 rounded-lg px-4 py-2 transition-all focus:outline-none">
                            <span>{{ __('About Us') }}</span>
                            <i class="fas fa-chevron-down text-[10px] transition-transform duration-300" :class="{'rotate-180': open}" aria-hidden="true"></i>
                        </button>

                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 translate-y-2"
                             class="absolute top-full start-0 mt-2 w-64 bg-white rounded-2xl shadow-xl border border-slate-100/50 p-2 z-50 overflow-hidden"
                             style="display: none;">
                            <div class="grid grid-cols-1 gap-1">
                                @foreach($aboutItems as $item)
                                    <a href="{{ $item['url'] }}" wire:navigate class="flex items-center p-3 rounded-xl hover:bg-slate-50 group/item transition-colors">
                                        <div class="w-9 h-9 rounded-lg bg-white shadow-sm border border-slate-100 flex items-center justify-center text-slate-400 group-hover/item:text-[#1FA7A2] group-hover/item:border-[#1FA7A2]/30 transition-all">
                                            <i class="fas {{ $item['icon'] }}" aria-hidden="true"></i>
                                        </div>
                                        <span class="ms-3 text-sm font-bold text-slate-600 group-hover/item:text-[#1FA7A2]">
                                            {{ $item['name'] }}
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- قائمة الخدمات --}}
                    <div x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" class="relative group">
                        <button class="flex items-center gap-1.5 text-[15px] font-bold text-slate-700 hover:text-[#1FA7A2] hover:bg-slate-50 rounded-lg px-4 py-2 transition-all focus:outline-none">
                            <span>{{ $servicesLabel }}</span>
                            <i class="fas fa-chevron-down text-[10px] transition-transform duration-300" :class="{'rotate-180': open}" aria-hidden="true"></i>
                        </button>

                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 translate-y-2"
                             class="absolute top-full start-0 mt-2 w-72 bg-white rounded-2xl shadow-xl border border-slate-100/50 py-3 z-50 max-h-[70vh] overflow-y-auto custom-scrollbar"
                             style="display: none;">

                            @foreach(($menuPlatforms ?? []) as $platform)
                                <a href="{{ route('services.platform', ['platform' => $platform->slug]) }}" wire:navigate
                                   class="block px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-[#1FA7A2] transition-colors group/link">
                                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-slate-300 group-hover/link:bg-[#1FA7A2] group-hover/link:scale-125 transition-all ms-2"></span>
                                    {{ $platform->name }}
                                </a>
                            @endforeach

                            <div class="border-t border-slate-100 my-2"></div>

                            <a href="{{ route('services.index') }}" wire:navigate class="block px-4 py-2 text-center text-sm font-black text-[#1FA7A2] hover:bg-slate-50 transition-colors mx-2 rounded-xl">
                                {{ __('View All Services') }} <i class="fas fa-arrow-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }} rtl:-scale-x-100 ms-1 text-[10px]" aria-hidden="true"></i>
                            </a>
                        </div>
                    </div>

                    <a href="{{ route('blog.index') }}" wire:navigate class="text-[15px] font-bold text-slate-700 hover:text-[#1FA7A2] hover:bg-slate-50 rounded-lg px-4 py-2 transition-all">{{ __('Blog') }}</a>
                    <a href="{{ route('contact.index') }}" wire:navigate class="text-[15px] font-bold text-slate-700 hover:text-[#1FA7A2] hover:bg-slate-50 rounded-lg px-4 py-2 transition-all">{{ __('Contact Us') }}</a>

                    <div class="flex items-center gap-3 ms-4 border-s border-slate-200 ps-6">
                        <a href="{{ route('services.index') }}" wire:navigate class="bg-[#1FA7A2] hover:bg-[#167F7B] text-white px-7 py-2.5 rounded-xl text-sm font-bold shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all whitespace-nowrap flex items-center">
                            {{ __('اطلب خدمة') }}
                        </a>

                        @auth
                            @include('partials.company-switcher')
                            <a href="{{ route('dashboard') }}" wire:navigate class="bg-slate-900 text-white px-5 py-2.5 rounded-xl text-sm font-black shadow-md hover:shadow-lg hover:bg-slate-800 transition-all whitespace-nowrap">
                                {{ __('Dashboard') }}
                            </a>
                            <button wire:click="logout" type="button" class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-rose-500 hover:bg-rose-50 rounded-xl transition-all border border-transparent hover:border-rose-100" title="{{ __('Logout') }}" aria-label="{{ __('Logout') }}">
                                <i wire:loading.remove wire:target="logout" class="fas fa-sign-out-alt" aria-hidden="true"></i>
                                <i wire:loading wire:target="logout" class="fas fa-circle-notch fa-spin" aria-hidden="true"></i>
                            </button>
                        @else
                            <a href="{{ route('login') }}" wire:navigate class="flex items-center gap-2 px-5 py-2.5 text-slate-600 hover:text-[#1FA7A2] hover:bg-slate-50 rounded-xl transition-all border border-slate-200 font-bold text-sm" aria-label="{{ __('Login') }}">
                                <i class="fas fa-user" aria-hidden="true"></i> {{ __('Login') }}
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>

        {{-- قائمة الموبايل --}}
        <div x-show="mobileMenuOpen" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-4"
             class="lg:hidden absolute top-full left-0 right-0 bg-white shadow-xl border-t border-slate-100 z-50" 
             style="display: none;">
            <div class="flex flex-col p-4 space-y-1 max-h-[80vh] overflow-y-auto custom-scrollbar">
                <a href="{{ $homeUrl }}" wire:navigate class="block px-4 py-3.5 rounded-xl font-bold hover:bg-slate-50 text-slate-700 transition-colors">{{ __('Home') }}</a>

                <div x-data="{ subOpen: false }" class="rounded-xl overflow-hidden border border-transparent" :class="{'border-slate-100 bg-slate-50/50': subOpen}">
                    <button @click="subOpen = !subOpen" class="w-full flex justify-between items-center px-4 py-3.5 text-slate-700 font-bold hover:bg-slate-50 transition-colors">
                        <span>{{ $servicesLabel }}</span>
                        <i class="fas fa-chevron-down text-xs transition-transform duration-300 text-slate-400" :class="{'rotate-180 text-[#1FA7A2]': subOpen}" aria-hidden="true"></i>
                    </button>
                    <div x-show="subOpen" x-collapse class="px-4 pb-3 space-y-1">
                        @foreach(($menuPlatforms ?? []) as $platform)
                            <a href="{{ route('services.platform', ['platform' => $platform->slug]) }}" wire:navigate class="block py-2.5 px-2 text-sm font-semibold text-slate-600 hover:text-[#1FA7A2] hover:translate-x-1 rtl:hover:-translate-x-1 transition-transform">
                                {{ $platform->name }}
                            </a>
                        @endforeach
                        <div class="border-t border-slate-200/60 my-2"></div>
                        <a href="{{ route('services.index') }}" wire:navigate class="block py-2 px-2 text-sm text-[#1FA7A2] font-black">
                            {{ __('View All Services') }} &rarr;
                        </a>
                    </div>
                </div>

                <a href="{{ route('blog.index') }}" wire:navigate class="block px-4 py-3.5 rounded-xl font-bold hover:bg-slate-50 text-slate-700 transition-colors">{{ __('Blog') }}</a>
                <a href="{{ route('contact.index') }}" wire:navigate class="block px-4 py-3.5 rounded-xl font-bold hover:bg-slate-50 text-slate-700 transition-colors">{{ __('Contact Us') }}</a>

                <div class="border-t border-slate-100 mt-4 pt-4 px-2">
                    <a href="{{ route('services.index') }}" wire:navigate class="w-full text-center block px-4 py-4 rounded-xl bg-[#1FA7A2] text-white font-black mb-3 shadow-md active:scale-95 transition-all">
                        {{ __('اطلب خدمة') }}
                    </a>

                    @auth
                        <a href="{{ route('dashboard') }}" wire:navigate class="w-full text-center block px-4 py-4 rounded-xl bg-slate-900 text-white font-black mb-3 shadow-md active:scale-95 transition-all">
                            {{ __('Dashboard') }}
                        </a>
                        <button wire:click="logout" type="button" class="w-full text-center block px-4 py-3.5 rounded-xl bg-rose-50 text-rose-600 font-bold border border-rose-100 active:scale-95 transition-all">
                            <span wire:loading.remove wire:target="logout"><i class="fas fa-sign-out-alt me-2" aria-hidden="true"></i>{{ __('Logout') }}</span>
                            <span wire:loading wire:target="logout"><i class="fas fa-circle-notch fa-spin me-2" aria-hidden="true"></i>{{ __('Processing...') }}</span>
                        </button>
                    @else
                        <a href="{{ route('login') }}" wire:navigate class="w-full text-center block px-4 py-4 rounded-xl bg-white border-2 border-slate-100 text-[#1FA7A2] font-black mb-3 active:scale-95 transition-all">
                            <i class="fas fa-user-circle me-2" aria-hidden="true"></i> {{ __('Login') }}
                        </a>
                    @endauth

                    <a href="{{ $switchUrl }}" class="w-full text-center flex justify-center items-center mt-3 px-4 py-3.5 rounded-xl bg-slate-50 text-slate-600 font-bold active:scale-95 transition-all">
                        <i class="fas fa-globe me-2" aria-hidden="true"></i> {{ $langLabel }}
                    </a>
                </div>
            </div>
        </div>
    </nav>
</div>
