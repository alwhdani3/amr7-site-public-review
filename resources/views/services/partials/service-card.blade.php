@php
    $serviceTitle = trim((string) ($service->title_ar ?: $service->title_en ?: $service->slug ?: __('Service')));
@endphp

<a href="{{ route('services.show', ['service' => $service->slug]) }}"
   wire:navigate
   class="group h-full bg-white border border-slate-200 rounded-[2rem] p-6 text-center relative overflow-hidden transition-all duration-300 hover:-translate-y-2 hover:border-[#1FA7A2] hover:shadow-xl hover:shadow-[#1FA7A2]/10 flex flex-col cursor-pointer focus:outline-none focus:ring-4 focus:ring-[#1FA7A2]/15"
   aria-label="{{ __('View Details & Request') }}: {{ $serviceTitle }}">
    
    {{-- 1. أيقونة الخدمة (إضافة مهمة للهوية البصرية) --}}
    <div class="w-[70px] h-[70px] mx-auto mb-5 flex items-center justify-center rounded-full shadow-md bg-gradient-to-br from-[#1FA7A2] to-[#167F7B] text-white transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3">
        @if($service->icon)
            <img src="{{ asset('storage/' . $service->icon) }}"
                 class="w-[35px] h-[35px] object-contain brightness-0 invert"
                 alt="{{ $serviceTitle }}"
                 loading="lazy"
                 onerror="this.onerror=null;this.src='{{ asset('images/service-placeholder.png') }}';this.classList.remove('brightness-0','invert');">
        @else
            <i class="fas fa-briefcase text-2xl"></i>
        @endif
    </div>

    {{-- 2. تصنيف المنصة --}}
    <div class="mb-4">
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-teal-50 text-[#1FA7A2] border border-teal-100">
            {{ $service->platform->name ?? __('General') }}
        </span>
    </div>

    {{-- 3. العنوان --}}
    <h3 class="text-xl font-bold text-slate-900 mb-3 group-hover:text-[#1FA7A2] transition-colors leading-tight">
        {{ $service->title }}
    </h3>

    {{-- 4. الوصف --}}
    <p class="text-slate-500 text-sm leading-relaxed flex-grow mb-6 line-clamp-3">
        {{ Str::limit($service->excerpt, 100) }}
    </p>

    {{-- 5. الزر --}}
    <div class="mt-auto">
        <span class="inline-flex items-center justify-center w-full py-3.5 rounded-xl bg-[#1FA7A2] text-white font-bold text-sm group-hover:bg-[#167F7B] transition-all duration-300 shadow-md group-hover:shadow-lg group-hover:-translate-y-0.5">
            {{ __('View Details & Request') }}
            <i class="fas fa-arrow-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }} ms-2 transition-transform duration-300 group-hover:-translate-x-1 rtl:group-hover:-translate-x-1 ltr:group-hover:translate-x-1"></i>
        </span>
    </div>
</a>
