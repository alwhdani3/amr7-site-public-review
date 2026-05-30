<footer class="bg-slate-50 border-t border-slate-200 pt-16 pb-8 mt-auto relative font-['Tajawal'] overflow-hidden" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <div class="absolute inset-0 pointer-events-none opacity-[0.03]" style="background-image: radial-gradient(#1FA7A2 1px, transparent 1px); background-size: 24px 24px;"></div>

    <div class="container mx-auto px-4 relative z-10">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-8 lg:gap-12 mb-12">
            <div class="lg:col-span-5 md:col-span-6">
                <div class="mb-6">
                    <x-site.brand :href="url('/')" />
                </div>

                <p class="text-slate-500 text-sm leading-relaxed mb-6 max-w-md">
                    {{ __('footer.brand_desc') }}
                </p>

                <div class="flex items-center bg-white rounded-full border border-slate-200 p-1 mb-6 max-w-sm shadow-sm focus-within:border-[#1FA7A2] focus-within:ring-1 focus-within:ring-[#1FA7A2]/20 transition-all">
                    <input
                        type="email"
                        class="bg-transparent border-none text-sm w-full px-4 py-2 focus:ring-0 focus:outline-none text-slate-700 placeholder-slate-400"
                        placeholder="{{ __('footer.email_placeholder') }}"
                        aria-label="{{ __('footer.email_placeholder') }}"
                    >
                    <button
                        type="button"
                        class="bg-[#1FA7A2] hover:bg-[#167F7B] text-white w-10 h-10 rounded-full flex items-center justify-center transition-transform hover:rotate-12 hover:scale-105 shadow-md flex-shrink-0"
                        aria-label="{{ __('footer.subscribe') }}"
                    >
                        <i class="fas fa-paper-plane text-xs rtl:-scale-x-100"></i>
                    </button>
                </div>

                <div class="flex flex-wrap items-center gap-4">
                    <div class="flex items-center gap-2 opacity-75 grayscale hover:grayscale-0 transition-all duration-300">
                        <img src="{{ asset('applepay.svg') }}" class="h-6" alt="{{ __('payment_apple_pay') }}" loading="lazy">
                        <img src="{{ asset('mada.svg') }}" class="h-5" alt="{{ __('payment_mada') }}" loading="lazy">
                        <img src="{{ asset('mastercard.svg') }}" class="h-7" alt="{{ __('payment_mastercard') }}" loading="lazy">
                    </div>

                    <div class="hidden sm:block w-px h-6 bg-slate-300"></div>

                    <div class="flex items-center gap-3">
                        <a href="https://mazaya.monshaat.gov.sa/" target="_blank" rel="noopener" class="opacity-70 hover:opacity-100 grayscale hover:grayscale-0 transition-all duration-300 hover:-translate-y-1" aria-label="{{ __('badge_monshaat') }}">
                            <img src="{{ asset('monsha2at.png') }}" class="h-8 w-auto" alt="{{ __('badge_monshaat') }}" loading="lazy">
                        </a>
                        <a href="https://maroof.sa/businesses/show/498029" target="_blank" rel="noopener" class="opacity-70 hover:opacity-100 grayscale hover:grayscale-0 transition-all duration-300 hover:-translate-y-1" aria-label="{{ __('badge_maroof') }}">
                            <img src="{{ asset('ma3rof.png') }}" class="h-8 w-auto" alt="{{ __('badge_maroof') }}" loading="lazy">
                        </a>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 md:col-span-3">
                <h5 class="font-bold text-[#1FA7A2] text-lg mb-6 relative inline-block pb-2 after:content-[''] after:absolute after:bottom-0 after:rtl:right-0 after:ltr:left-0 after:w-8 after:h-1 after:bg-[#1FA7A2] after:rounded-full">
                    {{ __('footer.links_title') }}
                </h5>

                <ul class="space-y-3">
                    <li>
                        <a href="{{ \Illuminate\Support\Facades\Route::has('about') ? route('about') : '#' }}" class="text-sm text-slate-500 hover:text-[#1FA7A2] transition-all duration-300 flex items-center gap-2 group">
                            <i class="fas fa-chevron-left text-[10px] text-slate-300 group-hover:text-[#1FA7A2] transition-colors rtl:rotate-0 ltr:rotate-180"></i>
                            {{ __('footer.link_about') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ \Illuminate\Support\Facades\Route::has('services.index') ? route('services.index') : '#' }}" class="text-sm text-slate-500 hover:text-[#1FA7A2] transition-all duration-300 flex items-center gap-2 group">
                            <i class="fas fa-chevron-left text-[10px] text-slate-300 group-hover:text-[#1FA7A2] transition-colors rtl:rotate-0 ltr:rotate-180"></i>
                            {{ __('footer.link_services') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ \Illuminate\Support\Facades\Route::has('blog.index') ? route('blog.index') : '#' }}" class="text-sm text-slate-500 hover:text-[#1FA7A2] transition-all duration-300 flex items-center gap-2 group">
                            <i class="fas fa-chevron-left text-[10px] text-slate-300 group-hover:text-[#1FA7A2] transition-colors rtl:rotate-0 ltr:rotate-180"></i>
                            {{ __('footer.link_blog') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ \Illuminate\Support\Facades\Route::has('faq') ? route('faq') : '#' }}" class="text-sm text-slate-500 hover:text-[#1FA7A2] transition-all duration-300 flex items-center gap-2 group">
                            <i class="fas fa-chevron-left text-[10px] text-slate-300 group-hover:text-[#1FA7A2] transition-colors rtl:rotate-0 ltr:rotate-180"></i>
                            {{ __('footer.link_faq') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ \Illuminate\Support\Facades\Route::has('privacy.policy') ? route('privacy.policy') : '#' }}" class="text-sm text-slate-500 hover:text-[#1FA7A2] transition-all duration-300 flex items-center gap-2 group">
                            <i class="fas fa-chevron-left text-[10px] text-slate-300 group-hover:text-[#1FA7A2] transition-colors rtl:rotate-0 ltr:rotate-180"></i>
                            {{ __('footer.link_privacy') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ \Illuminate\Support\Facades\Route::has('contact.index') ? route('contact.index') : '#' }}" class="text-sm text-slate-500 hover:text-[#1FA7A2] transition-all duration-300 flex items-center gap-2 group">
                            <i class="fas fa-chevron-left text-[10px] text-slate-300 group-hover:text-[#1FA7A2] transition-colors rtl:rotate-0 ltr:rotate-180"></i>
                            {{ __('footer.link_contact') }}
                        </a>
                    </li>
                </ul>
            </div>

            <div class="lg:col-span-3 md:col-span-3">
                <h5 class="font-bold text-[#1FA7A2] text-lg mb-6 relative inline-block pb-2 after:content-[''] after:absolute after:bottom-0 after:rtl:right-0 after:ltr:left-0 after:w-8 after:h-1 after:bg-[#1FA7A2] after:rounded-full">
                    {{ __('footer.contact_title') }}
                </h5>

                <div class="flex flex-col gap-3">
                    <a href="tel:+966505336956" class="flex items-center gap-3 p-3 rounded-xl bg-white border border-slate-200 hover:border-[#1FA7A2] hover:-translate-y-1 transition-all duration-300 shadow-sm group" aria-label="{{ __('footer.call_us') }}">
                        <div class="w-10 h-10 rounded-full bg-[#1FA7A2]/10 text-[#1FA7A2] flex items-center justify-center group-hover:bg-[#1FA7A2] group-hover:text-white transition-colors">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <div>
                            <span class="block font-bold text-slate-800 text-sm font-mono" dir="ltr">050 533 6956</span>
                            <small class="text-slate-400 text-xs">{{ __('footer.direct_call') }}</small>
                        </div>
                    </a>

                    <a href="https://wa.me/966505336956" target="_blank" rel="noopener noreferrer" class="flex items-center gap-3 p-3 rounded-xl bg-[#f0fdfa] border border-teal-100 hover:shadow-md hover:-translate-y-1 transition-all duration-300 group" aria-label="{{ __('footer.whatsapp') }}">
                        <div class="w-10 h-10 rounded-full bg-white text-green-500 flex items-center justify-center shadow-sm group-hover:bg-green-500 group-hover:text-white transition-colors">
                            <i class="fab fa-whatsapp text-lg"></i>
                        </div>
                        <div>
                            <span class="block font-bold text-slate-800 text-sm font-mono" dir="ltr">050 533 6956</span>
                            <small class="text-green-600 text-xs font-bold">{{ __('footer.chat_now') }}</small>
                        </div>
                    </a>

                    <a href="https://maps.app.goo.gl/nauF3H6MriBVLoDAA?g_st=ic" target="_blank" rel="noopener noreferrer" class="flex items-center gap-3 p-3 rounded-xl bg-white border border-slate-200 hover:border-[#1FA7A2] hover:-translate-y-1 transition-all duration-300 shadow-sm group" aria-label="{{ __('footer.location') }}">
                        <div class="w-10 h-10 rounded-full bg-[#1FA7A2]/10 text-[#1FA7A2] flex items-center justify-center group-hover:bg-[#1FA7A2] group-hover:text-white transition-colors">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div>
                            <span class="block font-bold text-slate-800 text-sm">{{ __('footer.city_area') }}</span>
                            <small class="text-slate-400 text-xs">{{ __('footer.hq_location') }}</small>
                        </div>
                    </a>
                </div>
            </div>

            <div class="lg:col-span-2 md:col-span-12 text-center lg:text-end rtl:lg:text-end ltr:lg:text-start">
                <h5 class="font-bold text-[#1FA7A2] text-lg mb-6">{{ __('footer.follow_us') }}</h5>

                <div class="flex justify-center lg:justify-end rtl:lg:justify-end ltr:lg:justify-start gap-2 mb-6">
                    <a href="{{ config('amr7.social_links.x') }}" target="_blank" rel="noopener" class="w-9 h-9 rounded-full bg-white border border-slate-200 text-slate-500 flex items-center justify-center hover:bg-[#1FA7A2] hover:text-white hover:border-[#1FA7A2] hover:-translate-y-1 transition-all duration-300 shadow-sm" aria-label="X (Twitter)">
                        <i class="fab fa-x-twitter"></i>
                    </a>
                    <a href="{{ config('amr7.social_links.linkedin') }}" target="_blank" rel="noopener" class="w-9 h-9 rounded-full bg-white border border-slate-200 text-slate-500 flex items-center justify-center hover:bg-[#1FA7A2] hover:text-white hover:border-[#1FA7A2] hover:-translate-y-1 transition-all duration-300 shadow-sm" aria-label="LinkedIn">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                    <a href="{{ config('amr7.social_links.instagram') }}" target="_blank" rel="noopener" class="w-9 h-9 rounded-full bg-white border border-slate-200 text-slate-500 flex items-center justify-center hover:bg-[#1FA7A2] hover:text-white hover:border-[#1FA7A2] hover:-translate-y-1 transition-all duration-300 shadow-sm" aria-label="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="{{ config('amr7.social_links.tiktok') }}" target="_blank" rel="noopener" class="w-9 h-9 rounded-full bg-white border border-slate-200 text-slate-500 flex items-center justify-center hover:bg-[#1FA7A2] hover:text-white hover:border-[#1FA7A2] hover:-translate-y-1 transition-all duration-300 shadow-sm" aria-label="TikTok">
                        <i class="fab fa-tiktok"></i>
                    </a>
                </div>

                <div class="bg-white border border-slate-200 p-4 rounded-xl shadow-sm mb-4 text-center lg:text-end rtl:lg:text-end ltr:lg:text-start">
                    <div class="flex items-center justify-center lg:justify-end rtl:lg:justify-end ltr:lg:justify-start gap-2 text-[#1FA7A2] mb-1">
                        <i class="far fa-clock"></i>
                        <span class="font-bold text-xs">{{ __('footer.work_hours_title') }}</span>
                    </div>
                    <span class="block text-slate-500 text-xs">{{ __('footer.work_hours_value') }}</span>
                </div>

                <a href="tel:920017083" class="block w-full bg-[#f0fdfa] text-[#1FA7A2] hover:bg-[#1FA7A2] hover:text-white border border-teal-100 hover:border-[#1FA7A2] text-center py-3 rounded-xl transition-all duration-300 font-bold text-sm shadow-sm group" aria-label="{{ __('footer.unified_number') }}">
                    <span class="text-xs opacity-80 me-1">{{ __('footer.unified_number') }}</span>
                    <span class="font-mono group-hover:text-white" dir="ltr">920017083</span>
                </a>
            </div>
        </div>

        <div class="border-t border-slate-200 pt-6 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="text-center md:text-start">
                <p class="text-slate-500 text-xs mb-1">
                    {{ __('footer.all_rights_reserved', ['year' => date('Y')]) }}
                    <span class="font-bold text-slate-800">{{ __('footer.brand_name') }}</span>
                </p>
                <div class="flex justify-center md:justify-start gap-3 text-xs text-slate-400 font-mono">
                    <span>{{ __('footer.cr') }}: 7041008108</span>
                    <span class="text-slate-300">|</span>
                    <span>{{ __('footer.vat') }}: 310892748800003</span>
                </div>
            </div>
        </div>

        <button type="button" id="backToTop" class="absolute bottom-6 left-6 rtl:left-6 ltr:right-6 w-10 h-10 rounded-xl bg-[#1FA7A2] text-white flex items-center justify-center shadow-lg hover:-translate-y-1 hover:bg-[#167F7B] transition-all duration-300 z-50" aria-label="{{ __('footer.back_to_top') }}">
            <i class="fas fa-chevron-up"></i>
        </button>
    </div>
</footer>
