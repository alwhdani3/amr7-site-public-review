<footer class="bg-white border-t border-slate-100 pt-16 pb-8 mt-auto relative overflow-hidden group/footer" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    
    {{-- النقاط الخلفية الجمالية (تم تخفيف الشفافية لتكون أكثر أناقة) --}}
    <div class="absolute inset-0 opacity-[0.03] pointer-events-none" 
         style="background-image: radial-gradient(#1FA7A2 1.5px, transparent 1.5px); background-size: 32px 32px;">
    </div>

    <div class="container mx-auto px-4 relative z-10">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-10 lg:gap-12 mb-16">

            {{-- العمود الأول: الشعار والنبذة --}}
            <div class="lg:col-span-5 md:col-span-6 space-y-7">
                <x-site.brand :href="url('/')" />

                <p class="text-slate-500 text-sm leading-relaxed max-w-md font-medium">
                    {{ __('footer_brand_bio') }}
                </p>

                {{-- النشرة البريدية --}}
                <form action="#" method="POST" class="relative max-w-sm group/input">
                    @csrf
                    <label for="footer_newsletter_email" class="sr-only">
                        {{ __('footer_email_placeholder') }}
                    </label>

                    <div class="flex items-center bg-slate-50/80 rounded-2xl border border-slate-200 p-1.5 transition-all duration-300 focus-within:border-[#1FA7A2] focus-within:bg-white focus-within:ring-4 focus-within:ring-[#1FA7A2]/10 hover:border-slate-300 hover:bg-slate-50">
                        <i class="fas fa-envelope text-slate-400 mx-3 group-focus-within/input:text-[#1FA7A2] transition-colors" aria-hidden="true"></i>

                        <input
                            type="email"
                            id="footer_newsletter_email"
                            name="footer_newsletter_email"
                            class="bg-transparent border-none text-sm w-full py-2 focus:ring-0 outline-none text-slate-700 placeholder-slate-400 font-medium"
                            placeholder="{{ __('footer_email_placeholder') }}"
                            autocomplete="email"
                            required
                        >

                        <button
                            type="submit"
                            class="bg-[#1FA7A2] hover:bg-[#167F7B] text-white w-11 h-11 rounded-xl flex items-center justify-center transition-all shadow-md hover:shadow-lg active:scale-95 shrink-0"
                            aria-label="{{ __('footer_subscribe') }}"
                        >
                            {{-- استخدام الخصائص المنطقية لقلب الأيقونة بناءً على الاتجاه --}}
                            <i class="fas fa-paper-plane text-xs rtl:-scale-x-100" aria-hidden="true"></i>
                        </button>
                    </div>
                </form>

                {{-- الشركاء وطرق الدفع --}}
                <div class="flex flex-wrap items-center gap-6 pt-2">
                    <div class="flex items-center gap-3 opacity-60 grayscale hover:grayscale-0 hover:opacity-100 transition-all duration-500">
                        <img src="{{ asset('applepay.svg') }}" class="h-5" alt="{{ __('payment_apple_pay') }}" loading="lazy">
                        <img src="{{ asset('mada.svg') }}" class="h-4" alt="{{ __('payment_mada') }}" loading="lazy">
                        <img src="{{ asset('mastercard.svg') }}" class="h-6" alt="{{ __('payment_mastercard') }}" loading="lazy">
                    </div>

                    <div class="hidden sm:block w-px h-6 bg-slate-200"></div>

                    <div class="flex items-center gap-4">
                        <a href="https://mazaya.monshaat.gov.sa/" target="_blank" rel="noopener" 
                           class="opacity-60 hover:opacity-100 transition-all hover:-translate-y-1 drop-shadow-sm hover:drop-shadow-md" aria-label="{{ __('badge_monshaat') }}">
                            <img src="{{ asset('monsha2at.png') }}" class="h-8 w-auto" alt="{{ __('badge_monshaat') }}" loading="lazy">
                        </a>
                        <a href="https://maroof.sa/businesses/show/498029" target="_blank" rel="noopener" 
                           class="opacity-60 hover:opacity-100 transition-all hover:-translate-y-1 drop-shadow-sm hover:drop-shadow-md" aria-label="{{ __('badge_maroof') }}">
                            <img src="{{ asset('ma3rof.png') }}" class="h-8 w-auto" alt="{{ __('badge_maroof') }}" loading="lazy">
                        </a>
                    </div>
                </div>
            </div>

            {{-- العمود الثاني: روابط هامة --}}
            <div class="lg:col-span-2 md:col-span-3">
                <h4 class="text-slate-800 font-bold text-lg mb-6 relative inline-block">
                    {{ __('footer_links_title') }}
                    {{-- تم استخدام start-0 بدلاً من الخلط بين ltr و rtl --}}
                    <span class="absolute -bottom-2 start-0 w-8 h-1 bg-[#1FA7A2] rounded-full"></span>
                </h4>

                <ul class="space-y-3.5">
                    @php
                        $links = [
                            ['route' => 'public.landing.company-formation-riyadh', 'label' => 'Company Formation'],
                            ['route' => 'landing.foreign_investment', 'label' => 'Foreign Investment'],
                            ['route' => 'landing.liquidation', 'label' => 'Company Liquidation'],
                            ['route' => 'about', 'label' => 'footer_link_about'],
                            ['route' => 'services.index', 'label' => 'footer_link_services'],
                            ['route' => 'blog.index', 'label' => 'footer_link_tech_blog'],
                            ['route' => 'faq', 'label' => 'footer_link_faq'],
                            ['route' => 'privacy.policy', 'label' => 'footer_link_privacy'],
                            ['route' => 'contact.index', 'label' => 'footer_link_contact'],
                        ];
                    @endphp

                    @foreach($links as $link)
                    <li>
                        <a href="{{ \Illuminate\Support\Facades\Route::has($link['route']) ? route($link['route']) : '#' }}" 
                           class="text-sm font-medium text-slate-500 hover:text-[#1FA7A2] transition-all flex items-center gap-2.5 group/link w-fit focus:outline-none focus:text-[#1FA7A2]">
                            {{-- استخدام rtl:-scale-x-100 لقلب الأيقونة تلقائياً مع اللغة العربية --}}
                            <i class="fas fa-chevron-right text-[10px] text-slate-300 rtl:-scale-x-100 group-hover/link:text-[#1FA7A2] transition-all group-hover/link:translate-x-1 rtl:group-hover/link:-translate-x-1" aria-hidden="true"></i>
                            {{ __($link['label']) }}
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>

            {{-- العمود الثالث: تواصل معنا --}}
            <div class="lg:col-span-3 md:col-span-3 space-y-6">
                <h4 class="text-slate-800 font-bold text-lg mb-6 relative inline-block">
                    {{ __('footer_contact_title') }}
                    <span class="absolute -bottom-2 start-0 w-8 h-1 bg-[#1FA7A2] rounded-full"></span>
                </h4>

                <div class="grid gap-3.5">
                    <a href="tel:+966505336956" class="flex items-center gap-4 p-3.5 rounded-2xl bg-slate-50 border border-transparent hover:border-slate-200 hover:bg-white hover:shadow-md hover:shadow-slate-200/50 transition-all group/card">
                        <div class="w-11 h-11 rounded-xl bg-white shadow-sm flex items-center justify-center text-[#1FA7A2] group-hover/card:bg-[#1FA7A2] group-hover/card:text-white transition-all duration-300">
                            <i class="fas fa-phone-alt" aria-hidden="true"></i>
                        </div>
                        <div>
                            <span class="block font-bold text-slate-800 text-sm" dir="ltr">050 533 6956</span>
                            <small class="text-slate-500 font-semibold text-[10px] uppercase tracking-wider mt-0.5 block">{{ __('footer_contact_call_desc') }}</small>
                        </div>
                    </a>

                    <a href="https://wa.me/966505336956" target="_blank" rel="noopener noreferrer" 
                       class="flex items-center gap-4 p-3.5 rounded-2xl bg-emerald-50/50 border border-transparent hover:border-emerald-100 hover:bg-white hover:shadow-md hover:shadow-emerald-100/50 transition-all group/card">
                        <div class="w-11 h-11 rounded-xl bg-white shadow-sm flex items-center justify-center text-[#22c55e] group-hover/card:bg-[#22c55e] group-hover/card:text-white transition-all duration-300">
                            <i class="fab fa-whatsapp text-lg" aria-hidden="true"></i>
                        </div>
                        <div>
                            <span class="block font-bold text-slate-800 text-sm" dir="ltr">050 533 6956</span>
                            <small class="text-emerald-600 font-semibold text-[10px] uppercase tracking-wider mt-0.5 block">{{ __('footer_contact_whatsapp_desc') }}</small>
                        </div>
                    </a>

                    <a href="https://maps.app.goo.gl/XKE5JJzxV7xmF7eV7?g_st=ic" target="_blank" rel="noopener" 
                       class="flex items-center gap-4 p-3.5 rounded-2xl bg-slate-50 border border-transparent hover:border-slate-200 hover:bg-white hover:shadow-md hover:shadow-slate-200/50 transition-all group/card">
                        <div class="w-11 h-11 rounded-xl bg-white shadow-sm flex items-center justify-center text-[#1FA7A2] group-hover/card:bg-[#1FA7A2] group-hover/card:text-white transition-all duration-300">
                            <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                        </div>
                        <div class="min-w-0">
                            <span class="block text-slate-800 text-sm font-bold truncate">{{ __('footer_address_city') }}</span>
                            <small class="text-slate-500 font-semibold text-[10px] uppercase tracking-wider truncate block mt-0.5">{{ __('footer_address_street') }}</small>
                        </div>
                    </a>
                </div>
            </div>

            {{-- العمود الرابع: تابعنا وأوقات العمل --}}
            <div class="lg:col-span-2 md:col-span-12 text-center lg:text-end space-y-6">
                <h4 class="text-slate-800 font-bold text-lg mb-6 relative inline-block lg:hidden">
                    {{ __('footer_follow_title') }}
                </h4>

                <div class="flex justify-center lg:justify-end gap-2.5">
                    @php
                        $socials = [
                            ['icon' => 'fa-x-twitter', 'url' => 'https://x.com/A3Alammar', 'hoverClass' => 'hover:bg-black hover:text-white'],
                            ['icon' => 'fa-linkedin-in', 'url' => 'https://www.linkedin.com/company/%D8%A2%D9%85%D8%B1-%D8%B3%D8%A8%D8%B9%D8%A9', 'hoverClass' => 'hover:bg-[#0077b5] hover:text-white'],
                            ['icon' => 'fa-instagram', 'url' => 'https://www.instagram.com/amr7.sa/', 'hoverClass' => 'hover:bg-gradient-to-tr hover:from-[#fd5949] hover:to-[#d6249f] hover:text-white'],
                            ['icon' => 'fa-tiktok', 'url' => 'https://www.tiktok.com/@amr7sa3', 'hoverClass' => 'hover:bg-black hover:text-white'],
                        ];
                    @endphp

                    @foreach($socials as $social)
                    <a href="{{ $social['url'] }}" target="_blank" 
                       class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-500 transition-all duration-300 shadow-sm hover:-translate-y-1 {{ $social['hoverClass'] }} focus:outline-none focus:ring-2 focus:ring-[#1FA7A2]" 
                       aria-label="{{ str_replace('fa-', '', $social['icon']) }}"
                       rel="noopener">
                        <i class="fab {{ $social['icon'] }} text-sm" aria-hidden="true"></i>
                    </a>
                    @endforeach
                </div>

                <div class="bg-slate-50/50 border border-slate-100 p-4 rounded-2xl shadow-sm text-center lg:text-end hover:bg-white hover:border-slate-200 transition-colors">
                    <div class="flex items-center justify-center lg:justify-end gap-2 text-[#1FA7A2] mb-1.5">
                        <i class="far fa-clock text-sm" aria-hidden="true"></i>
                        <span class="font-bold text-xs uppercase tracking-tighter">{{ __('footer_work_hours_title') }}</span>
                    </div>
                    <span class="block text-slate-500 text-xs font-medium leading-relaxed">{{ __('footer_work_hours_value') }}</span>
                </div>

                <a href="tel:920017083" class="block w-full bg-[#f0fdfa] text-[#1FA7A2] border border-[#1FA7A2]/10 hover:bg-[#1FA7A2] hover:text-white hover:shadow-lg hover:shadow-[#1FA7A2]/20 text-center py-3.5 rounded-2xl transition-all duration-300 font-bold text-sm group/btn-unif focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#1FA7A2]">
                    <span class="text-[10px] opacity-70 ms-1 uppercase tracking-widest">{{ __('footer_unified_number_label') }}</span>
                    <span dir="ltr" class="text-base tracking-wide">920017083</span>
                </a>
            </div>
        </div>

        {{-- شريط الحقوق --}}
        <div class="border-t border-slate-100 pt-6 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="text-center md:text-start space-y-1.5 order-2 md:order-1">
                <p class="text-slate-500 text-xs font-medium">
                    {{ __('footer_rights') }} <span class="text-slate-800 font-bold">{{ __('footer_company_name') }}</span> &copy; {{ date('Y') }}
                </p>
                <div class="flex flex-wrap justify-center md:justify-start gap-3.5 text-[10px] font-semibold text-slate-400 uppercase tracking-wider">
                    <span class="flex items-center gap-1.5 hover:text-[#1FA7A2] transition-colors cursor-default"><i class="fas fa-fingerprint text-[9px]" aria-hidden="true"></i> {{ __('footer_cr_label') }} 7041008108</span>
                    <span class="hidden md:block opacity-20">|</span>
                    <span class="flex items-center gap-1.5 hover:text-[#1FA7A2] transition-colors cursor-default"><i class="fas fa-receipt text-[9px]" aria-hidden="true"></i> {{ __('footer_vat_label') }} 310892748800003</span>
                </div>
            </div>

            <button type="button" id="backToTop"
                    class="order-1 md:order-2 w-11 h-11 rounded-xl bg-slate-800 text-white flex items-center justify-center shadow-md transition-all duration-300 hover:-translate-y-1 hover:bg-[#1FA7A2] hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-800 group/top"
                    aria-label="{{ __('footer_back_to_top') }}">
                <i class="fas fa-chevron-up text-sm transition-transform group-hover/top:-translate-y-0.5" aria-hidden="true"></i>
            </button>
        </div>
    </div>
</footer>
