<div class="bg-white rounded-[2.5rem] shadow-xl border border-slate-100 overflow-hidden font-['Tajawal']">
    
    {{-- Header & Filters --}}
    <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex flex-wrap items-center justify-between gap-4">
        <h3 class="font-bold text-slate-900 text-lg flex items-center gap-2">
            <i class="fas fa-list text-[#1FA7A2]"></i>
            قائمة الطلبات
        </h3>

        {{-- Filter Select --}}
        <div class="relative min-w-[200px]">
            <select wire:model.live="status" 
                    class="appearance-none w-full bg-white border border-slate-200 text-slate-700 text-sm rounded-xl focus:ring-2 focus:ring-[#1FA7A2]/20 focus:border-[#1FA7A2] block p-3 pe-10 font-bold outline-none cursor-pointer transition-all shadow-sm">
                <option value="all">كل الحالات</option>
                @foreach($statusLabels as $k => $label)
                    <option value="{{ $k }}">{{ $label }}</option>
                @endforeach
            </select>
            <div class="absolute inset-y-0 left-0 flex items-center px-3 pointer-events-none text-slate-400">
                <i class="fas fa-chevron-down text-xs"></i>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-right text-slate-500">
            <thead class="text-xs text-slate-400 uppercase bg-slate-50 border-b border-slate-100">
                <tr>
                    <th scope="col" class="px-6 py-4 font-bold rounded-tr-[2.5rem]">رقم الطلب</th>
                    <th scope="col" class="px-6 py-4 font-bold">المنشأة</th>
                    <th scope="col" class="px-6 py-4 font-bold">السنة المالية</th>
                    <th scope="col" class="px-6 py-4 font-bold">الحالة</th>
                    <th scope="col" class="px-6 py-4 font-bold rounded-tl-[2.5rem]">الإجراء</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($requests as $r)
                    <tr class="bg-white hover:bg-[#1FA7A2]/5 transition-colors group">
                        
                        {{-- Ticket No --}}
                        <td class="px-6 py-4 font-bold text-[#1FA7A2] font-mono">
                            #{{ $r->ticket_no }}
                        </td>

                        {{-- Company --}}
                        <td class="px-6 py-4 font-bold text-slate-800">
                            {{ $r->company_name ?? '—' }}
                        </td>

                        {{-- Year --}}
                        <td class="px-6 py-4 font-medium font-mono text-slate-600">
                            {{ $r->fiscal_year ?? '—' }}
                        </td>

                        {{-- Status --}}
                        <td class="px-6 py-4">
                            @php
                                $statusConfig = [
                                    'new' => 'bg-sky-50 text-sky-600 border-sky-100',
                                    'in_review' => 'bg-amber-50 text-amber-600 border-amber-100',
                                    'waiting_docs' => 'bg-red-50 text-red-600 border-red-100',
                                    'completed' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                ];
                                $classes = $statusConfig[$r->status] ?? 'bg-slate-100 text-slate-500 border-slate-200';
                            @endphp
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $classes }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-current me-2 opacity-60"></span>
                                {{ $statusLabels[$r->status] ?? $r->status }}
                            </span>
                        </td>

                        {{-- Actions --}}
                        <td class="px-6 py-4">
                            <a href="{{ route('financial-statements.show', $r) }}" class="inline-flex items-center gap-2 text-sm font-bold text-slate-400 hover:text-[#1FA7A2] transition-colors">
                                عرض التفاصيل
                                <i class="fas fa-arrow-left text-xs transition-transform group-hover:-translate-x-1"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mb-3 text-slate-300">
                                    <i class="fas fa-inbox text-3xl"></i>
                                </div>
                                <p class="text-slate-500 font-bold">لا توجد طلبات حتى الآن</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($requests->hasPages())
        <div class="p-6 border-t border-slate-100 bg-slate-50/50">
            {{ $requests->links() }}
        </div>
    @endif
</div>