<div class="w-full bg-white p-8 md:p-10 rounded-[2.5rem] shadow-xl border border-slate-100 font-['Tajawal'] relative overflow-hidden"
     wire:cloak
     x-data="{ showRecoveryCodes: false }">

    {{-- Background Decoration --}}
    <div class="absolute top-0 right-0 w-32 h-32 bg-[#1FA7A2]/5 rounded-bl-[4rem] -mr-8 -mt-8 pointer-events-none"></div>

    {{-- Header --}}
    <div class="relative z-10 mb-8">
        <h2 class="text-2xl font-black text-slate-900 mb-3 flex items-center gap-3">
            <span class="w-10 h-10 rounded-xl bg-[#1FA7A2]/10 flex items-center justify-center text-[#1FA7A2] shadow-sm">
                <i class="fas fa-lock text-lg"></i>
            </span>
            {{ __('2FA Recovery Codes') }}
        </h2>
        <p class="text-slate-500 text-sm leading-relaxed max-w-2xl">
            {{ __('Recovery codes let you regain access if you lose your 2FA device. Store them in a secure password manager.') }}
        </p>
    </div>

    {{-- Actions & Content --}}
    <div class="relative z-10">
        
        {{-- Control Buttons --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            {{-- Show/Hide Button --}}
            <button type="button" 
                    @click="showRecoveryCodes = !showRecoveryCodes"
                    class="px-6 py-3 rounded-xl border-2 border-slate-100 text-slate-600 font-bold hover:border-[#1FA7A2] hover:text-[#1FA7A2] hover:bg-[#1FA7A2]/5 transition-all duration-300 flex items-center gap-2 w-full sm:w-auto justify-center">
                
                <i class="fas" :class="showRecoveryCodes ? 'fa-eye-slash' : 'fa-eye'"></i>
                
                {{-- استخدام x-show بدلاً من x-text لتجنب مشاكل الترجمة --}}
                <span x-show="!showRecoveryCodes">{{ __('View Recovery Codes') }}</span>
                <span x-show="showRecoveryCodes" style="display: none;">{{ __('Hide Recovery Codes') }}</span>
            </button>

            @if (count($recoveryCodes) > 0)
                {{-- Regenerate Button --}}
                <button type="button" 
                        x-show="showRecoveryCodes"
                        x-transition.opacity
                        wire:click="regenerateRecoveryCodes"
                        wire:loading.attr="disabled"
                        class="px-6 py-3 rounded-xl bg-slate-900 text-white font-bold shadow-lg hover:shadow-xl hover:bg-slate-800 transition-all duration-300 flex items-center gap-2 w-full sm:w-auto justify-center disabled:opacity-70">
                    
                    <i wire:loading.remove wire:target="regenerateRecoveryCodes" class="fas fa-sync-alt"></i>
                    <i wire:loading wire:target="regenerateRecoveryCodes" class="fas fa-spinner fa-spin"></i>
                    
                    {{ __('Regenerate Codes') }}
                </button>
            @endif
        </div>

        {{-- Codes Display Area --}}
        <div x-show="showRecoveryCodes"
             x-transition.duration.300ms
             id="recovery-codes-section"
             class="relative overflow-hidden">
            
            <div class="space-y-4">
                {{-- Success/Error Messages --}}
                <x-action-message on="regenerated">
                    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-600 font-bold text-sm flex items-center gap-2">
                        <i class="fas fa-check-circle"></i>
                        {{ __('Codes regenerated successfully.') }}
                    </div>
                </x-action-message>

                @if (count($recoveryCodes) > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-6 rounded-2xl bg-slate-50 border border-slate-100">
                        @foreach($recoveryCodes as $code)
                            <div class="flex items-center justify-center p-3 rounded-xl bg-white border border-slate-200 shadow-sm hover:border-[#1FA7A2] transition-colors group">
                                <span class="font-mono text-lg font-bold text-slate-700 tracking-wider select-all cursor-text group-hover:text-[#1FA7A2]">
                                    {{ $code }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                    <p class="text-xs font-bold text-slate-400 mt-4 flex items-start gap-2">
                        <i class="fas fa-info-circle mt-0.5 text-[#1FA7A2]"></i>
                        {{ __('Each recovery code can be used once to access your account and will be removed after use. If you need more, click Regenerate Codes above.') }}
                    </p>
                @else
                    <div class="p-6 rounded-2xl bg-slate-50 border border-slate-100 text-center text-slate-500">
                        {{ __('No recovery codes available. Please regenerate them.') }}
                    </div>
                @endif
            </div>
        </div>
        
        {{-- Placeholder when hidden --}}
        <div x-show="!showRecoveryCodes" 
             x-transition 
             class="p-8 rounded-2xl bg-slate-50 border border-slate-100 border-dashed text-center">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-slate-200 text-slate-400 mb-3">
                <i class="fas fa-shield-alt text-xl"></i>
            </div>
            <p class="text-slate-400 font-bold text-sm">{{ __('Codes are hidden for security') }}</p>
        </div>

    </div>
</div>