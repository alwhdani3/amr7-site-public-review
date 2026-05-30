<section class="w-full bg-white p-8 md:p-10 rounded-[2.5rem] shadow-xl border border-red-100 font-['Tajawal'] relative overflow-hidden">

    {{-- Background Decoration --}}
    <div class="absolute top-0 right-0 w-32 h-32 bg-red-50 rounded-bl-[4rem] -mr-8 -mt-8 pointer-events-none"></div>

    {{-- Header --}}
    <div class="relative z-10 mb-8">
        <h2 class="text-2xl font-black text-slate-900 mb-3 flex items-center gap-3">
            <span class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center text-red-500 shadow-sm">
                <i class="fas fa-trash-alt text-lg"></i>
            </span>
            {{ __('Delete Account') }}
        </h2>
        <p class="text-slate-500 text-sm leading-relaxed max-w-2xl">
            {{ __('Delete your account and all of its resources') }}
        </p>
    </div>

    {{-- Trigger Button --}}
    <div class="relative z-10">
        {{-- استخدام dispatch لفتح المودال بالاسم --}}
        <button x-data="" 
                x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')" 
                class="px-8 py-3 rounded-xl bg-red-50 text-red-600 font-bold border border-red-100 shadow-sm hover:bg-red-600 hover:text-white hover:shadow-lg hover:shadow-red-200 transition-all duration-300 transform hover:-translate-y-0.5 flex items-center gap-2">
            <i class="fas fa-exclamation-triangle"></i>
            {{ __('Delete Account') }}
        </button>
    </div>

    {{-- Modal --}}
    {{-- إضافة focusable لضمان إمكانية الوصول --}}
    <x-modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable>
        <form method="POST" wire:submit="deleteUser" class="p-8 bg-white font-['Tajawal'] text-center">
            
            {{-- Modal Header --}}
            <div class="mb-6 text-center">
                <div class="mx-auto w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mb-4 text-red-500 animate__animated animate__pulse">
                    <i class="fas fa-exclamation-triangle text-3xl"></i>
                </div>
                <h2 class="text-2xl font-black text-slate-900 mb-2">
                    {{ __('Are you sure you want to delete your account?') }}
                </h2>
                <p class="text-slate-500 text-sm leading-relaxed max-w-sm mx-auto">
                    {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                </p>
            </div>

            {{-- Password Input with Show/Hide Toggle --}}
            <div class="mb-8 relative max-w-md mx-auto" x-data="{ show: false }">
                <label for="password" class="sr-only">{{ __('Password') }}</label>
                
                <div class="relative">
                    {{-- أيقونة القفل (يمين في العربية، يسار في الإنجليزية) --}}
                    <div class="absolute inset-y-0 flex items-center px-4 pointer-events-none text-slate-400 rtl:right-0 ltr:left-0">
                        <i class="fas fa-lock"></i>
                    </div>

                    <input wire:model="password" 
                           id="password" 
                           name="password" 
                           :type="show ? 'text' : 'password'" 
                           class="block w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-red-500/20 focus:border-red-500 p-4 font-bold outline-none transition-all shadow-sm placeholder-slate-400 
                                  rtl:pr-12 rtl:pl-12 ltr:pl-12 ltr:pr-12"
                           placeholder="{{ __('Password') }}" />

                    {{-- زر العين (يسار في العربية، يمين في الإنجليزية) --}}
                    <button type="button" 
                            @click="show = !show" 
                            class="absolute inset-y-0 flex items-center px-4 text-slate-400 hover:text-red-500 transition-colors focus:outline-none rtl:left-0 ltr:right-0">
                        <i class="fas" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                    </button>
                </div>
                
                <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-500 text-xs font-bold text-center" />
            </div>

            {{-- Actions --}}
            <div class="flex justify-center gap-3">
                <button type="button" x-on:click="$dispatch('close')" class="h-12 px-6 rounded-xl border-2 border-slate-100 text-slate-600 font-bold hover:bg-slate-50 hover:text-slate-800 transition-all">
                    {{ __('Cancel') }}
                </button>

                <button type="submit" 
                        wire:loading.attr="disabled"
                        class="h-12 px-8 rounded-xl bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 shadow-lg shadow-red-200 text-white font-bold transition-all transform hover:-translate-y-0.5 disabled:opacity-50 flex items-center gap-2">
                    
                    <span wire:loading.remove wire:target="deleteUser">{{ __('Delete Account') }}</span>
                    <span wire:loading wire:target="deleteUser">
                        <i class="fas fa-spinner fa-spin"></i> {{ __('Deleting...') }}
                    </span>
                </button>
            </div>
        </form>
    </x-modal>
</section>