<div class="relative p-6 md:p-8 rounded-3xl bg-slate-900/60 border border-white/10 shadow-2xl backdrop-blur-xl" dir="rtl">

    {{-- Success Alert --}}
    @if (session()->has('success'))
        <div class="flex items-center gap-3 mb-6 p-4 rounded-xl bg-green-500/10 text-green-400 border border-green-500/20 animate__animated animate__fadeInDown">
            <i class="fas fa-check-circle text-xl"></i>
            <div class="font-medium text-sm">{{ session('success') }}</div>
        </div>
    @endif

    <div class="space-y-6">
        
        {{-- Subject --}}
        <div>
            <label class="block text-sm font-bold text-white mb-2">
                {{ __('label_subject') }} <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <input type="text" 
                       wire:model.defer="subject" 
                       required 
                       placeholder="{{ __('placeholder_subject') }}" 
                       class="w-full bg-slate-800/50 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-[#44BDB8] focus:ring-1 focus:ring-[#44BDB8] transition-all duration-300">
                @error('subject') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            {{-- Department --}}
            <div>
                <label class="block text-sm font-bold text-white mb-2">
                    {{ __('label_department') }} <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <select wire:model.defer="department_id" 
                            required 
                            class="w-full bg-slate-800/50 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-[#44BDB8] focus:ring-1 focus:ring-[#44BDB8] transition-all duration-300 appearance-none cursor-pointer">
                        <option value="" class="bg-slate-800 text-slate-400">{{ __('select_placeholder') }}</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}" class="bg-slate-800">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-4 text-slate-400">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </div>
                </div>
                @error('department_id') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            {{-- Priority --}}
            <div>
                <label class="block text-sm font-bold text-white mb-2">
                    {{ __('label_priority') }} <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <select wire:model.defer="priority" 
                            required 
                            class="w-full bg-slate-800/50 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-[#44BDB8] focus:ring-1 focus:ring-[#44BDB8] transition-all duration-300 appearance-none cursor-pointer">
                        <option value="low" class="bg-slate-800">{{ __('priority_low') }}</option>
                        <option value="medium" class="bg-slate-800">{{ __('priority_medium') }}</option>
                        <option value="high" class="bg-slate-800">{{ __('priority_high') }}</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-4 text-slate-400">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </div>
                </div>
                @error('priority') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Description --}}
        <div>
            <label class="block text-sm font-bold text-white mb-2">
                {{ __('label_description') }} <span class="text-red-500">*</span>
            </label>
            <textarea wire:model.defer="description" 
                      rows="5" 
                      required 
                      class="w-full bg-slate-800/50 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-[#44BDB8] focus:ring-1 focus:ring-[#44BDB8] transition-all duration-300 resize-none" 
                      placeholder="{{ __('placeholder_description') }}"></textarea>
            @error('description') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-4 pt-6 border-t border-white/10">
            <button wire:click="save" 
                    wire:loading.attr="disabled" 
                    class="px-8 py-3 rounded-full bg-gradient-to-br from-[#1FA7A2] to-[#115e59] text-white font-bold shadow-lg hover:shadow-[#1FA7A2]/40 hover:-translate-y-0.5 transition-all duration-300 disabled:opacity-70 disabled:cursor-not-allowed flex items-center gap-2">
                <span wire:loading.remove>{{ $ticket ? __('btn_update_ticket') : __('btn_create_ticket') }}</span>
                <span wire:loading class="flex items-center gap-2">
                    <i class="fas fa-spinner fa-spin"></i> {{ __('loading_processing') }}
                </span>
            </button>

            <a href="{{ route('amr7.tickets.index') }}" 
               class="px-6 py-3 rounded-full border border-white/20 text-white font-bold hover:bg-white/10 transition-all duration-300 text-sm">
                {{ __('btn_cancel') }}
            </a>
        </div>

    </div>
</div>