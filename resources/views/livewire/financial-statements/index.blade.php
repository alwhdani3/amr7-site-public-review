<div class="min-h-screen bg-slate-50 pt-24 pb-20 font-['Tajawal'] relative overflow-x-hidden" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    
    <div class="fixed inset-0 pointer-events-none z-0">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-full bg-[radial-gradient(circle_at_top,_var(--tw-gradient-stops))] from-[#1FA7A2]/5 via-transparent to-transparent"></div>
    </div>

    <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 mb-16">
        
        <div class="text-center mb-12 reveal opacity-0 translate-y-8 transition-all duration-700 ease-out">
            
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-[#1FA7A2]/5 border border-[#1FA7A2]/10 text-[#1FA7A2] text-sm font-bold mb-6">
                <i class="fas fa-check-circle"></i>
                {{ __('fs_portal_badge') }}
            </div>

            <h1 class="text-4xl md:text-6xl font-black text-slate-900 mb-6 leading-tight tracking-tight">
                {{ __('fs_portal_title') }}
            </h1>
            
            <p class="text-lg md:text-xl text-slate-500 max-w-3xl mx-auto mb-12 leading-relaxed">
                {{ __('fs_portal_subtitle') }}
            </p>

            <div class="max-w-2xl mx-auto bg-white rounded-3xl shadow-xl border border-slate-100 p-2 md:p-3 relative overflow-visible z-20">
                <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-[#1FA7A2] text-white px-4 py-1 rounded-full text-xs font-bold shadow-md whitespace-nowrap">
                    {{ __('fs_track_title') }}
                </div>

                <div class="relative flex items-center mt-3 md:mt-0">
                    <div class="absolute rtl:right-5 ltr:left-5 text-slate-400 text-lg pointer-events-none">
                        <i class="fas fa-hashtag"></i>
                    </div>

                    <input type="text" 
                           class="w-full h-14 bg-slate-50 border-0 rounded-2xl rtl:pr-12 ltr:pl-12 rtl:pl-36 ltr:pr-36 text-slate-800 font-bold placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-[#1FA7A2]/20 transition-all outline-none shadow-inner" 
                           placeholder="{{ __('fs_track_placeholder') }}..." 
                           wire:model.defer="search"
                           autocomplete="off">
                    
                    <button wire:click="trackRequest" class="absolute rtl:left-1.5 ltr:right-1.5 top-1.5 bottom-1.5 bg-[#1FA7A2] hover:bg-[#167F7B] text-white px-6 rounded-xl font-bold transition-all shadow-md hover:shadow-lg flex items-center gap-2">
                        <span>{{ __('btn_track_now') }}</span>
                    </button>
                </div>
            </div>

            @guest
                <div class="mt-12 flex flex-wrap justify-center gap-4 reveal delay-100">
                    <a href="{{ route('financial-statements.create') }}" 
                       class="group px-8 py-4 rounded-full bg-gradient-to-r from-[#1FA7A2] to-[#167F7B] text-white font-bold text-lg shadow-lg hover:shadow-teal-500/30 hover:-translate-y-1 transition-all duration-300 flex items-center gap-3">
                        <i class="fas fa-plus-circle text-white/80 group-hover:text-white transition-colors"></i> 
                        {{ __('btn_start_new_request') }}
                    </a>

                    <a href="{{ url('/login') }}" 
                       class="group px-8 py-4 rounded-full bg-white text-slate-700 border-2 border-slate-100 font-bold text-lg shadow-sm hover:border-[#1FA7A2] hover:text-[#1FA7A2] hover:-translate-y-1 transition-all duration-300 flex items-center gap-3">
                        <i class="fas fa-user-lock text-slate-400 group-hover:text-[#1FA7A2] transition-colors"></i> 
                        {{ __('btn_member_login') }}
                    </a>
                </div>
            @endguest
        </div>
    </div>

    @auth
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-12 animate__animated animate__fadeInUp">
        
        <div class="bg-white border border-slate-200 rounded-[2rem] shadow-sm overflow-hidden">
            
            <div class="p-6 md:p-8 border-b border-slate-100">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div>
                        <h2 class="text-2xl font-black text-slate-800 flex items-center gap-2">
                            <i class="fas fa-folder-open text-[#1FA7A2]"></i>
                            {{ __('financial_requests_title') }}
                        </h2>
                        <p class="text-slate-500 text-sm mt-1">
                            {{ __('financial_requests_desc') }}
                        </p>
                    </div>

                    <a href="{{ route('financial-statements.create') }}" 
                       class="inline-flex items-center justify-center gap-2 bg-slate-900 hover:bg-slate-800 text-white px-5 py-2.5 rounded-xl font-bold text-sm transition-all shadow-md hover:shadow-lg">
                        <i class="fas fa-plus"></i> {{ __('btn_new_request') }}
                    </a>
                </div>

                <div class="mt-8 flex flex-col lg:flex-row gap-4">
                    <div class="relative w-full lg:w-1/3">
                        <input type="text" wire:model.live.debounce.300ms="search" 
                               class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-[#1FA7A2] focus:border-[#1FA7A2] block px-4 py-3 rtl:pl-10 ltr:pr-10" 
                               placeholder="{{ __('Search by ID, Company or CR...') }}">
                        <div class="absolute inset-y-0 rtl:left-0 ltr:right-0 flex items-center px-3 pointer-events-none text-slate-400">
                            <i class="fas fa-search"></i>
                        </div>
                    </div>

                    <div class="flex-1 overflow-x-auto no-scrollbar pb-1">
                        <div class="flex gap-2">
                            @foreach($this->statuses() as $key => $label)
                                <button wire:click="$set('status', '{{ $key }}')"
                                        class="whitespace-nowrap px-4 py-2 rounded-lg text-sm font-bold transition-all border
                                        {{ $status === $key 
                                            ? 'bg-[#1FA7A2] text-white border-[#1FA7A2] shadow-md' 
                                            : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50 hover:border-slate-300' }}">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="relative">
                <div wire:loading wire:target="search, status, gotoPage, previousPage, nextPage" 
                     class="absolute inset-0 bg-white/80 z-50 flex items-center justify-center backdrop-blur-sm rounded-b-[2rem]">
                    <div class="flex flex-col items-center">
                        <i class="fas fa-circle-notch fa-spin text-3xl text-[#1FA7A2]"></i>
                        <span class="text-sm font-bold text-slate-500 mt-2">{{ __('loading') }}</span>
                    </div>
                </div>

                @if($requests->count() > 0)
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full text-sm text-right text-slate-600">
                            <thead class="text-xs text-slate-400 uppercase bg-slate-50/50 border-b border-slate-100">
                                <tr>
                                    <th scope="col" class="px-6 py-4 font-bold text-start">{{ __('table_ref') }}</th>
                                    <th scope="col" class="px-6 py-4 font-bold text-start">{{ __('table_company') }}</th>
                                    <th scope="col" class="px-6 py-4 font-bold text-start">{{ __('table_date') }}</th>
                                    <th scope="col" class="px-6 py-4 font-bold text-center">{{ __('table_status') }}</th>
                                    <th scope="col" class="px-6 py-4 font-bold text-center">{{ __('table_actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($requests as $req)
                                    <tr class="bg-white hover:bg-[#f0fdfa]/30 transition-colors group">
                                        <td class="px-6 py-4 font-bold text-slate-800 text-start">
                                            <span class="font-mono text-[#1FA7A2]">#{{ $req->public_id ?? $req->id }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-start">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
                                                    <i class="fas fa-building text-xs"></i>
                                                </div>
                                                <div>
                                                    <div class="font-bold text-slate-800">{{ $req->company_name }}</div>
                                                    <div class="text-xs text-slate-400 font-mono">{{ $req->cr_number }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-start">
                                            <div class="flex flex-col">
                                                <span class="font-bold text-slate-700">{{ $req->created_at->format('Y-m-d') }}</span>
                                                <span class="text-xs text-slate-400">{{ $req->created_at->diffForHumans() }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            @php
                                                $statusColors = [
                                                    'new' => 'bg-blue-50 text-blue-700 border-blue-100',
                                                    'waiting_docs' => 'bg-amber-50 text-amber-700 border-amber-100',
                                                    'in_review' => 'bg-purple-50 text-purple-700 border-purple-100',
                                                    'client_approval' => 'bg-orange-50 text-orange-700 border-orange-100',
                                                    'moc_approval' => 'bg-indigo-50 text-indigo-700 border-indigo-100',
                                                    'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                                    'closed' => 'bg-slate-100 text-slate-600 border-slate-200',
                                                    'cancelled' => 'bg-red-50 text-red-700 border-red-100',
                                                ];
                                                $colorClass = $statusColors[$req->status] ?? 'bg-slate-50 text-slate-600 border-slate-200';
                                            @endphp
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $colorClass }}">
                                                {{ $this->statuses()[$req->status] ?? $req->status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <a href="{{ route('financial-statements.show', $req->public_id ?? $req->id) }}" 
                                               wire:navigate
                                               class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-white border border-slate-200 text-slate-400 hover:text-[#1FA7A2] hover:border-[#1FA7A2] transition-all shadow-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="md:hidden grid grid-cols-1 gap-4 p-4 bg-slate-50/50">
                        @foreach($requests as $req)
                            <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm hover:border-[#1FA7A2]/30 transition-all">
                                <div class="flex justify-between items-start mb-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-[#f0fdfa] text-[#1FA7A2] flex items-center justify-center border border-[#ccfbf1]">
                                            <i class="fas fa-file-invoice"></i>
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-800 text-sm">{{ $req->company_name }}</div>
                                            <div class="text-xs text-slate-400 font-mono">#{{ $req->public_id ?? $req->id }}</div>
                                        </div>
                                    </div>
                                    @php
                                        $colorClass = match($req->status) {
                                            'completed' => 'text-emerald-600 bg-emerald-50 border border-emerald-100',
                                            'new' => 'text-blue-600 bg-blue-50 border border-blue-100',
                                            'cancelled' => 'text-red-600 bg-red-50 border border-red-100',
                                            default => 'text-amber-600 bg-amber-50 border border-amber-100',
                                        };
                                    @endphp
                                    <span class="px-2 py-1 rounded-lg text-xs font-bold {{ $colorClass }}">
                                        {{ $this->statuses()[$req->status] ?? $req->status }}
                                    </span>
                                </div>
                                
                                <div class="flex justify-between items-center pt-3 border-t border-slate-50 mt-2">
                                    <span class="text-xs text-slate-400 font-bold">
                                        <i class="far fa-calendar-alt me-1"></i> {{ $req->created_at->format('Y-m-d') }}
                                    </span>
                                    <a href="{{ route('financial-statements.show', $req->public_id ?? $req->id) }}" 
                                       wire:navigate
                                       class="text-xs font-bold text-[#1FA7A2] hover:underline">
                                        {{ __('btn_view_details') }} <i class="fas fa-arrow-left rtl:mr-1 ltr:ml-1"></i>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="p-6 border-t border-slate-100 bg-slate-50/30 rounded-b-[2rem]">
                        {{ $requests->links(data: ['scrollTo' => false]) }} 
                    </div>

                @else
                    <div class="py-20 text-center flex flex-col items-center justify-center">
                        <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-inbox text-4xl text-slate-300"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-800">{{ __('no_requests_found') }}</h3>
                        @if($search || $status !== 'all')
                            <button wire:click="$set('search', ''); $set('status', 'all')" 
                                    class="mt-4 text-[#1FA7A2] font-bold text-sm hover:underline">
                                {{ __('Clear search') }}
                            </button>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endauth

    <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 mt-20">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm hover:shadow-xl hover:border-[#1FA7A2]/20 hover:-translate-y-2 transition-all duration-300 group reveal opacity-0 translate-y-8">
                <div class="w-14 h-14 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-cloud-upload-alt"></i>
                </div>
                <h2 class="text-xl font-extrabold text-slate-800 mb-3">{{ __('feature_archiving_title') }}</h2>
                <p class="text-slate-500 leading-relaxed mb-6">{{ __('feature_archiving_desc') }}</p>
            </div>

            <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm hover:shadow-xl hover:border-[#1FA7A2]/20 hover:-translate-y-2 transition-all duration-300 group reveal opacity-0 translate-y-8 delay-200">
                <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-sync-alt"></i>
                </div>
                <h2 class="text-xl font-extrabold text-slate-800 mb-3">{{ __('feature_updates_title') }}</h2>
                <p class="text-slate-500 leading-relaxed mb-6">{{ __('feature_updates_desc') }}</p>
            </div>

            <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm hover:shadow-xl hover:border-[#1FA7A2]/20 hover:-translate-y-2 transition-all duration-300 group reveal opacity-0 translate-y-8 delay-300">
                <div class="w-14 h-14 rounded-2xl bg-teal-50 text-[#1FA7A2] flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <h2 class="text-xl font-extrabold text-slate-800 mb-3">{{ __('feature_compliance_title') }}</h2>
                <p class="text-slate-500 leading-relaxed mb-6">{{ __('feature_compliance_desc') }}</p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.remove('opacity-0', 'translate-y-8');
                        entry.target.classList.add('opacity-100', 'translate-y-0');
                    }
                });
            }, { threshold: 0.1 });
            document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
        });
    </script>
</div>