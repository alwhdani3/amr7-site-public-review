@if(($companies ?? collect())->isNotEmpty() && !empty($activeCompany))
    <div x-data="{ open: false }" @click.outside="open = false" class="relative inline-block text-left rtl:text-right z-50">
        <button
            @click="open = !open"
            type="button"
            class="flex items-center gap-3 bg-[#f8fafc] border border-gray-200 hover:bg-gray-50 rounded-xl px-3 py-2 transition duration-200 shadow-sm group focus:outline-none focus:ring-2 focus:ring-[#1FA7A2]/20"
        >
            <div class="w-9 h-9 rounded-lg bg-[#e0f2f1] text-[#1FA7A2] flex items-center justify-center font-bold text-sm shadow-sm group-hover:bg-[#1FA7A2] group-hover:text-white transition">
                {{ mb_substr($activeCompany->name ?? '-', 0, 1) }}
            </div>

            <div class="hidden md:block text-end rtl:text-left">
                <span class="block font-bold text-gray-800 text-xs truncate max-w-[120px]">
                    {{ \Illuminate\Support\Str::limit($activeCompany->name ?? '-', 20) }}
                </span>
                @if(!empty($activeCompany->cr_number))
                    <span class="block text-gray-400 text-[10px] font-medium tracking-wide">
                        {{ $activeCompany->cr_number }}
                    </span>
                @endif
            </div>

            <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform duration-200" :class="{'rotate-180': open}"></i>
        </button>

        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="transform opacity-0 scale-95"
            x-transition:enter-end="transform opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="transform opacity-100 scale-100"
            x-transition:leave-end="transform opacity-0 scale-95"
            class="absolute right-0 rtl:left-0 rtl:right-auto mt-2 w-72 bg-white rounded-xl shadow-xl border border-gray-100 z-[100] overflow-hidden origin-top-right rtl:origin-top-left"
            style="display: none;"
        >
            <div class="px-4 py-3 bg-gray-50 border-b border-gray-100 text-[#1FA7A2] font-bold text-sm flex items-center">
                <i class="fas fa-exchange-alt me-2 rtl:ml-2"></i>
                {{ __('company_switcher.switch_title') }}
            </div>

            <div class="max-h-60 overflow-y-auto custom-scrollbar">
                @foreach($companies as $c)
                    <form method="POST" action="{{ route('company.switch', $c->id) }}" class="block m-0 p-0">
                        @csrf
                        <input type="hidden" name="section" value="{{ request('section', 'documents') }}">

                        <button
                            type="submit"
                            class="w-full flex items-center justify-between px-4 py-3 text-sm text-gray-700 hover:bg-[#f0fdfa] transition duration-150 group {{ ($activeCompany->id ?? null) == $c->id ? 'bg-[#f8fafc]' : '' }}"
                        >
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-md flex items-center justify-center text-xs font-bold transition
                                            {{ ($activeCompany->id ?? null) == $c->id ? 'bg-[#1FA7A2] text-white' : 'bg-gray-100 text-gray-500 group-hover:bg-[#1FA7A2] group-hover:text-white' }}">
                                    {{ mb_substr($c->name ?? '-', 0, 1) }}
                                </div>

                                <div class="text-start rtl:text-right">
                                    <div class="font-bold text-gray-800 text-xs {{ ($activeCompany->id ?? null) == $c->id ? 'text-[#1FA7A2]' : '' }}">
                                        {{ $c->name }}
                                    </div>
                                    <div class="text-gray-400 text-[10px]">
                                        {{ $c->cr_number ?? '---' }}
                                    </div>
                                </div>
                            </div>

                            @if(($activeCompany->id ?? null) == $c->id)
                                <i class="fas fa-check-circle text-[#1FA7A2]"></i>
                            @endif
                        </button>
                    </form>
                @endforeach
            </div>

            <div class="border-t border-gray-100">
                <a href="{{ route('company.select') }}" class="block w-full text-center px-4 py-3 text-sm font-bold text-[#1FA7A2] hover:bg-gray-50 transition flex items-center justify-center gap-2">
                    <i class="fas fa-plus-circle"></i>
                    {{ __('company_switcher.add_new') }}
                </a>
            </div>
        </div>
    </div>
@endif
