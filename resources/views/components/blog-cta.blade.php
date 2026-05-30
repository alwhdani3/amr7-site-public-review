@php
  $logo = asset('brand/amr7/amr7-mark-light.svg');
  $whatsappNumber = '966505336956';
  $phoneNumber = '0505336956';
  $email = 'info@amr-7.sa';
@endphp

<div class="mt-8 p-6 rounded-2xl relative overflow-hidden group bg-gradient-to-br from-slate-900 to-slate-950 border border-teal-500/20 shadow-2xl">
    
    {{-- Glow Effect --}}
    <div class="absolute -top-1/2 -left-1/2 w-[200%] h-[200%] bg-[radial-gradient(circle,rgba(20,184,166,0.1)_0%,transparent_60%)] pointer-events-none transition-opacity duration-500"></div>

    <div class="flex flex-col md:flex-row items-center justify-between gap-6 relative z-10">
        
        <div class="flex items-center gap-4 text-center md:text-end w-full md:w-auto">
            <div class="w-14 h-14 shrink-0 bg-white/5 rounded-2xl flex items-center justify-center p-2.5 border border-white/10 backdrop-blur-sm">
                <img src="{{ $logo }}" alt="Amr 7 Logo" class="w-full h-full object-contain">
            </div>
            <div class="flex flex-col items-center md:items-start">
                <h3 class="text-lg font-bold text-white mb-1">تبغى ننجزها لك باحترافية؟</h3>
                <p class="text-slate-300 text-sm">فريق آمر سبعة جاهز لخدمتك، تواصل معنا وخلك في المضمون.</p>
            </div>
        </div>

        <div class="flex flex-wrap justify-center gap-3 w-full md:w-auto">
            
            {{-- Whatsapp Button --}}
            <a href="https://wa.me/{{ $whatsappNumber }}" 
               target="_blank" 
               class="inline-flex items-center gap-2.5 px-6 py-2.5 rounded-full font-bold text-sm transition-all duration-300 bg-gradient-to-br from-[#25D366] to-[#128C7E] text-white shadow-[0_4px_12px_rgba(37,211,102,0.25)] hover:shadow-[0_6px_20px_rgba(37,211,102,0.4)] hover:-translate-y-0.5 group-hover:scale-105 no-underline border-0">
                <i class="fab fa-whatsapp text-lg"></i>
                <span>واتساب</span>
            </a>

            {{-- Phone Button --}}
            <a href="tel:{{ $phoneNumber }}" 
               class="inline-flex items-center gap-2.5 px-6 py-2.5 rounded-full font-bold text-sm transition-all duration-300 bg-white/5 border border-white/10 text-white/90 hover:bg-white/10 hover:text-white hover:border-teal-500/50 hover:-translate-y-0.5 no-underline">
                <i class="fas fa-phone-alt"></i>
                <span dir="ltr">{{ $phoneNumber }}</span>
            </a>

            {{-- Email Button --}}
            <a href="mailto:{{ $email }}" 
               class="inline-flex items-center justify-center w-10 h-10 rounded-full font-bold text-sm transition-all duration-300 bg-white/5 border border-white/10 text-white/90 hover:bg-white/10 hover:text-white hover:border-teal-500/50 hover:-translate-y-0.5 no-underline" 
               aria-label="Email">
                <i class="fas fa-envelope"></i>
            </a>

            {{-- Share Button (Alpine.js) --}}
            <button type="button" 
                    x-data="{ copied: false }"
                    @click="
                        if (navigator.share) {
                            navigator.share({ title: document.title, url: window.location.href }).catch(console.error);
                        } else {
                            navigator.clipboard.writeText(window.location.href);
                            copied = true;
                            setTimeout(() => copied = false, 2000);
                        }
                    "
                    class="inline-flex items-center justify-center w-10 h-10 rounded-full font-bold text-sm transition-all duration-300 bg-white/5 border border-white/10 text-white/90 hover:bg-white/10 hover:text-white hover:border-teal-500/50 hover:-translate-y-0.5 cursor-pointer">
                <i class="fas" :class="copied ? 'fa-check text-teal-400' : 'fa-share-alt'"></i>
            </button>
        </div>
    </div>
</div>