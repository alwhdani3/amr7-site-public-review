<section 
    x-data="{ 
        theme: localStorage.getItem('theme') || 'system',
        setTheme(val) {
            this.theme = val;
            localStorage.setItem('theme', val);
            
            if (val === 'dark' || (val === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        }
    }"
    x-init="$watch('theme', val => setTheme(val)); setTheme(theme)"
    class="w-full bg-white p-8 md:p-10 rounded-[2.5rem] shadow-xl border border-slate-100 font-['Tajawal'] relative overflow-hidden">
    
    {{-- Background Decoration --}}
    <div class="absolute top-0 right-0 w-32 h-32 bg-[#1FA7A2]/5 rounded-bl-[4rem] -mr-8 -mt-8 pointer-events-none"></div>

    {{-- Header --}}
    <div class="relative z-10 mb-8">
        <h2 class="text-2xl font-black text-slate-900 mb-3 flex items-center gap-3">
            <span class="w-10 h-10 rounded-xl bg-[#1FA7A2]/10 flex items-center justify-center text-[#1FA7A2] shadow-sm">
                <i class="fas fa-palette text-lg"></i>
            </span>
            {{ __('Appearance') }}
        </h2>
        <p class="text-slate-500 text-sm leading-relaxed max-w-2xl">
            {{ __('Update the appearance settings for your account') }}
        </p>
    </div>

    {{-- Custom Radio Group for Theme --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 relative z-10">
        
        {{-- Light Mode --}}
        <button type="button" 
                @click="setTheme('light')" 
                class="group relative p-6 rounded-2xl border-2 transition-all duration-300 flex flex-col items-center gap-4 hover:-translate-y-1 hover:shadow-lg"
                :class="theme === 'light' 
                    ? 'bg-[#1FA7A2]/5 border-[#1FA7A2]' 
                    : 'bg-slate-50 border-transparent hover:border-[#1FA7A2]/30'">
            
            <div class="w-14 h-14 rounded-full flex items-center justify-center transition-colors duration-300"
                 :class="theme === 'light' ? 'bg-[#1FA7A2] text-white shadow-lg shadow-[#1FA7A2]/30' : 'bg-white text-slate-400 group-hover:text-[#1FA7A2]'">
                <i class="fas fa-sun text-2xl"></i>
            </div>
            
            <span class="font-bold text-sm transition-colors"
                  :class="theme === 'light' ? 'text-[#1FA7A2]' : 'text-slate-600 group-hover:text-slate-900'">
                {{ __('Light') }}
            </span>

            {{-- Checkmark --}}
            <div x-show="theme === 'light'" 
                 x-transition:enter="animate__animated animate__zoomIn"
                 class="absolute top-3 right-3 text-[#1FA7A2]">
                <i class="fas fa-check-circle"></i>
            </div>
        </button>

        {{-- Dark Mode --}}
        <button type="button" 
                @click="setTheme('dark')" 
                class="group relative p-6 rounded-2xl border-2 transition-all duration-300 flex flex-col items-center gap-4 hover:-translate-y-1 hover:shadow-lg"
                :class="theme === 'dark' 
                    ? 'bg-slate-900 border-slate-700' 
                    : 'bg-slate-50 border-transparent hover:border-slate-300'">
            
            <div class="w-14 h-14 rounded-full flex items-center justify-center transition-colors duration-300"
                 :class="theme === 'dark' ? 'bg-white text-slate-900 shadow-lg' : 'bg-white text-slate-400 group-hover:text-slate-600'">
                <i class="fas fa-moon text-2xl"></i>
            </div>
            
            <span class="font-bold text-sm transition-colors"
                  :class="theme === 'dark' ? 'text-slate-900' : 'text-slate-600 group-hover:text-slate-900'">
                {{ __('Dark') }}
            </span>

             <div x-show="theme === 'dark'" 
                  x-transition:enter="animate__animated animate__zoomIn"
                  class="absolute top-3 right-3 text-slate-900">
                <i class="fas fa-check-circle"></i>
            </div>
        </button>

        {{-- System Mode --}}
        <button type="button" 
                @click="setTheme('system')" 
                class="group relative p-6 rounded-2xl border-2 transition-all duration-300 flex flex-col items-center gap-4 hover:-translate-y-1 hover:shadow-lg"
                :class="theme === 'system' 
                    ? 'bg-gradient-to-br from-slate-100 to-slate-200 border-slate-300' 
                    : 'bg-slate-50 border-transparent hover:border-slate-300'">
            
            <div class="w-14 h-14 rounded-full flex items-center justify-center transition-colors duration-300"
                 :class="theme === 'system' ? 'bg-slate-800 text-white shadow-lg' : 'bg-white text-slate-400 group-hover:text-slate-600'">
                <i class="fas fa-desktop text-xl"></i>
            </div>
            
            <span class="font-bold text-sm transition-colors"
                  :class="theme === 'system' ? 'text-slate-900' : 'text-slate-600 group-hover:text-slate-900'">
                {{ __('System') }}
            </span>

             <div x-show="theme === 'system'" 
                  x-transition:enter="animate__animated animate__zoomIn"
                  class="absolute top-3 right-3 text-slate-500">
                <i class="fas fa-check-circle"></i>
            </div>
        </button>

    </div>
</section>