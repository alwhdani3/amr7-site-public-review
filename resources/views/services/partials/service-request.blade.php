<div class="max-w-3xl mx-auto bg-white rounded-[2rem] shadow-xl border border-slate-200 overflow-hidden font-['Tajawal']" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    
    {{-- Header --}}
    <div class="bg-gradient-to-br from-slate-50 to-slate-100 p-8 text-center border-b border-slate-100">
        <h4 class="font-bold text-2xl text-slate-800 mb-2">{{ __('Submit a New Service Request') }}</h4>
        <p class="text-slate-500 text-sm">{{ __('We are here to simplify your procedures and grow your business') }}</p>
    </div>

    {{-- Form Body --}}
    <div class="p-8 md:p-10">
        <form method="POST" action="{{ route('service.requests.store') }}" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @csrf
            
            {{-- Name Input --}}
            <div class="col-span-1">
                <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-wide">{{ __('Full Name') }}</label>
                <div class="group flex items-center bg-slate-50 border border-slate-200 rounded-xl px-4 transition-all duration-300 focus-within:bg-white focus-within:border-[#1FA7A2] focus-within:ring-4 focus-within:ring-[#1FA7A2]/10">
                    <i class="fas fa-user text-[#1FA7A2] text-lg"></i>
                    <input type="text" name="name" class="w-full bg-transparent border-none focus:ring-0 py-3.5 px-3 text-slate-700 placeholder-slate-400 text-sm font-semibold" placeholder="{{ __('Enter your full name') }}" required>
                </div>
            </div>

            {{-- Phone Input --}}
            <div class="col-span-1">
                <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-wide">{{ __('Mobile Number') }}</label>
                <div class="group flex items-center bg-slate-50 border border-slate-200 rounded-xl px-4 transition-all duration-300 focus-within:bg-white focus-within:border-[#1FA7A2] focus-within:ring-4 focus-within:ring-[#1FA7A2]/10">
                    <i class="fas fa-phone text-[#1FA7A2] text-lg"></i>
                    <input type="tel" name="phone" class="w-full bg-transparent border-none focus:ring-0 py-3.5 px-3 text-slate-700 placeholder-slate-400 text-sm font-semibold" placeholder="05xxxxxxxx" dir="ltr" required>
                </div>
            </div>

            {{-- Service Select --}}
            <div class="col-span-1 md:col-span-2">
                <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-wide">{{ __('Required Service') }}</label>
                <div class="relative group">
                    <div class="flex items-center bg-slate-50 border border-slate-200 rounded-xl px-4 transition-all duration-300 focus-within:bg-white focus-within:border-[#1FA7A2] focus-within:ring-4 focus-within:ring-[#1FA7A2]/10">
                        <i class="fas fa-briefcase text-[#1FA7A2] text-lg"></i>
                        <select name="service" class="w-full bg-transparent border-none focus:ring-0 py-3.5 px-3 text-slate-700 text-sm font-semibold appearance-none cursor-pointer z-10 relative" required>
                            <option value="" selected disabled>{{ __('Select a service from the list...') }}</option>
                            @foreach($platforms as $platform)
                                <option value="{{ $platform->id }}">
                                    {{ $platform->{'name_'.app()->getLocale()} ?? $platform->name_en }}
                                </option>
                            @endforeach
                        </select>
                        {{-- Custom Arrow --}}
                        <div class="absolute inset-y-0 rtl:left-4 ltr:right-4 flex items-center pointer-events-none text-slate-400">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Description Input --}}
            <div class="col-span-1 md:col-span-2">
                <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-wide">{{ __('Request Details') }}</label>
                <div class="group flex items-start bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 transition-all duration-300 focus-within:bg-white focus-within:border-[#1FA7A2] focus-within:ring-4 focus-within:ring-[#1FA7A2]/10">
                    <i class="fas fa-pen text-[#1FA7A2] text-lg mt-3"></i>
                    <textarea name="description" class="w-full bg-transparent border-none focus:ring-0 py-2 px-3 text-slate-700 placeholder-slate-400 text-sm font-medium h-32 resize-none leading-relaxed" placeholder="{{ __('Explain your requirements in detail...') }}"></textarea>
                </div>
            </div>

            {{-- Submit Button --}}
            <div class="col-span-1 md:col-span-2 mt-4">
                <button type="submit" class="group w-full py-4 bg-gradient-to-r from-[#1FA7A2] to-[#167F7B] text-white font-bold rounded-full shadow-lg hover:shadow-xl hover:shadow-[#1FA7A2]/30 hover:-translate-y-1 transition-all duration-300 flex items-center justify-center gap-2">
                    <span>{{ __('Send Request Now') }}</span>
                    <i class="fas fa-paper-plane transition-transform duration-300 group-hover:translate-x-1 rtl:group-hover:-translate-x-1 {{ app()->getLocale() == 'ar' ? 'fa-flip-horizontal' : '' }}"></i>
                </button>
            </div>

        </form>
    </div>
</div>