{{--
    Etmam-parity: lightweight notification bell + dropdown for the dashboard topbar.

    Read-only aggregation over existing tables (no Laravel notifications, no new
    migrations). Mirrors the company-scoping patterns already used in
    App\Livewire\Dashboard so the badge stays consistent with the "Required
    Actions" card on the home section.

    Sources, all gated by the active company id:
      - CompanyDocument expiring within the next 30 days (or already expired)
      - Open Ticket rows (status != closed)
      - ServiceRequest rows the current user owns and is still active
        (status in new / in_review / pending_customer)
      - Invoice rows in issued / overdue state (guarded by Schema::hasTable so
        the partial degrades gracefully on environments where the billing
        migration hasn't run yet).

    Items link back to the matching ?section= in the dashboard, surfacing the
    full list via the existing "View all" pattern.
--}}

@php
    use App\Models\CompanyDocument;
    use App\Models\Invoice;
    use App\Models\ServiceRequest;
    use App\Models\Ticket;
    use Illuminate\Support\Facades\Schema;

    $activeCompanyId = session('active_company_id');
    if (! $activeCompanyId && auth()->check() && method_exists(auth()->user(), 'primaryCompany')) {
        $activeCompanyId = auth()->user()->primaryCompany()?->id;
    }

    $bellExpiringDocs = collect();
    $bellOpenTickets = collect();
    $bellPendingRequests = collect();
    $bellUnpaidInvoices = collect();

    $bellExpiringDocsTotal = 0;
    $bellOpenTicketsTotal = 0;
    $bellPendingRequestsTotal = 0;
    $bellUnpaidInvoicesTotal = 0;

    if ($activeCompanyId) {
        try {
            $bellExpiringDocsTotal = (int) CompanyDocument::where('company_id', $activeCompanyId)
                ->whereNotNull('expiry_date')
                ->whereDate('expiry_date', '<=', now()->addDays(30))
                ->count();

            $bellExpiringDocs = CompanyDocument::where('company_id', $activeCompanyId)
                ->whereNotNull('expiry_date')
                ->whereDate('expiry_date', '<=', now()->addDays(30))
                ->orderBy('expiry_date')
                ->limit(3)
                ->get(['id', 'type', 'expiry_date']);
        } catch (\Throwable $e) {
            // schema or driver mismatch — leave empty
        }

        try {
            $bellOpenTicketsTotal = (int) Ticket::where('company_id', $activeCompanyId)
                ->where('status', '!=', 'closed')
                ->count();

            $bellOpenTickets = Ticket::where('company_id', $activeCompanyId)
                ->where('status', '!=', 'closed')
                ->latest()
                ->limit(3)
                ->get(['id', 'subject', 'status', 'created_at']);
        } catch (\Throwable $e) {
        }

        try {
            $bellPendingRequestsTotal = (int) ServiceRequest::where('company_id', $activeCompanyId)
                ->where('user_id', auth()->id())
                ->whereIn('status', ['new', 'in_review', 'pending_customer'])
                ->count();

            $bellPendingRequests = ServiceRequest::with('service:id,title_ar,title_en,slug')
                ->where('company_id', $activeCompanyId)
                ->where('user_id', auth()->id())
                ->whereIn('status', ['new', 'in_review', 'pending_customer'])
                ->latest()
                ->limit(3)
                ->get(['id', 'service_id', 'status', 'created_at']);
        } catch (\Throwable $e) {
        }

        if (Schema::hasTable('invoices')) {
            try {
                $bellUnpaidInvoicesTotal = (int) Invoice::where('company_id', $activeCompanyId)
                    ->whereIn('status', [Invoice::STATUS_ISSUED, Invoice::STATUS_OVERDUE])
                    ->count();

                $bellUnpaidInvoices = Invoice::where('company_id', $activeCompanyId)
                    ->whereIn('status', [Invoice::STATUS_ISSUED, Invoice::STATUS_OVERDUE])
                    ->latest()
                    ->limit(3)
                    ->get(['id', 'invoice_number', 'total', 'currency', 'status', 'due_date']);
            } catch (\Throwable $e) {
            }
        }
    }

    $bellTotalCount = $bellExpiringDocsTotal + $bellOpenTicketsTotal
                    + $bellPendingRequestsTotal + $bellUnpaidInvoicesTotal;
    $bellHasItems = $bellTotalCount > 0;
    $bellDashboardUrl = route('dashboard');
    $bellLocale = app()->getLocale() === 'en' ? 'en' : 'ar';

    $bellTr = function (string $enKey, string $arFallback) {
        $value = __($enKey);
        return $value === $enKey ? $arFallback : $value;
    };

    $bellDocLabel = function ($doc) {
        $type = strtolower((string) ($doc->type ?? ''));
        $labels = [
            'cr'                              => 'سجل تجاري',
            'commercial_register'             => 'سجل تجاري',
            'commercial_register_certificate' => 'سجل تجاري',
            'tax'                             => 'زكاة وضريبة',
            'gosi'                            => 'شهادة التأمينات',
            'medical_insurance'               => 'وثيقة التأمين الطبي',
            'national_address'                => 'العنوان الوطني',
            'articles_of_association'         => 'عقد التأسيس',
            'saudization'                     => 'شهادة سعودة',
        ];
        return $labels[$type] ?? ($type ?: 'وثيقة');
    };

    $bellRequestTitle = function ($request) use ($bellLocale) {
        $service = $request->service ?? null;
        if (! $service) return 'طلب خدمة';
        return $bellLocale === 'en'
            ? ($service->title_en ?? $service->title_ar ?? $service->slug ?? 'Service Request')
            : ($service->title_ar ?? $service->title_en ?? $service->slug ?? 'طلب خدمة');
    };
@endphp

<div x-data="{ bellOpen: false }" @click.outside="bellOpen = false" class="relative">
    <button type="button"
            x-on:click="bellOpen = !bellOpen"
            class="relative inline-flex items-center justify-center w-9 h-9 rounded-xl text-slate-600 hover:bg-slate-50 border border-slate-100"
            :aria-expanded="bellOpen.toString()"
            aria-haspopup="true"
            title="{{ $bellTr('Notifications', 'الإشعارات') }}">
        <i class="fas fa-bell text-[14px]"></i>
        @if($bellHasItems)
            <span class="absolute -top-1 -end-1 min-w-[18px] h-[18px] px-1 rounded-full bg-rose-500 text-white text-[10px] font-black flex items-center justify-center ring-2 ring-white">
                {{ $bellTotalCount > 99 ? '99+' : $bellTotalCount }}
            </span>
        @endif
    </button>

    <div x-show="bellOpen" x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="absolute end-0 mt-2 w-80 md:w-96 bg-white rounded-2xl shadow-xl border border-slate-100 z-[9500] overflow-hidden"
         role="menu"
         aria-label="{{ $bellTr('Notifications', 'الإشعارات') }}">

        <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between bg-slate-50/40">
            <div class="flex items-center gap-2">
                <i class="fas fa-bell text-[#1FA7A2] text-[12px]"></i>
                <h3 class="text-sm font-black text-[#0A2540]">{{ $bellTr('Notifications', 'الإشعارات') }}</h3>
            </div>
            @if($bellHasItems)
                <span class="text-[10px] font-black px-2 py-0.5 rounded-md bg-rose-50 text-rose-600 border border-rose-100">
                    {{ $bellTotalCount }}
                </span>
            @endif
        </div>

        @unless($bellHasItems)
            <div class="px-6 py-10 text-center">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-500 mx-auto mb-3">
                    <i class="fas fa-circle-check"></i>
                </div>
                <p class="text-sm font-black text-slate-700">{{ $bellTr('All caught up', 'لا توجد تنبيهات حالياً') }}</p>
                <p class="text-[11px] text-slate-500 font-medium mt-1">{{ $bellTr('You will see new alerts here as they appear.', 'ستظهر هنا أي تنبيهات جديدة فور حدوثها.') }}</p>
            </div>
        @else
            <div class="max-h-[420px] overflow-y-auto divide-y divide-slate-100">

                {{-- Documents expiring soon --}}
                @if($bellExpiringDocs->isNotEmpty())
                    <div class="px-4 py-3">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <span class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                                    <i class="fas fa-clock text-[12px]"></i>
                                </span>
                                <h4 class="text-[12px] font-black text-[#0A2540]">{{ $bellTr('Documents expiring soon', 'وثائق على وشك الانتهاء') }}</h4>
                                @if($bellExpiringDocsTotal > $bellExpiringDocs->count())
                                    <span class="text-[10px] font-black text-slate-400">+{{ $bellExpiringDocsTotal - $bellExpiringDocs->count() }}</span>
                                @endif
                            </div>
                            <a href="{{ $bellDashboardUrl }}?section=compliance" wire:navigate
                               class="text-[10px] font-black text-[#1FA7A2] hover:underline">{{ __('view_all') }}</a>
                        </div>
                        <ul class="space-y-1">
                            @foreach($bellExpiringDocs as $doc)
                                @php
                                    $expiry = $doc->expiry_date instanceof \Carbon\CarbonInterface
                                        ? $doc->expiry_date
                                        : \Carbon\Carbon::parse($doc->expiry_date);
                                    $isPast = $expiry->isPast();
                                @endphp
                                <li>
                                    <a href="{{ $bellDashboardUrl }}?section=compliance" wire:navigate
                                       class="flex items-center justify-between gap-2 px-2 py-1.5 rounded-lg hover:bg-slate-50 transition-colors">
                                        <span class="text-[12px] font-bold text-slate-700 truncate flex-1">{{ $bellDocLabel($doc) }}</span>
                                        <span class="text-[10px] font-black {{ $isPast ? 'text-rose-600' : 'text-amber-600' }} shrink-0">
                                            {{ $expiry->translatedFormat('d M') }}
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Pending service requests --}}
                @if($bellPendingRequests->isNotEmpty())
                    <div class="px-4 py-3">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <span class="w-7 h-7 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center shrink-0">
                                    <i class="fas fa-clipboard-list text-[12px]"></i>
                                </span>
                                <h4 class="text-[12px] font-black text-[#0A2540]">{{ $bellTr('Pending service requests', 'طلبات خدمة قيد المعالجة') }}</h4>
                                @if($bellPendingRequestsTotal > $bellPendingRequests->count())
                                    <span class="text-[10px] font-black text-slate-400">+{{ $bellPendingRequestsTotal - $bellPendingRequests->count() }}</span>
                                @endif
                            </div>
                            <a href="{{ $bellDashboardUrl }}?section=request-history" wire:navigate
                               class="text-[10px] font-black text-[#1FA7A2] hover:underline">{{ __('view_all') }}</a>
                        </div>
                        <ul class="space-y-1">
                            @foreach($bellPendingRequests as $request)
                                <li>
                                    <a href="{{ $bellDashboardUrl }}?section=request-history&request_id={{ $request->id }}" wire:navigate
                                       class="flex items-center justify-between gap-2 px-2 py-1.5 rounded-lg hover:bg-slate-50 transition-colors">
                                        <span class="text-[12px] font-bold text-slate-700 truncate flex-1">{{ $bellRequestTitle($request) }}</span>
                                        <span class="font-mono text-[10px] text-slate-400 shrink-0">#{{ $request->id }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Open tickets --}}
                @if($bellOpenTickets->isNotEmpty())
                    <div class="px-4 py-3">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <span class="w-7 h-7 rounded-lg bg-[#1FA7A2]/10 text-[#1FA7A2] flex items-center justify-center shrink-0">
                                    <i class="fas fa-headset text-[12px]"></i>
                                </span>
                                <h4 class="text-[12px] font-black text-[#0A2540]">{{ $bellTr('Open support tickets', 'تذاكر دعم مفتوحة') }}</h4>
                                @if($bellOpenTicketsTotal > $bellOpenTickets->count())
                                    <span class="text-[10px] font-black text-slate-400">+{{ $bellOpenTicketsTotal - $bellOpenTickets->count() }}</span>
                                @endif
                            </div>
                            <a href="{{ $bellDashboardUrl }}?section=tickets" wire:navigate
                               class="text-[10px] font-black text-[#1FA7A2] hover:underline">{{ __('view_all') }}</a>
                        </div>
                        <ul class="space-y-1">
                            @foreach($bellOpenTickets as $ticket)
                                <li>
                                    <a href="{{ $bellDashboardUrl }}?section=tickets&ticket_id={{ $ticket->id }}" wire:navigate
                                       class="flex items-center justify-between gap-2 px-2 py-1.5 rounded-lg hover:bg-slate-50 transition-colors">
                                        <span class="text-[12px] font-bold text-slate-700 truncate flex-1">{{ $ticket->subject ?: ('#' . $ticket->id) }}</span>
                                        <span class="font-mono text-[10px] text-slate-400 shrink-0">#{{ $ticket->id }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Unpaid invoices --}}
                @if($bellUnpaidInvoices->isNotEmpty())
                    <div class="px-4 py-3">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <span class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                                    <i class="fas fa-file-invoice-dollar text-[12px]"></i>
                                </span>
                                <h4 class="text-[12px] font-black text-[#0A2540]">{{ $bellTr('Unpaid invoices', 'فواتير غير مدفوعة') }}</h4>
                                @if($bellUnpaidInvoicesTotal > $bellUnpaidInvoices->count())
                                    <span class="text-[10px] font-black text-slate-400">+{{ $bellUnpaidInvoicesTotal - $bellUnpaidInvoices->count() }}</span>
                                @endif
                            </div>
                            <a href="{{ $bellDashboardUrl }}?section=invoices" wire:navigate
                               class="text-[10px] font-black text-[#1FA7A2] hover:underline">{{ __('view_all') }}</a>
                        </div>
                        <ul class="space-y-1">
                            @foreach($bellUnpaidInvoices as $invoice)
                                <li>
                                    <a href="{{ $bellDashboardUrl }}?section=invoices" wire:navigate
                                       class="flex items-center justify-between gap-2 px-2 py-1.5 rounded-lg hover:bg-slate-50 transition-colors">
                                        <span class="text-[12px] font-bold text-slate-700 truncate flex-1 font-mono">{{ $invoice->invoice_number ?: ('#' . $invoice->id) }}</span>
                                        <span class="text-[10px] font-black text-rose-600 shrink-0">{{ number_format((float) ($invoice->total ?? 0), 2) }} {{ $invoice->currency ?? 'SAR' }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endunless
    </div>
</div>
