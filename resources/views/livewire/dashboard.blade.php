<div class="min-h-screen bg-slate-50 font-['Tajawal'] text-slate-800 relative" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 20px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background-color: #94a3b8; }
    </style>

    <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 animate__animated animate__fadeIn overflow-x-hidden">

        {{-- Phase 2: تم حذف الـpage header المكرّر مع native select شركات — الـtopbar في
             portal shell يعرض اسم المنشأة، وزر "تبديل المنشأة" يقود إلى صفحة company.select.
             الـstats انتقلت داخل home section فقط (لا تتكرّر على كل قسم). --}}

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm relative">

            <div wire:loading.flex wire:target="section" class="absolute inset-0 bg-white/80 backdrop-blur-sm z-50 flex items-center justify-center rounded-[2rem]">
                <div class="flex flex-col items-center gap-3">
                    <i class="fas fa-circle-notch fa-spin text-4xl text-[#1FA7A2]"></i>
                    <span class="text-sm font-bold text-slate-500">{{ __('loading') }}</span>
                </div>
            </div>

            {{-- 0. Home / Overview Section — Phase 2: compact، بدون gradient ضخم --}}
            @if($section === 'home')
                @php
                    $homeUser = auth()->user();
                    $homeCompany = $this->activeCompany;
                    $homeCompletion = $homeCompany ? (int) $homeCompany->profile_completion_percent : 0;
                    $isBackoffice = $homeUser && method_exists($homeUser, 'hasBackofficeAccess') && $homeUser->hasBackofficeAccess();
                    $actions = $this->requiredActions;
                    $homeStats = $this->stats;
                @endphp
                <div class="p-5 animate__animated animate__fadeIn space-y-5">

                    {{-- Complete-profile banner — يظهر فقط إذا فيه منشأة نشطة والملف غير مكتمل --}}
                    @if($homeCompany && $homeCompletion < 100)
                        <div class="rounded-2xl border border-amber-200 bg-amber-50/70 p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div class="flex items-start gap-3 min-w-0">
                                <div class="w-10 h-10 shrink-0 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center">
                                    <i class="fas fa-clipboard-check"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-black text-amber-900 text-sm sm:text-base">{{ __('complete_profile_banner_title') ?: 'أكمل بيانات المنشأة' }}</p>
                                    <p class="text-xs sm:text-sm text-amber-800/80 font-medium mt-1 leading-6">
                                        {{ __('complete_profile_banner_hint') ?: 'اكتمال الملف يساعدنا في تقديم خدماتك بسرعة ودقة أعلى.' }}
                                        <span class="font-black text-amber-900">{{ $homeCompletion }}%</span>
                                    </p>
                                </div>
                            </div>
                            <a href="{{ route('dashboard') }}?section=profile" wire:navigate
                               class="shrink-0 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-sm shadow-sm transition-colors">
                                <span>{{ __('go_to_company_profile') ?: 'فتح ملف المنشأة' }}</span>
                                <i class="fas fa-arrow-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }} text-[11px]"></i>
                            </a>
                        </div>
                    @endif

                    {{-- Welcome bar — slim بلا gradient، يضم اسم المستخدم + الشركة + completion --}}
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div class="min-w-0">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">{{ __('welcome_back') }}</p>
                                <h2 class="text-xl md:text-2xl font-black text-[#0A2540] tracking-tight truncate">{{ $homeUser->name ?? '—' }}</h2>
                                @if($homeCompany)
                                    <div class="mt-1.5 flex items-center flex-wrap gap-2 text-[12px] font-bold text-slate-600">
                                        <i class="fas fa-building text-[#1FA7A2] text-[11px]"></i>
                                        <span class="truncate max-w-[260px]">{{ $homeCompany->name }}</span>
                                        @if($homeCompany->status)
                                            <span class="px-2 py-0.5 rounded-md text-[10px] bg-slate-50 border border-slate-100 text-slate-600 font-bold">{{ $this->translateStatus($homeCompany->status) }}</span>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            {{-- Compact completion + actions cluster --}}
                            <div class="flex items-center gap-3 shrink-0">
                                @if($homeCompany)
                                    <div class="text-end">
                                        <div class="text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ __('company_profile_status') }}</div>
                                        <div class="text-2xl font-black text-[#0A2540] leading-none mt-0.5">{{ $homeCompletion }}<span class="text-sm text-slate-400">%</span></div>
                                    </div>
                                    <a href="{{ route('dashboard') }}?section=profile" wire:navigate
                                       class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-[12px] font-bold text-[#0A2540] bg-[#0A2540]/5 hover:bg-[#0A2540]/10 border border-[#0A2540]/10">
                                        <i class="fas fa-arrow-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }} text-[10px]"></i>
                                        <span>{{ __('go_to_company_profile') ?: 'فتح ملف المنشأة' }}</span>
                                    </a>
                                @endif
                            </div>
                        </div>

                        @if($homeCompany)
                            {{-- Progress bar تحت الـrow --}}
                            <div class="mt-3 w-full h-1 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-l from-[#1FA7A2] to-[#1FA7A2] transition-all duration-700" style="width: {{ $homeCompletion }}%"></div>
                            </div>
                        @endif
                    </div>

                    {{-- Required Actions + Quick Actions في صف grid --}}
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

                        {{-- Required Actions (col-span-2) — كل سطر clickable كامل --}}
                        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-sm font-black text-[#0A2540] flex items-center gap-2">
                                    <i class="fas fa-circle-exclamation text-amber-500 text-[13px]"></i>
                                    {{ __('required_actions') }}
                                </h3>
                                @if(! empty($actions))
                                    <span class="text-[10px] font-black px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 border border-amber-100">{{ count($actions) }}</span>
                                @endif
                            </div>

                            @if(empty($actions))
                                <div class="py-5 text-center">
                                    <div class="w-10 h-10 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-500 mx-auto mb-2">
                                        <i class="fas fa-circle-check"></i>
                                    </div>
                                    <p class="text-sm font-black text-slate-700 mb-0.5">{{ __('no_required_actions') }}</p>
                                    <p class="text-[11px] text-slate-500 font-medium">{{ __('no_required_actions_hint') }}</p>
                                </div>
                            @else
                                <ul class="divide-y divide-slate-100 -mx-2">
                                    @foreach($actions as $a)
                                        @php
                                            $sectionTarget = $a['cta_section'] ?? 'home';
                                            $dotClass = match($a['severity']) {
                                                'high'   => 'bg-rose-500',
                                                'medium' => 'bg-amber-500',
                                                default  => 'bg-slate-400',
                                            };
                                            $countClass = match($a['severity']) {
                                                'high'   => 'bg-rose-50 text-rose-600 border-rose-100',
                                                'medium' => 'bg-amber-50 text-amber-700 border-amber-100',
                                                default  => 'bg-slate-50 text-slate-600 border-slate-100',
                                            };
                                        @endphp
                                        <li>
                                            <a href="{{ route('dashboard') }}?section={{ $sectionTarget }}"
                                               wire:navigate
                                               class="flex items-center justify-between gap-3 px-2 py-2.5 rounded-lg hover:bg-slate-50 transition-colors group">
                                                <div class="flex items-center gap-3 min-w-0">
                                                    <span class="w-2 h-2 rounded-full {{ $dotClass }} shrink-0"></span>
                                                    <p class="text-[13px] font-bold text-slate-800 truncate group-hover:text-[#0A2540]">{{ $a['label'] }}</p>
                                                </div>
                                                <div class="flex items-center gap-2 shrink-0">
                                                    <span class="text-[10px] font-black px-2 py-0.5 rounded-md border {{ $countClass }}">{{ $a['count'] }}</span>
                                                    <i class="fas fa-arrow-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }} text-slate-300 group-hover:text-[#0A2540] text-[10px] transition-colors"></i>
                                                </div>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>

                        {{-- Quick Actions --}}
                        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                            <h3 class="text-sm font-black text-[#0A2540] mb-3 flex items-center gap-2">
                                <i class="fas fa-bolt text-[#1FA7A2] text-[13px]"></i>
                                {{ __('quick_actions') }}
                            </h3>
                            <div class="space-y-1.5">
                                <button type="button" x-data x-on:click="$dispatch('open-modal', 'add-doc')" class="w-full flex items-center gap-2.5 p-2 rounded-xl hover:bg-slate-50 border border-slate-100 text-start transition-all group">
                                    <div class="w-8 h-8 rounded-lg bg-[#0A2540]/5 group-hover:bg-[#0A2540] text-[#0A2540] group-hover:text-white flex items-center justify-center transition-all shrink-0">
                                        <i class="fas fa-cloud-upload-alt text-[11px]"></i>
                                    </div>
                                    <span class="text-[12px] font-bold text-slate-700 truncate">{{ __('upload_document_action') }}</span>
                                </button>

                                <button type="button" x-data x-on:click="$dispatch('open-modal', 'create-ticket')" class="w-full flex items-center gap-2.5 p-2 rounded-xl hover:bg-slate-50 border border-slate-100 text-start transition-all group">
                                    <div class="w-8 h-8 rounded-lg bg-[#1FA7A2]/10 group-hover:bg-[#1FA7A2] text-[#1FA7A2] group-hover:text-white flex items-center justify-center transition-all shrink-0">
                                        <i class="fas fa-headset text-[11px]"></i>
                                    </div>
                                    <span class="text-[12px] font-bold text-slate-700 truncate">{{ __('create_support_ticket_action') }}</span>
                                </button>

                                <a href="{{ route('dashboard') }}?section=requests" wire:navigate class="w-full flex items-center gap-2.5 p-2 rounded-xl hover:bg-slate-50 border border-slate-100 text-start transition-all group">
                                    <div class="w-8 h-8 rounded-lg bg-[#0A2540]/5 group-hover:bg-[#0A2540] text-[#0A2540] group-hover:text-white flex items-center justify-center transition-all shrink-0">
                                        <i class="fas fa-clipboard-list text-[11px]"></i>
                                    </div>
                                    <span class="text-[12px] font-bold text-slate-700 truncate">{{ __('request_service_action') ?: 'طلب خدمة' }}</span>
                                </a>

                                <a href="{{ route('financial-statements.create') }}" class="w-full flex items-center gap-2.5 p-2 rounded-xl hover:bg-slate-50 border border-slate-100 text-start transition-all group">
                                    <div class="w-8 h-8 rounded-lg bg-[#1FA7A2]/10 group-hover:bg-[#1FA7A2] text-[#1FA7A2] group-hover:text-white flex items-center justify-center transition-all shrink-0">
                                        <i class="fas fa-file-invoice-dollar text-[11px]"></i>
                                    </div>
                                    <span class="text-[12px] font-bold text-slate-700 truncate">{{ __('request_financial_statement_action') }}</span>
                                </a>

                                <a href="{{ route('company.select') }}" class="w-full flex items-center gap-2.5 p-2 rounded-xl hover:bg-slate-50 border border-slate-100 text-start transition-all group">
                                    <div class="w-8 h-8 rounded-lg bg-slate-100 group-hover:bg-slate-700 text-slate-600 group-hover:text-white flex items-center justify-center transition-all shrink-0">
                                        <i class="fas fa-right-left text-[11px]"></i>
                                    </div>
                                    <span class="text-[12px] font-bold text-slate-700 truncate">{{ __('switch_company_action') }}</span>
                                </a>

                                @if($isBackoffice)
                                    <a href="{{ route('dashboard') }}?section=ai-review" wire:navigate class="w-full flex items-center gap-2.5 p-2 rounded-xl hover:bg-slate-50 border border-slate-100 text-start transition-all group">
                                        <div class="w-8 h-8 rounded-lg bg-[#0A2540]/5 group-hover:bg-[#0A2540] text-[#0A2540] group-hover:text-white flex items-center justify-center transition-all shrink-0">
                                            <i class="fas fa-robot text-[11px]"></i>
                                        </div>
                                        <span class="text-[12px] font-bold text-slate-700 truncate flex-1">{{ __('nav_ai_review') ?: 'مراجعة الذكاء الاصطناعي' }}</span>
                                        @if($this->pendingAiReviewsCount > 0)
                                            <span class="text-[10px] font-black px-1.5 py-0.5 rounded-md bg-amber-100 text-amber-700 shrink-0">{{ $this->pendingAiReviewsCount }}</span>
                                        @endif
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Stats compact — 5 أعمدة، أصغر مما كانت --}}
                    <div>
                        <h3 class="text-[12px] font-black text-slate-500 uppercase tracking-widest mb-2 flex items-center gap-1.5">
                            <i class="fas fa-chart-simple text-[#1FA7A2] text-[11px]"></i>
                            {{ __('overview') }}
                        </h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-2.5">
                            <a href="{{ route('dashboard') }}?section=compliance" wire:navigate class="bg-white rounded-xl p-3 border border-slate-100 hover:border-rose-200 hover:shadow-sm transition-all text-start group">
                                <div class="flex items-center justify-between mb-1.5">
                                    <i class="fas fa-circle-exclamation text-rose-500 text-[13px]"></i>
                                    <i class="fas fa-arrow-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }} text-slate-300 group-hover:text-rose-500 text-[10px] transition-colors"></i>
                                </div>
                                <div class="text-xl font-black text-rose-500 leading-none">{{ $this->expiredDocumentsCount }}</div>
                                <div class="text-[10px] font-bold text-slate-500 mt-1 truncate">{{ __('expired_documents_count') }}</div>
                            </a>

                            <a href="{{ route('dashboard') }}?section=compliance" wire:navigate class="bg-white rounded-xl p-3 border border-slate-100 hover:border-amber-200 hover:shadow-sm transition-all text-start group">
                                <div class="flex items-center justify-between mb-1.5">
                                    <i class="fas fa-clock text-amber-500 text-[13px]"></i>
                                    <i class="fas fa-arrow-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }} text-slate-300 group-hover:text-amber-500 text-[10px] transition-colors"></i>
                                </div>
                                <div class="text-xl font-black text-amber-500 leading-none">{{ $this->expiringDocumentsCount }}</div>
                                <div class="text-[10px] font-bold text-slate-500 mt-1 truncate">{{ __('expiring_documents_count') }}</div>
                            </a>

                            <a href="{{ route('dashboard') }}?section=tickets" wire:navigate class="bg-white rounded-xl p-3 border border-slate-100 hover:border-[#1FA7A2]/30 hover:shadow-sm transition-all text-start group">
                                <div class="flex items-center justify-between mb-1.5">
                                    <i class="fas fa-headset text-[#1FA7A2] text-[13px]"></i>
                                    <i class="fas fa-arrow-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }} text-slate-300 group-hover:text-[#1FA7A2] text-[10px] transition-colors"></i>
                                </div>
                                <div class="text-xl font-black text-[#0A2540] leading-none">{{ $this->openTicketsCount }}</div>
                                <div class="text-[10px] font-bold text-slate-500 mt-1 truncate">{{ __('open_tickets_count') }}</div>
                            </a>

                            <a href="{{ route('dashboard') }}?section=financial" wire:navigate class="bg-white rounded-xl p-3 border border-slate-100 hover:border-[#1FA7A2]/30 hover:shadow-sm transition-all text-start group">
                                <div class="flex items-center justify-between mb-1.5">
                                    <i class="fas fa-file-invoice-dollar text-[#1FA7A2] text-[13px]"></i>
                                    <i class="fas fa-arrow-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }} text-slate-300 group-hover:text-[#1FA7A2] text-[10px] transition-colors"></i>
                                </div>
                                <div class="text-xl font-black text-[#0A2540] leading-none">{{ $homeStats['fs_requests'] ?? 0 }}</div>
                                <div class="text-[10px] font-bold text-slate-500 mt-1 truncate">{{ __('financial_requests_count') }}</div>
                            </a>

                            <a href="{{ route('dashboard') }}?section=users" wire:navigate class="bg-white rounded-xl p-3 border border-slate-100 hover:border-[#0A2540]/30 hover:shadow-sm transition-all text-start group">
                                <div class="flex items-center justify-between mb-1.5">
                                    <i class="fas fa-users text-[#0A2540] text-[13px]"></i>
                                    <i class="fas fa-arrow-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }} text-slate-300 group-hover:text-[#0A2540] text-[10px] transition-colors"></i>
                                </div>
                                <div class="text-xl font-black text-[#0A2540] leading-none">{{ $homeStats['users'] ?? 0 }}</div>
                                <div class="text-[10px] font-bold text-slate-500 mt-1 truncate">{{ __('go_to_users') }}</div>
                            </a>
                        </div>
                    </div>

                    {{-- Document Expiry Alerts (top 5 docs expiring within 30 days or already expired) --}}
                    @php $expiryAlerts = $this->documentExpiryAlerts; @endphp
                    @if($expiryAlerts->isNotEmpty())
                        <div class="bg-white rounded-2xl border border-amber-100 shadow-sm p-4 mb-3">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-sm font-black text-[#0A2540] inline-flex items-center gap-2">
                                    <i class="fas fa-bell text-amber-500 text-[12px]"></i>
                                    {{ __('home_expiry_alerts_title') ?: 'تنبيهات الوثائق' }}
                                </h3>
                                <a href="{{ route('dashboard') }}?section=compliance" wire:navigate class="text-[11px] font-bold text-[#1FA7A2] hover:underline">
                                    {{ __('view_all') ?: 'الكل' }}
                                </a>
                            </div>
                            <ul class="divide-y divide-slate-100">
                                @foreach($expiryAlerts as $doc)
                                    @php
                                        $statusKey   = $this->documentStatusKey($doc);
                                        $statusLabel = $this->translateStatus($statusKey);
                                        $statusClass = $this->documentStatusBadgeClass($doc);
                                        $expiryDate  = $doc->expiry_date instanceof \Carbon\CarbonInterface
                                            ? $doc->expiry_date
                                            : \Carbon\Carbon::parse($doc->expiry_date);
                                    @endphp
                                    <li class="flex items-center justify-between gap-3 py-2.5">
                                        <div class="min-w-0 flex-1">
                                            <div class="text-sm font-black text-slate-800 truncate">{{ $this->translateStatus($doc->type) }}</div>
                                            <div class="text-[11px] text-slate-500 font-mono">{{ $expiryDate->translatedFormat('d F Y') }}</div>
                                        </div>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-black {{ $statusClass }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Phase 6: Recent Activity placeholder — 3 آخر وثائق + 3 آخر تذاكر + 3 آخر طلبات قوائم --}}
                    @php
                        $recentDocs    = $this->complianceDocuments?->take(3) ?? collect();
                        $recentTickets = $this->tickets?->take(3) ?? collect();
                        $recentFs      = $this->financialStatementRequests?->take(3) ?? collect();
                        $hasAnyActivity = $recentDocs->isNotEmpty() || $recentTickets->isNotEmpty() || $recentFs->isNotEmpty();
                    @endphp

                    <div>
                        <h3 class="text-[12px] font-black text-slate-500 uppercase tracking-widest mb-2 flex items-center gap-1.5">
                            <i class="fas fa-clock-rotate-left text-[#1FA7A2] text-[11px]"></i>
                            {{ __('recent_activity') ?: 'النشاط الأخير' }}
                        </h3>

                        @if(! $hasAnyActivity)
                            <div class="bg-white rounded-2xl border border-dashed border-slate-200 p-6 text-center">
                                <div class="w-10 h-10 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400 mx-auto mb-2">
                                    <i class="fas fa-stream"></i>
                                </div>
                                <p class="text-sm font-black text-slate-700 mb-0.5">{{ __('recent_activity_empty_title') ?: 'لا يوجد نشاط بعد' }}</p>
                                <p class="text-[11px] text-slate-500 font-medium">{{ __('recent_activity_empty_hint') ?: 'ستظهر هنا آخر الوثائق والتذاكر والطلبات فور إضافتها.' }}</p>
                            </div>
                        @else
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                {{-- Documents column --}}
                                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
                                    <div class="flex items-center justify-between mb-2.5">
                                        <h4 class="text-[12px] font-black text-[#0A2540] flex items-center gap-1.5">
                                            <i class="fas fa-folder-open text-[#1FA7A2] text-[11px]"></i>
                                            {{ __('recent_documents') ?: 'آخر الوثائق' }}
                                        </h4>
                                        @if($recentDocs->isNotEmpty())
                                            <a href="{{ route('dashboard') }}?section=compliance" wire:navigate class="text-[10px] font-bold text-[#0A2540] hover:underline">{{ __('view_all') ?: 'الكل' }}</a>
                                        @endif
                                    </div>
                                    @if($recentDocs->isEmpty())
                                        <p class="text-[11px] text-slate-400 font-medium text-center py-3">{{ __('no_recent_documents') ?: 'لا توجد وثائق' }}</p>
                                    @else
                                        <ul class="space-y-1.5">
                                            @foreach($recentDocs as $doc)
                                                <li class="flex items-center justify-between gap-2 py-1.5 border-b border-slate-50 last:border-0">
                                                    <span class="text-[12px] font-bold text-slate-700 truncate min-w-0">
                                                        {{ $this->translateDocumentType($doc->type) }}
                                                    </span>
                                                    <span class="text-[10px] text-slate-400 font-mono shrink-0">{{ optional($doc->created_at)->diffForHumans() }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>

                                {{-- Tickets column --}}
                                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
                                    <div class="flex items-center justify-between mb-2.5">
                                        <h4 class="text-[12px] font-black text-[#0A2540] flex items-center gap-1.5">
                                            <i class="fas fa-headset text-[#1FA7A2] text-[11px]"></i>
                                            {{ __('recent_tickets') ?: 'آخر التذاكر' }}
                                        </h4>
                                        @if($recentTickets->isNotEmpty())
                                            <a href="{{ route('dashboard') }}?section=tickets" wire:navigate class="text-[10px] font-bold text-[#0A2540] hover:underline">{{ __('view_all') ?: 'الكل' }}</a>
                                        @endif
                                    </div>
                                    @if($recentTickets->isEmpty())
                                        <p class="text-[11px] text-slate-400 font-medium text-center py-3">{{ __('no_recent_tickets') ?: 'لا توجد تذاكر' }}</p>
                                    @else
                                        <ul class="space-y-1.5">
                                            @foreach($recentTickets as $t)
                                                <li class="flex items-center justify-between gap-2 py-1.5 border-b border-slate-50 last:border-0">
                                                    <span class="text-[12px] font-bold text-slate-700 truncate min-w-0">
                                                        #{{ $t->id }} · {{ Str::limit($t->subject ?? '—', 22) }}
                                                    </span>
                                                    <span class="text-[10px] text-slate-400 font-mono shrink-0">{{ optional($t->created_at)->diffForHumans() }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>

                                {{-- Financial column --}}
                                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
                                    <div class="flex items-center justify-between mb-2.5">
                                        <h4 class="text-[12px] font-black text-[#0A2540] flex items-center gap-1.5">
                                            <i class="fas fa-file-invoice-dollar text-[#1FA7A2] text-[11px]"></i>
                                            {{ __('recent_financial_requests') ?: 'آخر طلبات القوائم' }}
                                        </h4>
                                        @if($recentFs->isNotEmpty())
                                            <a href="{{ route('dashboard') }}?section=financial" wire:navigate class="text-[10px] font-bold text-[#0A2540] hover:underline">{{ __('view_all') ?: 'الكل' }}</a>
                                        @endif
                                    </div>
                                    @if($recentFs->isEmpty())
                                        <p class="text-[11px] text-slate-400 font-medium text-center py-3">{{ __('no_recent_financial_requests') ?: 'لا توجد طلبات' }}</p>
                                    @else
                                        <ul class="space-y-1.5">
                                            @foreach($recentFs as $req)
                                                <li class="flex items-center justify-between gap-2 py-1.5 border-b border-slate-50 last:border-0">
                                                    <span class="text-[12px] font-bold text-slate-700 truncate min-w-0">
                                                        #{{ $req->id }} · {{ $req->fiscal_year ?? '—' }}
                                                    </span>
                                                    <span class="text-[10px] text-slate-400 font-mono shrink-0">{{ optional($req->created_at)->diffForHumans() }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>

                </div>
            @endif

            {{-- 1. Profile Section — Phase B: 4 أقسام + بطاقة اكتمال --}}
            @if($section === 'profile')
                @php
                    $c = $this->activeCompany;
                    $completion = $c ? (int) $c->profile_completion_percent : 0;
                    $missing = $c ? $c->profileMissingFields() : [];
                    $isBackofficeViewer = auth()->user() && method_exists(auth()->user(), 'hasBackofficeAccess') && auth()->user()->hasBackofficeAccess();

                    // Polish: helper inline لتنسيق التاريخ بالعربي مع fallback
                    $formatArDate = function ($date) {
                        if (! $date) return null;
                        try {
                            return ($date instanceof \Carbon\CarbonInterface ? $date : \Carbon\Carbon::parse($date))
                                ->translatedFormat('d F Y');
                        } catch (\Throwable $e) {
                            return null;
                        }
                    };
                @endphp
                <div class="p-6 animate__animated animate__fadeIn space-y-5">

                    {{-- Polish: header slim بدلاً من كرت ضخم --}}
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-black text-[#0A2540]">{{ __('company_profile') }}</h2>
                            <p class="text-xs text-slate-500 font-medium mt-0.5">{{ __('company_profile_hint') }}</p>
                        </div>
                        @if($this->isCompanyAdmin)
                            <button x-data x-on:click="$dispatch('open-modal', 'edit-company')" class="px-4 py-2.5 bg-[#0A2540] text-white font-bold rounded-xl text-sm hover:bg-[#0a2540]/90 transition-all flex items-center justify-center gap-2">
                                <i class="fas fa-pen text-xs"></i> {{ __('edit_company_profile') }}
                            </button>
                        @endif
                    </div>

                    {{-- Polish: بطاقة اكتمال — chips بألوان amber حسب البرف --}}
                    @if($completion >= 100)
                        <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-4 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0">
                                <i class="fas fa-circle-check"></i>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-sm font-black text-emerald-900">{{ __('profile_ready_title') }}</h3>
                                <p class="text-[11px] text-emerald-700 font-medium">{{ __('profile_ready_hint') }}</p>
                            </div>
                        </div>
                    @else
                        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                            <div class="flex items-center justify-between gap-4 mb-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-xl bg-[#0A2540]/5 flex items-center justify-center text-[#0A2540] shrink-0">
                                        <i class="fas fa-circle-check text-lg"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="text-sm font-black text-[#0A2540]">{{ __('profile_completion_title') }}</h3>
                                        <p class="text-[11px] text-slate-500 font-medium">{{ __('profile_completion_hint') }}</p>
                                    </div>
                                </div>
                                <div class="text-end shrink-0">
                                    <div class="text-2xl font-black text-[#0A2540] leading-none">{{ $completion }}<span class="text-sm text-slate-400">%</span></div>
                                </div>
                            </div>
                            <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-l from-[#1FA7A2] to-[#1FA7A2] transition-all duration-700" style="width: {{ $completion }}%"></div>
                            </div>
                            @if(! empty($missing))
                                <div class="mt-3">
                                    <p class="text-[11px] font-bold text-slate-500 mb-2">{{ __('missing_information') }}:</p>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach(array_slice($missing, 0, 6) as $m)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-amber-50 border border-amber-100 text-[11px] font-bold text-amber-700">
                                                <i class="fas fa-circle-exclamation text-[9px]"></i>
                                                {{ $m['label'] }}
                                            </span>
                                        @endforeach
                                        @if(count($missing) > 6)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-100 text-[11px] font-bold text-slate-500">
                                                +{{ count($missing) - 6 }} {{ __('more') ?: 'أخرى' }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Polish: 4 كروت أقسام كل قسم بـ bg-white + border + shadow-sm --}}

                    {{-- البيانات الأساسية --}}
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                        <div class="flex items-center gap-3 mb-4 pb-3 border-b border-slate-100">
                            <div class="w-9 h-9 rounded-xl bg-[#1FA7A2]/10 flex items-center justify-center text-[#1FA7A2] shrink-0">
                                <i class="fas fa-id-card text-sm"></i>
                            </div>
                            <h3 class="text-sm font-black text-[#0A2540]">{{ __('basic_information') }}</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach([
                                ['label' => __('label_company_name'),       'value' => $c?->name,              'mono' => false],
                                ['label' => __('commercial_name') ?: 'الاسم التجاري', 'value' => $c?->commercial_name, 'mono' => false],
                                ['label' => __('label_unified_number'),     'value' => $c?->unified_number,    'mono' => true],
                                ['label' => __('label_tax_number'),         'value' => $c?->tax_number,        'mono' => true],
                                ['label' => __('cr_number') ?: 'رقم السجل التجاري', 'value' => $c?->cr_number, 'mono' => true],
                                ['label' => __('label_city'),               'value' => $c?->city,              'mono' => false],
                                ['label' => __('label_national_address'),   'value' => $c?->address,           'mono' => false],
                                ['label' => __('cr_issue_date') ?: 'تاريخ إصدار السجل',  'value' => $formatArDate($c?->cr_issue_date), 'mono' => false],
                                ['label' => __('cr_expiry_date') ?: 'تاريخ انتهاء السجل', 'value' => $formatArDate($c?->cr_expiry_date), 'mono' => false],
                                ['label' => __('status') ?: 'حالة المنشأة',  'value' => $c?->status ? $this->translateStatus($c->status) : null, 'mono' => false],
                            ] as $f)
                                <div class="space-y-1">
                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $f['label'] }}</div>
                                    @if(filled($f['value']))
                                        <div class="text-sm font-bold text-slate-800 {{ $f['mono'] ? 'font-mono' : '' }} truncate" title="{{ $f['value'] }}">{{ $f['value'] }}</div>
                                    @else
                                        <span class="text-sm font-medium text-slate-300" aria-label="{{ __('not_specified') }}">—</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- البيانات التشغيلية --}}
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                        <div class="flex items-center gap-3 mb-4 pb-3 border-b border-slate-100">
                            <div class="w-9 h-9 rounded-xl bg-[#1FA7A2]/10 flex items-center justify-center text-[#1FA7A2] shrink-0">
                                <i class="fas fa-briefcase text-sm"></i>
                            </div>
                            <h3 class="text-sm font-black text-[#0A2540]">{{ __('operational_information') }}</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach([
                                ['label' => __('field_activity'),          'value' => $c?->activity,         'mono' => false],
                                ['label' => __('entity_size') ?: 'حجم المنشأة', 'value' => $c?->entity_size, 'mono' => false],
                                ['label' => __('employees_count') ?: 'عدد الموظفين', 'value' => $c?->employees_count, 'mono' => false],
                                ['label' => __('nitaq_color') ?: 'نطاق المنشأة', 'value' => $c?->nitaq_color, 'mono' => false],
                                ['label' => __('field_entity_status'),     'value' => $c?->entity_status,    'mono' => false],
                            ] as $f)
                                <div class="space-y-1">
                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $f['label'] }}</div>
                                    @if(filled($f['value']))
                                        <div class="text-sm font-bold text-slate-800 {{ $f['mono'] ? 'font-mono' : '' }} truncate" title="{{ $f['value'] }}">{{ $f['value'] }}</div>
                                    @else
                                        <span class="text-sm font-medium text-slate-300" aria-label="{{ __('not_specified') }}">—</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        @if($isBackofficeViewer)
                            <div class="mt-4 pt-4 border-t border-slate-100">
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 flex items-center gap-1.5">
                                    <i class="fas fa-lock text-[9px] text-slate-400"></i> {{ __('field_internal_notes') }} <span class="text-slate-300 font-medium">·</span> <span class="text-slate-400 font-medium normal-case">{{ __('backoffice_only') ?: 'يظهر للموظفين فقط' }}</span>
                                </div>
                                @if(filled($c?->internal_notes))
                                    <p class="text-sm text-slate-700 font-medium leading-relaxed bg-slate-50 rounded-xl px-3 py-2 border border-slate-100">{{ $c->internal_notes }}</p>
                                @else
                                    <span class="text-sm font-medium text-slate-300" aria-label="{{ __('not_specified') }}">—</span>
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- التأمينات الاجتماعية (GOSI) --}}
                    @php
                        $hasGosi = $c && (filled($c->gosi_subscription_number) || ($c->gosi_link_status && $c->gosi_link_status !== 'none') || $c->gosi_last_verified_at);
                    @endphp
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                        <div class="flex items-center gap-3 mb-4 pb-3 border-b border-slate-100">
                            <div class="w-9 h-9 rounded-xl bg-[#1FA7A2]/10 flex items-center justify-center text-[#1FA7A2] shrink-0">
                                <i class="fas fa-shield-halved text-sm"></i>
                            </div>
                            <h3 class="text-sm font-black text-[#0A2540]">{{ __('gosi_information') }}</h3>
                        </div>
                        @if($hasGosi)
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                <div class="space-y-1">
                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('field_gosi_subscription_number') }}</div>
                                    @if(filled($c->gosi_subscription_number))
                                        <div class="text-sm font-mono font-bold text-slate-800 truncate" title="{{ $c->gosi_subscription_number }}">{{ $c->gosi_subscription_number }}</div>
                                    @else
                                        <span class="text-sm font-medium text-slate-300" aria-label="{{ __('not_specified') }}">—</span>
                                    @endif
                                </div>
                                <div class="space-y-1">
                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('field_gosi_link_status') }}</div>
                                    <div class="text-sm font-bold text-slate-800">{{ __('gosi_link_status_' . ($c->gosi_link_status ?: 'none')) }}</div>
                                </div>
                                <div class="space-y-1">
                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('field_gosi_last_verified_at') }}</div>
                                    @if($c->gosi_last_verified_at)
                                        <div class="text-sm font-bold text-slate-800">{{ $c->gosi_last_verified_at->diffForHumans() }}</div>
                                    @else
                                        <span class="text-sm font-medium text-slate-300" aria-label="{{ __('not_specified') }}">—</span>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="text-center py-6">
                                <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400 mx-auto mb-2">
                                    <i class="fas fa-shield-halved"></i>
                                </div>
                                <p class="text-xs font-bold text-slate-600">{{ __('no_gosi_information') }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- التأمين الطبي --}}
                    @php
                        $hasMedical = $c && (filled($c->medical_insurance_company) || filled($c->medical_insurance_policy_number) || ($c->medical_insurance_status && $c->medical_insurance_status !== 'none') || $c->medical_insurance_start_date || $c->medical_insurance_end_date);
                    @endphp
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                        <div class="flex items-center gap-3 mb-4 pb-3 border-b border-slate-100">
                            <div class="w-9 h-9 rounded-xl bg-[#1FA7A2]/10 flex items-center justify-center text-[#1FA7A2] shrink-0">
                                <i class="fas fa-notes-medical text-sm"></i>
                            </div>
                            <h3 class="text-sm font-black text-[#0A2540]">{{ __('medical_insurance_information') }}</h3>
                        </div>
                        @if($hasMedical)
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                <div class="space-y-1">
                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('field_medical_insurance_status') }}</div>
                                    <div class="text-sm font-bold text-slate-800">{{ __('medical_insurance_status_' . ($c->medical_insurance_status ?: 'none')) }}</div>
                                </div>
                                <div class="space-y-1">
                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('field_medical_insurance_company') }}</div>
                                    @if(filled($c->medical_insurance_company))
                                        <div class="text-sm font-bold text-slate-800 truncate" title="{{ $c->medical_insurance_company }}">{{ $c->medical_insurance_company }}</div>
                                    @else
                                        <span class="text-sm font-medium text-slate-300" aria-label="{{ __('not_specified') }}">—</span>
                                    @endif
                                </div>
                                <div class="space-y-1">
                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('field_medical_insurance_policy_number') }}</div>
                                    @if(filled($c->medical_insurance_policy_number))
                                        <div class="text-sm font-mono font-bold text-slate-800 truncate" title="{{ $c->medical_insurance_policy_number }}">{{ $c->medical_insurance_policy_number }}</div>
                                    @else
                                        <span class="text-sm font-medium text-slate-300" aria-label="{{ __('not_specified') }}">—</span>
                                    @endif
                                </div>
                                <div class="space-y-1">
                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('field_medical_insurance_start_date') }}</div>
                                    @if($c->medical_insurance_start_date)
                                        <div class="text-sm font-bold text-slate-800">{{ $formatArDate($c->medical_insurance_start_date) }}</div>
                                    @else
                                        <span class="text-sm font-medium text-slate-300" aria-label="{{ __('not_specified') }}">—</span>
                                    @endif
                                </div>
                                <div class="space-y-1">
                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('field_medical_insurance_end_date') }}</div>
                                    @if($c->medical_insurance_end_date)
                                        <div class="text-sm font-bold text-slate-800">{{ $formatArDate($c->medical_insurance_end_date) }}</div>
                                    @else
                                        <span class="text-sm font-medium text-slate-300" aria-label="{{ __('not_specified') }}">—</span>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="text-center py-6">
                                <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400 mx-auto mb-2">
                                    <i class="fas fa-notes-medical"></i>
                                </div>
                                <p class="text-xs font-bold text-slate-600">{{ __('no_medical_insurance_information') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- 2. Users Section --}}
            @if($section === 'users')
                <div class="p-8 animate__animated animate__fadeIn space-y-6">
                    <div class="flex justify-between items-center">
                        <h2 class="text-2xl font-black text-slate-800">{{ __('users_management_title') }}</h2>
                        @if($this->isCompanyAdmin)
                            {{-- Phase A: primary CTA now opens the invite-by-email modal.
                                 The legacy "Add User" flow is still available via
                                 Dashboard::addUserToCompany() but no longer surfaced. --}}
                            <button x-data @click="$dispatch('open-modal', 'invite-member')"
                                    class="px-5 py-2.5 bg-[#1FA7A2] text-white font-bold rounded-xl shadow-lg shadow-[#1FA7A2]/20 hover:bg-[#167F7B] hover:-translate-y-0.5 transition-all flex items-center gap-2">
                                <i class="fas fa-paper-plane"></i>
                                {{ __('invite_member_action') === 'invite_member_action' ? 'دعوة موظف' : __('invite_member_action') }}
                            </button>
                        @endif
                    </div>

                    {{-- Phase A: freshly-minted invitation link surfaced once.
                         Admin copies it then dismisses the banner. Plain token
                         is never persisted, only its SHA-256 digest. --}}
                    @if($this->isCompanyAdmin && $lastInvitationLink)
                        <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-4 flex flex-col md:flex-row md:items-center gap-3"
                             x-data="{ copied: false }">
                            <div class="flex items-start md:items-center gap-3 min-w-0 flex-1">
                                <span class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0">
                                    <i class="fas fa-link"></i>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <h4 class="text-sm font-black text-emerald-900">
                                        {{ __('invitation_link_ready') === 'invitation_link_ready' ? 'رابط الدعوة جاهز' : __('invitation_link_ready') }}
                                    </h4>
                                    <p class="text-[11px] text-emerald-700 font-medium mb-1.5">
                                        {{ __('invitation_link_hint') === 'invitation_link_hint' ? 'أرسله للموظف بأي وسيلة آمنة. الرابط صالح لمدة 7 أيام ولن يظهر مجددًا.' : __('invitation_link_hint') }}
                                    </p>
                                    <code class="block w-full text-[11px] font-mono text-emerald-900 bg-white border border-emerald-200 rounded-lg px-2 py-1.5 break-all" dir="ltr">{{ $lastInvitationLink }}</code>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <button type="button"
                                        x-on:click="navigator.clipboard.writeText(@js($lastInvitationLink)).then(() => { copied = true; setTimeout(() => copied = false, 1500); })"
                                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-emerald-600 text-white text-[12px] font-black hover:bg-emerald-700">
                                    <i class="fas fa-copy text-[10px]"></i>
                                    <span x-text="copied ? '{{ __('copied') === 'copied' ? 'تم النسخ ✓' : __('copied') }}' : '{{ __('copy_link') === 'copy_link' ? 'نسخ الرابط' : __('copy_link') }}'"></span>
                                </button>
                                <button type="button" wire:click="dismissInvitationLink"
                                        class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-emerald-100 text-emerald-700 hover:bg-emerald-100"
                                        title="{{ __('dismiss') === 'dismiss' ? 'إخفاء' : __('dismiss') }}">
                                    <i class="fas fa-times text-[12px]"></i>
                                </button>
                            </div>
                        </div>
                    @endif

                    @unless($this->isCompanyAdmin)
                        <div class="mb-6 px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center gap-3 text-xs font-bold text-slate-600">
                            <i class="fas fa-info-circle text-slate-400"></i>
                            يمكنك عرض أعضاء فريق الشركة. إدارة الأعضاء (إضافة أو إزالة) متاحة لمسؤولي الشركة فقط.
                        </div>
                    @endunless

                    <div class="overflow-hidden rounded-2xl border border-slate-100 shadow-sm">
                        <table class="w-full text-sm text-start">
                            <thead class="bg-slate-50 text-slate-500 font-black text-xs uppercase tracking-widest border-b border-slate-100">
                                <tr>
                                    <th class="p-4 text-start">{{ __('table_name') }}</th>
                                    <th class="p-4 text-start">{{ __('table_contact') }}</th>
                                    <th class="p-4 text-start">{{ __('table_role') }}</th>
                                    <th class="p-4 text-start">{{ __('table_status') }}</th>
                                    <th class="p-4 text-center">{{ __('table_actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @forelse($this->activeCompany->users ?? [] as $user)
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="p-4">
                                            <div class="font-bold text-slate-800">{{ $user->name }}</div>
                                        </td>
                                        <td class="p-4">
                                            <div class="font-medium text-slate-600" dir="ltr">{{ $user->email }}</div>
                                            <div class="font-mono text-xs text-slate-400 mt-1" dir="ltr">{{ $user->mobile }}</div>
                                        </td>
                                        <td class="p-4">
                                            <span class="bg-[#1FA7A2]/10 text-[#1FA7A2] px-3 py-1 rounded-lg text-xs font-bold">
                                                {{ $this->translateRole($user->pivot->role ?? '') }}
                                            </span>
                                        </td>
                                        <td class="p-4">
                                            @if((bool) ($user->pivot->is_active ?? true))
                                                <span class="bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100 px-3 py-1 rounded-lg text-xs font-bold">{{ __('active') ?: 'نشط' }}</span>
                                            @else
                                                <span class="bg-slate-50 text-slate-500 ring-1 ring-slate-100 px-3 py-1 rounded-lg text-xs font-bold">{{ __('inactive') ?: 'غير نشط' }}</span>
                                            @endif
                                        </td>
                                        <td class="p-4 text-center">
                                            @php
                                                $rowRole = strtolower((string) ($user->pivot->role ?? ''));
                                                $rowIsAdmin = in_array($rowRole, ['admin', 'owner'], true);
                                                $rowMatrix = \App\Support\CompanyPermissions::effective($rowRole, $user->pivot->permissions ?? null);
                                                $rowGrantCount = array_sum(array_map('count', $rowMatrix));
                                            @endphp
                                            <div class="inline-flex items-center justify-center gap-1.5">
                                                {{-- Phase B (UI): matrix editor — admin-only, hidden for admin/owner
                                                     rows (those are always full and the modal would mislead). --}}
                                                @if($this->isCompanyAdmin && ! $rowIsAdmin && $user->id !== auth()->id())
                                                    <button wire:click="openPermissionsManager({{ $user->id }})"
                                                            class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-[#0A2540]/5 text-[#0A2540] hover:bg-[#0A2540] hover:text-white text-[11px] font-black"
                                                            title="{{ __('manage_permissions') === 'manage_permissions' ? 'إدارة الصلاحيات' : __('manage_permissions') }}">
                                                        <i class="fas fa-shield-halved text-[10px]"></i>
                                                        <span class="hidden sm:inline">{{ __('manage_permissions') === 'manage_permissions' ? 'الصلاحيات' : __('manage_permissions') }}</span>
                                                        <span class="text-[10px] font-mono opacity-70">({{ $rowGrantCount }})</span>
                                                    </button>
                                                @elseif($rowIsAdmin)
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-100 text-[11px] font-black"
                                                          title="{{ __('full_access_admin') === 'full_access_admin' ? 'صلاحيات كاملة' : __('full_access_admin') }}">
                                                        <i class="fas fa-crown text-[10px]"></i>
                                                        <span class="hidden sm:inline">{{ __('full_access_label') === 'full_access_label' ? 'كامل' : __('full_access_label') }}</span>
                                                    </span>
                                                @endif
                                                @if($this->isCompanyAdmin && $user->id !== auth()->id())
                                                    <button wire:click="removeUser({{ $user->id }})" wire:confirm="{{ __('confirm_delete') }}" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition-colors" title="{{ __('btn_remove') }}">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center">
                                            {{-- Polish: empty state للمستخدمين --}}
                                            <div class="flex flex-col items-center gap-3">
                                                <div class="w-14 h-14 rounded-2xl bg-[#1FA7A2]/5 flex items-center justify-center text-[#1FA7A2]">
                                                    <i class="fas fa-user-plus text-2xl"></i>
                                                </div>
                                                <div>
                                                    <h4 class="text-sm font-black text-slate-700 mb-1">{{ __('no_users_found') }}</h4>
                                                    <p class="text-xs text-slate-500 font-medium">{{ __('no_users_hint') }}</p>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Phase A: pending invitations list. Only company admins
                         see this; employees never get to peek at active tokens
                         or revoke each other. --}}
                    @if($this->isCompanyAdmin)
                        @php $pendingInvitations = $this->pendingCompanyInvitations; @endphp
                        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                            <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between">
                                <h3 class="text-sm font-black text-[#0A2540] inline-flex items-center gap-2">
                                    <i class="fas fa-envelope-open-text text-[#1FA7A2] text-[12px]"></i>
                                    {{ __('pending_invitations_title') === 'pending_invitations_title' ? 'دعوات معلقة' : __('pending_invitations_title') }}
                                </h3>
                                <span class="text-[10px] font-black px-2 py-0.5 rounded-md bg-slate-50 text-slate-600 border border-slate-100">{{ $pendingInvitations->count() }}</span>
                            </div>
                            @if($pendingInvitations->isEmpty())
                                <div class="px-6 py-8 text-center">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-50 text-slate-400 flex items-center justify-center mx-auto mb-2">
                                        <i class="fas fa-envelope-open-text"></i>
                                    </div>
                                    <p class="text-sm font-black text-slate-700 mb-0.5">{{ __('no_pending_invitations_title') === 'no_pending_invitations_title' ? 'لا توجد دعوات معلقة' : __('no_pending_invitations_title') }}</p>
                                    <p class="text-[11px] text-slate-500 font-medium">{{ __('no_pending_invitations_hint') === 'no_pending_invitations_hint' ? 'استخدم زر "دعوة موظف" لإصدار دعوة جديدة وانسخ رابطها للمستخدم.' : __('no_pending_invitations_hint') }}</p>
                                </div>
                            @else
                                <table class="w-full text-sm text-start">
                                    <thead class="bg-slate-50/60 text-slate-500 font-black text-[11px] uppercase tracking-widest border-b border-slate-100">
                                        <tr>
                                            <th class="p-3 text-start">{{ __('label_email') }}</th>
                                            <th class="p-3 text-start">{{ __('table_role') }}</th>
                                            <th class="p-3 text-start hidden md:table-cell">{{ __('invited_by_label') === 'invited_by_label' ? 'الداعي' : __('invited_by_label') }}</th>
                                            <th class="p-3 text-start hidden md:table-cell">{{ __('expires_label') === 'expires_label' ? 'الصلاحية' : __('expires_label') }}</th>
                                            <th class="p-3 text-center">{{ __('table_actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        @foreach($pendingInvitations as $inv)
                                            <tr wire:key="inv-row-{{ $inv->id }}" class="hover:bg-slate-50/40 transition-colors">
                                                <td class="p-3 font-mono text-slate-700" dir="ltr">{{ $inv->email }}</td>
                                                <td class="p-3">
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-black bg-[#1FA7A2]/10 text-[#1FA7A2]">
                                                        {{ $this->translateRole($inv->role) }}
                                                    </span>
                                                </td>
                                                <td class="p-3 hidden md:table-cell text-xs text-slate-500 font-medium">
                                                    {{ optional($inv->inviter)->name ?? '—' }}
                                                </td>
                                                <td class="p-3 hidden md:table-cell text-xs text-slate-500 font-mono">
                                                    {{ $inv->expires_at ? $inv->expires_at->format('Y-m-d') : '—' }}
                                                </td>
                                                <td class="p-3 text-center">
                                                    <div class="inline-flex items-center justify-center gap-1.5">
                                                        <button type="button"
                                                                wire:click="regenerateCompanyInvitation({{ $inv->id }})"
                                                                wire:confirm="{{ __('confirm_regenerate_invitation') === 'confirm_regenerate_invitation' ? 'سيؤدي تجديد الدعوة إلى تعطيل الرابط القديم. هل تريد المتابعة؟' : __('confirm_regenerate_invitation') }}"
                                                                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-[#0A2540]/5 text-[#0A2540] hover:bg-[#0A2540] hover:text-white text-[11px] font-black"
                                                                title="{{ __('regenerate_invitation') === 'regenerate_invitation' ? 'تجديد الرابط' : __('regenerate_invitation') }}">
                                                            <i class="fas fa-rotate text-[10px]"></i>
                                                            <span class="hidden sm:inline">{{ __('regenerate_invitation') === 'regenerate_invitation' ? 'تجديد' : __('regenerate_invitation') }}</span>
                                                        </button>
                                                        <button type="button"
                                                                wire:click="revokeCompanyInvitation({{ $inv->id }})"
                                                                wire:confirm="{{ __('confirm_revoke_invitation') === 'confirm_revoke_invitation' ? 'هل تريد إلغاء هذه الدعوة؟' : __('confirm_revoke_invitation') }}"
                                                                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-500 hover:text-white text-[11px] font-black"
                                                                title="{{ __('revoke_invitation') === 'revoke_invitation' ? 'إلغاء الدعوة' : __('revoke_invitation') }}">
                                                            <i class="fas fa-ban text-[10px]"></i>
                                                            <span class="hidden sm:inline">{{ __('revoke_invitation') === 'revoke_invitation' ? 'إلغاء' : __('revoke_invitation') }}</span>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    @endif
                </div>
            @endif

            {{-- 3. Files Section --}}
            @if($section === 'files')
                <div class="p-6 animate__animated animate__fadeIn space-y-5">
                    {{-- Polish: header مدمج بدلاً من decorative icon ضخم --}}
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-black text-[#0A2540]">{{ __('upload_new_file_title') }}</h2>
                            <p class="text-xs text-slate-500 font-medium mt-0.5">{{ __('files_section_hint') ?: 'ارفع السجلات والوثائق والمرفقات المهمة لمنشأتك.' }}</p>
                        </div>
                    </div>

                    {{-- Polish: dropzone-style upload card بـ x-data آمن للأحداث --}}
                    <div
                        x-data="{ uploading: false, progress: 0, uploadError: '', dragging: false }"
                        x-on:livewire-upload-start.window="uploading = true; progress = 0; uploadError = ''"
                        x-on:livewire-upload-progress.window="progress = $event.detail.progress"
                        x-on:livewire-upload-finish.window="uploading = false; progress = 100"
                        x-on:livewire-upload-cancel.window="uploading = false; progress = 0"
                        x-on:livewire-upload-error.window="uploading = false; progress = 0; uploadError = ($event.detail?.message ?? 'تعذّر رفع الملف. حاول مرة أخرى.')"
                        wire:key="files-upload-card"
                        class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">

                        <div class="grid md:grid-cols-3 gap-4 mb-4">
                            <div class="space-y-1">
                                <label class="block text-xs font-bold text-slate-600">{{ __('file_name') }}</label>
                                <input wire:model="newFileTitle" type="text" placeholder="{{ __('file_name_optional_hint') ?: 'اسم وصفي (اختياري)' }}" class="w-full rounded-xl bg-slate-50 border border-slate-200 focus:border-[#0A2540] focus:ring-2 focus:ring-[#0A2540]/10 text-sm font-medium py-2.5 outline-none transition-all">
                            </div>
                            <div class="space-y-1">
                                <label class="block text-xs font-bold text-slate-600">{{ __('file_category') }}</label>
                                <select wire:model="newFileCategory" class="w-full rounded-xl bg-slate-50 border border-slate-200 focus:border-[#0A2540] focus:ring-2 focus:ring-[#0A2540]/10 text-sm font-medium py-2.5 outline-none transition-all">
                                    <option value="general">{{ __('cat_general') }}</option>
                                    <option value="commercial_register">{{ __('commercial_register') }}</option>
                                    <option value="invoice">{{ __('cat_invoice') }}</option>
                                    <option value="contract">{{ __('cat_contract') }}</option>
                                </select>
                            </div>
                            <div class="space-y-1 flex flex-col justify-end">
                                <button type="button"
                                        wire:click="uploadFile"
                                        wire:loading.attr="disabled"
                                        wire:target="uploadFile"
                                        x-bind:disabled="uploading || !$wire.newFile"
                                        class="w-full bg-[#0A2540] hover:bg-[#0a2540]/90 text-white font-bold py-2.5 px-4 rounded-xl text-sm transition-all flex justify-center items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed h-[42px]">
                                    <span wire:loading.remove wire:target="uploadFile" x-show="!uploading" class="inline-flex items-center gap-1.5">
                                        <i class="fas fa-upload text-xs"></i>
                                        <span>{{ $newFile ? __('send_file') : __('choose_file') }}</span>
                                    </span>
                                    <span wire:loading wire:target="uploadFile" class="inline-flex items-center gap-1.5">
                                        <i class="fas fa-circle-notch fa-spin"></i>
                                        <span>{{ __('sending_file') ?: 'جاري إرسال الملف...' }}</span>
                                    </span>
                                    <span x-show="uploading" x-cloak class="inline-flex items-center gap-1.5">
                                        <i class="fas fa-cloud-arrow-up text-xs"></i>
                                        <span>{{ __('sending_file') ?: 'جاري إرسال الملف...' }}</span>
                                    </span>
                                </button>
                            </div>
                        </div>

                        {{-- Polish: Dropzone-style file input (بدون مكتبة) --}}
                        <label
                            x-on:dragover.prevent="if(!uploading) dragging = true"
                            x-on:dragleave.prevent="dragging = false"
                            x-on:drop.prevent="dragging = false"
                            x-bind:class="dragging ? 'border-[#0A2540] bg-[#0A2540]/5' : 'border-slate-300 bg-slate-50'"
                            class="block border-2 border-dashed rounded-2xl px-6 py-8 text-center cursor-pointer hover:bg-slate-50 transition-all">
                            <input wire:model="newFile" type="file" x-bind:disabled="uploading" accept=".pdf,.jpg,.jpeg,.png" class="hidden">
                            <div class="flex flex-col items-center gap-2">
                                <div class="w-12 h-12 rounded-2xl bg-[#0A2540]/5 flex items-center justify-center text-[#0A2540]">
                                    <i class="fas fa-cloud-upload-alt text-xl"></i>
                                </div>
                                @if($newFile)
                                    <div class="w-full max-w-md rounded-2xl bg-white border border-slate-200 shadow-sm p-3 flex items-center gap-3 text-start">
                                        <div class="w-10 h-10 rounded-xl bg-[#1FA7A2]/10 text-[#1FA7A2] flex items-center justify-center shrink-0">
                                            <i class="fas fa-file"></i>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-black text-slate-700 truncate">{{ $newFile->getClientOriginalName() }}</p>
                                            <p class="text-[11px] text-slate-400 font-medium">
                                                {{ $this->fileSizeHuman($newFile->getSize()) }} · {{ $this->fileMimeLabel($newFile->getMimeType()) }}
                                            </p>
                                        </div>
                                        <button type="button"
                                                wire:click="clearNewFile"
                                                class="w-8 h-8 rounded-lg text-slate-400 hover:bg-rose-50 hover:text-rose-500 transition-colors"
                                                title="{{ __('remove_file') ?: 'إزالة الملف' }}">
                                            <i class="fas fa-xmark text-sm"></i>
                                        </button>
                                    </div>
                                    <p class="text-[11px] text-slate-400 font-medium">{{ __('ready_to_send_file') ?: 'الملف جاهز للإرسال. اضغط إرسال الملف مرة واحدة فقط.' }}</p>
                                @else
                                    <p class="text-sm font-black text-slate-700">{{ __('choose_file') ?: 'اختر ملف' }}</p>
                                    <p class="text-[11px] text-slate-400 font-medium">{{ __('upload_size_hint', ['max' => 20]) }}</p>
                                @endif
                            </div>
                        </label>

                        @error('newFile') <span class="text-[11px] text-rose-500 font-bold block mt-2">{{ $message }}</span> @enderror
                        <span x-show="uploadError" x-text="uploadError" class="text-[11px] text-rose-500 font-bold block mt-2"></span>

                        {{-- Progress bar أثناء temp upload --}}
                        <div x-show="uploading" x-cloak class="mt-3">
                            <div class="flex items-center justify-between text-[11px] font-bold text-slate-600 mb-1">
                                <span><i class="fas fa-circle-notch fa-spin me-1 text-[#1FA7A2]"></i>{{ __('upload_in_progress') }}</span>
                                <span x-text="progress + '%'"></span>
                            </div>
                            <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-[#1FA7A2] transition-all duration-200" x-bind:style="`width: ${progress}%`"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Polish: filter pills للملفات حسب النوع --}}
                    <div class="flex flex-wrap items-center gap-2">
                        @php
                            $fileFilters = [
                                'all'   => __('all_files'),
                                'pdf'   => __('pdf_files'),
                                'image' => __('image_files'),
                                'other' => __('other_files'),
                            ];
                        @endphp
                        @foreach($fileFilters as $key => $label)
                            <button type="button"
                                    wire:click="$set('fileTypeFilter', '{{ $key }}')"
                                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $fileTypeFilter === $key ? 'bg-[#0A2540] text-white' : 'bg-white text-slate-500 border border-slate-200 hover:border-[#0A2540]/30' }}">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>

                    {{-- Polish: قائمة الملفات بصيغة جدول مدمج --}}
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                        <table class="w-full text-sm text-start">
                            <thead class="bg-slate-50 text-slate-500 font-bold text-[11px] uppercase tracking-widest border-b border-slate-100">
                                <tr>
                                    <th class="px-4 py-3 text-start">{{ __('file_name') }}</th>
                                    <th class="px-4 py-3 text-start hidden md:table-cell">{{ __('file_category') }}</th>
                                    <th class="px-4 py-3 text-start hidden md:table-cell">{{ __('file_type') }}</th>
                                    <th class="px-4 py-3 text-start hidden md:table-cell">{{ __('file_size') }}</th>
                                    <th class="px-4 py-3 text-start hidden lg:table-cell">{{ __('uploaded_at') }}</th>
                                    <th class="px-4 py-3 text-center">{{ __('table_actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @forelse($this->companyFiles as $file)
                                    @php
                                        $mime = strtolower((string) ($file->mime ?? ''));
                                        $icon = str_contains($mime, 'pdf') ? 'fa-file-pdf text-rose-500'
                                              : (str_starts_with($mime, 'image/') ? 'fa-file-image text-emerald-500'
                                              : (str_contains($mime, 'word') ? 'fa-file-word text-blue-500'
                                              : 'fa-file-alt text-slate-400'));
                                    @endphp
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-4 py-3">
                                            <div class="font-bold text-slate-800 flex items-center gap-2.5 min-w-0">
                                                <div class="w-9 h-9 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center shrink-0">
                                                    <i class="fas {{ $icon }}"></i>
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="truncate text-sm">{{ $file->original_name ?? $file->title ?? __('untitled') }}</div>
                                                    @if($file->title && $file->title !== $file->original_name)
                                                        <div class="truncate text-[11px] text-slate-400 font-medium">{{ $file->title }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 hidden md:table-cell">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-[#1FA7A2]/5 text-[#1FA7A2] border border-[#1FA7A2]/10">
                                                {{ $this->translateDocumentType($file->category ?: 'general') }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 hidden md:table-cell">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                                {{ $this->fileMimeLabel($file->mime) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 hidden md:table-cell text-slate-500 text-xs font-mono">
                                            {{ $this->fileSizeHuman($file->size) }}
                                        </td>
                                        <td class="px-4 py-3 hidden lg:table-cell text-slate-500 text-xs font-mono">
                                            {{ optional($file->created_at)->format('Y-m-d') }}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <button wire:click="downloadFile({{ $file->id }})"
                                                        wire:target="downloadFile({{ $file->id }})"
                                                        wire:loading.attr="disabled"
                                                        class="w-8 h-8 rounded-lg text-slate-500 hover:bg-[#0A2540] hover:text-white border border-slate-200 transition-all disabled:opacity-60 disabled:cursor-not-allowed"
                                                        title="{{ __('download') }}">
                                                    <i class="fas fa-download text-xs" wire:loading.remove wire:target="downloadFile({{ $file->id }})"></i>
                                                    <i class="fas fa-circle-notch fa-spin text-xs" wire:loading wire:target="downloadFile({{ $file->id }})"></i>
                                                </button>
                                                <button wire:click="deleteFile({{ $file->id }})"
                                                        wire:confirm="{{ __('confirm_delete') }}"
                                                        wire:target="deleteFile({{ $file->id }})"
                                                        wire:loading.attr="disabled"
                                                        class="w-8 h-8 rounded-lg text-slate-500 hover:bg-rose-500 hover:text-white border border-slate-200 transition-all disabled:opacity-60 disabled:cursor-not-allowed"
                                                        title="{{ __('delete') }}">
                                                    <i class="fas fa-trash-alt text-xs" wire:loading.remove wire:target="deleteFile({{ $file->id }})"></i>
                                                    <i class="fas fa-circle-notch fa-spin text-xs" wire:loading wire:target="deleteFile({{ $file->id }})"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center gap-3">
                                                <div class="w-14 h-14 rounded-2xl bg-[#1FA7A2]/5 flex items-center justify-center text-[#1FA7A2]">
                                                    <i class="fas fa-folder-open text-2xl"></i>
                                                </div>
                                                <div>
                                                    @if($fileTypeFilter !== 'all')
                                                        <h4 class="text-sm font-black text-slate-700 mb-1">{{ __('no_search_results') }}</h4>
                                                        <p class="text-xs text-slate-500 font-medium mb-2">{{ __('try_different_filter') }}</p>
                                                        <button wire:click="$set('fileTypeFilter', 'all')" class="text-xs font-bold text-[#0A2540] hover:underline">{{ __('all_files') }}</button>
                                                    @else
                                                        <h4 class="text-sm font-black text-slate-700 mb-1">{{ __('files_empty_title') ?: 'لا توجد وثائق مرفوعة بعد' }}</h4>
                                                        <p class="text-xs text-slate-500 font-medium">{{ __('files_empty_hint') ?: 'ابدأ برفع السجل التجاري أو مستندات المنشأة لمتابعتها من مكان واحد.' }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- 4. Compliance Section --}}
            @if($section === 'compliance')
                @php
                    $complianceDocs = $this->complianceDocuments;
                    $aiStatuses = $this->extractionStatusesFor($complianceDocs->pluck('id')->all());
                    $aiExtractionEnabled = $this->isAiExtractionEnabled();
                @endphp
                <div class="p-6 animate__animated animate__fadeIn">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-5">
                        <div>
                            <h2 class="text-lg font-black text-[#0A2540]">{{ __('tab_compliance') }}</h2>
                            <p class="text-xs text-slate-500 font-medium mt-0.5">{{ __('compliance_section_hint') ?: 'وثائق المنشأة وحالات صلاحيتها.' }}</p>
                        </div>
                        <button x-data x-on:click="$dispatch('open-modal', 'add-doc')" class="px-4 py-2.5 bg-[#0A2540] text-white text-sm font-bold rounded-xl hover:bg-[#0a2540]/90 transition-all flex items-center justify-center gap-2">
                            <i class="fas fa-plus text-xs"></i> {{ __('add_document') }}
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @forelse($complianceDocs as $doc)
                            @php
                                $aiStatus = $aiStatuses[$doc->id] ?? null;
                                $aiInfo = $aiStatus ? $this->extractionStatusInfo($aiStatus) : null;
                                $supportsAi = $this->isAiExtractionSupported($doc->type);
                                $aiInProgress = in_array($aiStatus, ['pending', 'processing'], true);
                                $aiHasPriorRun = $aiStatus !== null;
                                if (! $aiExtractionEnabled) {
                                    $aiBtnLabel = 'التحليل غير مفعّل حاليًا';
                                    $aiBtnDisabled = true;
                                } elseif ($aiInProgress) {
                                    $aiBtnLabel = 'جاري التحليل…';
                                    $aiBtnDisabled = true;
                                } else {
                                    $aiBtnLabel = $aiHasPriorRun ? 'إعادة التحليل' : 'تحليل بالذكاء الاصطناعي';
                                    $aiBtnDisabled = false;
                                }
                            @endphp
                            {{-- Phase 3: بطاقة وثيقة امتثال أهدأ — حُذف decorative gradient corner، CTAs أقل وزناً --}}
                            <div class="border border-slate-100 rounded-2xl p-5 hover:shadow-md hover:border-[#1FA7A2]/30 transition-all duration-300 bg-white shadow-sm">

                                <div class="flex justify-between items-start mb-3">
                                    <div class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center text-[#1FA7A2]">
                                        <i class="fas fa-certificate"></i>
                                    </div>
                                    <span class="text-[10px] font-black px-2.5 py-1 rounded-full {{ $this->documentStatusBadgeClass($doc) }}">
                                        {{ $this->documentStatusLabel($doc) }}
                                    </span>
                                </div>

                                <h4 class="font-black text-slate-800 text-base mb-1">{{ $this->translateDocumentType($doc->type) }}</h4>
                                <div class="text-[11px] font-bold text-slate-400 mb-3 flex items-center gap-1.5">
                                    <i class="far fa-clock text-slate-300"></i>
                                    <span>{{ $this->documentExpiryHuman($doc) }}</span>
                                </div>

                                {{-- AI Status badge inline — Phase 3: لا زر "تحليل" أساسي، فقط شارة حالة قراءة --}}
                                @if($supportsAi && $aiInfo && $aiInfo['label'])
                                    <div class="mb-3">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-bold {{ $aiInfo['class'] }}">
                                            <i class="{{ $aiInfo['icon'] }} text-[10px]"></i>
                                            <span>{{ __('ai_status_label') ?: 'تحليل الذكاء الاصطناعي' }}: {{ $aiInfo['label'] }}</span>
                                        </span>
                                    </div>
                                @endif

                                {{-- Phase 3: أزرار CTAs أقل وزناً — "عرض" primary خفيف، "حذف" outline-only --}}
                                <div class="flex gap-1.5">
                                    @if($doc->file_path)
                                        <a href="{{ route('company.docs.download', $doc) }}" target="_blank" class="flex-1 py-2 rounded-xl bg-[#0A2540]/5 text-xs font-bold text-[#0A2540] hover:bg-[#0A2540]/10 border border-[#0A2540]/10 text-center transition-all inline-flex items-center justify-center gap-1.5">
                                            <i class="fas fa-eye text-[10px]"></i>
                                            <span>{{ __('view') }}</span>
                                        </a>
                                    @endif
                                    <button wire:click="deleteDocument({{ $doc->id }})" wire:confirm="{{ __('confirm_delete') }}" class="flex-1 py-2 rounded-xl bg-white text-xs font-bold text-rose-500 hover:bg-rose-50 hover:text-rose-600 transition-all border border-rose-100 inline-flex items-center justify-center gap-1.5">
                                        <i class="fas fa-trash-alt text-[10px]"></i>
                                        <span>{{ __('delete') }}</span>
                                    </button>
                                </div>

                                {{-- زر تحليل الذكاء الاصطناعي — يظهر لكل نوع وثيقة مدعوم.
                                     يتعطّل تلقائيًا إذا AI_EXTRACTION_ENABLED=false أو إذا كان هناك تحليل جارٍ.
                                     ownership/scoping يُفرض داخل requestAiExtraction() في Dashboard.php. --}}
                                @if($supportsAi)
                                    <div class="mt-2 relative z-10">
                                        <button type="button"
                                                @if(! $aiBtnDisabled) wire:click="requestAiExtraction({{ $doc->id }})" @endif
                                                @disabled($aiBtnDisabled)
                                                wire:loading.attr="disabled"
                                                wire:target="requestAiExtraction({{ $doc->id }})"
                                                @if($aiBtnDisabled && ! $aiExtractionEnabled) title="التحليل الذكي غير مفعّل في الإعدادات حاليًا." @endif
                                                class="w-full h-7 rounded-lg bg-transparent text-[10px] font-bold text-slate-500 hover:text-[#0A2540] hover:bg-slate-50 transition-all flex items-center justify-center gap-1.5 disabled:opacity-50 disabled:cursor-not-allowed">
                                            <i class="fas fa-wand-magic-sparkles text-[9px]" wire:loading.remove wire:target="requestAiExtraction({{ $doc->id }})"></i>
                                            <i class="fas fa-circle-notch fa-spin text-[9px]" wire:loading wire:target="requestAiExtraction({{ $doc->id }})"></i>
                                            {{ $aiBtnLabel }}
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @empty
                            {{-- Phase 3: Empty state أهدأ — أيقونة أصغر، نسخة مركّزة، CTA واحد --}}
                            <div class="col-span-full py-12 px-6 text-center bg-white rounded-2xl border border-dashed border-slate-200">
                                <div class="w-14 h-14 rounded-2xl bg-[#1FA7A2]/5 flex items-center justify-center text-[#1FA7A2] mx-auto mb-3">
                                    <i class="fas fa-shield-alt text-xl"></i>
                                </div>
                                <h3 class="text-base font-black text-slate-800 mb-1">{{ __('no_compliance_documents_yet') ?: 'لا توجد وثائق امتثال بعد' }}</h3>
                                <p class="text-xs text-slate-500 font-medium max-w-md mx-auto mb-5">{{ __('compliance_empty_hint') ?: 'ارفع السجل التجاري أو شهادة الزكاة والضريبة أو أي وثيقة مطلوبة لمتابعة اكتمال ملف المنشأة.' }}</p>
                                <button type="button" x-data x-on:click="$dispatch('open-modal', 'add-doc')" class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#0A2540] text-white font-bold rounded-xl hover:bg-[#0a2540]/90 text-sm transition-all">
                                    <i class="fas fa-plus text-xs"></i> <span>{{ __('add_compliance_document') ?: 'إضافة وثيقة امتثال' }}</span>
                                </button>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endif

            {{-- 5. Service Catalog Section --}}
            @if($section === 'requests')
                @php
                    $selectedService = $this->selectedService;
                    $availableServices = $this->availableServices;
                    $serviceFilters = $this->servicePlatformFilters();
                    $locale = app()->getLocale() === 'en' ? 'en' : 'ar';
                    $step = (int) $serviceRequestStep;
                    $wizardSteps = [
                        1 => __('wizard_step_choose_service') ?: 'اختيار الخدمة',
                        2 => __('wizard_step_request_details') ?: 'تفاصيل الطلب',
                        3 => __('wizard_step_review_confirm') ?: 'مراجعة وتأكيد',
                        4 => __('wizard_step_done') ?: 'تم الإنشاء',
                    ];
                @endphp
                <div class="p-6 animate__animated animate__fadeIn space-y-6">
                    {{-- Header --}}
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-black text-[#0A2540]">{{ __('request_service_action') ?: 'طلب خدمة' }}</h2>
                            <p class="text-xs text-slate-500 font-medium mt-0.5">{{ __('dashboard_service_wizard_hint') ?: 'اتبع الخطوات لإنشاء طلب جديد.' }}</p>
                        </div>
                        <a href="{{ route('dashboard') }}?section=request-history" wire:navigate class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-sm font-bold text-[#0A2540] hover:bg-slate-50">
                            <i class="fas fa-list-check text-xs"></i>
                            <span>{{ __('nav_request_history') ?: 'سجل الطلبات' }}</span>
                        </a>
                    </div>

                    {{-- Wizard stepper --}}
                    <div class="grid grid-cols-4 gap-2">
                        @foreach($wizardSteps as $idx => $label)
                            @php
                                $isActive = $step === $idx;
                                $isDone   = $step > $idx;
                            @endphp
                            <div class="rounded-xl border px-3 py-2 {{ $isActive ? 'bg-[#0A2540] text-white border-[#0A2540]' : ($isDone ? 'bg-[#1FA7A2]/10 text-[#1FA7A2] border-[#1FA7A2]/20' : 'bg-slate-50 text-slate-500 border-slate-100') }}">
                                <div class="text-[9px] font-black opacity-70">{{ __('wizard_step_label') ?: 'الخطوة' }} {{ $idx }}</div>
                                <div class="text-[11px] font-black truncate">{{ $label }}</div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Step 1: Service Catalog --}}
                    @if($step === 1)
                        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 space-y-4">
                            <div class="relative">
                                <input type="search"
                                       wire:model.live.debounce.300ms="serviceSearch"
                                       placeholder="{{ __('service_search_placeholder') ?: 'ابحث عن خدمة...' }}"
                                       class="w-full h-12 rounded-xl bg-slate-50 border border-slate-200 focus:border-[#0A2540] focus:ring-2 focus:ring-[#0A2540]/10 text-sm font-bold ps-11 pe-4 outline-none transition-all">
                                <i class="fas fa-search absolute top-1/2 -translate-y-1/2 start-4 text-slate-400 text-sm"></i>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                @foreach($serviceFilters as $filterKey => $filterLabel)
                                    <button type="button"
                                            wire:click="$set('servicePlatformFilter', '{{ $filterKey }}')"
                                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $servicePlatformFilter === $filterKey ? 'bg-[#0A2540] text-white' : 'bg-slate-50 text-slate-600 border border-slate-200 hover:border-[#0A2540]/30' }}">
                                        {{ $filterLabel }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                            @forelse($availableServices as $service)
                                @php
                                    $title = $locale === 'en'
                                        ? ($service->title_en ?? $service->title_ar ?? $service->slug)
                                        : ($service->title_ar ?? $service->title_en ?? $service->slug);
                                    $excerpt = $locale === 'en'
                                        ? ($service->excerpt_en ?? $service->excerpt_ar)
                                        : ($service->excerpt_ar ?? $service->excerpt_en);
                                    $isSelected = (int) $serviceRequestServiceId === (int) $service->id;
                                @endphp
                                <button type="button"
                                        wire:click="selectServiceForRequest({{ $service->id }})"
                                        wire:key="wizard-svc-{{ $service->id }}"
                                        class="group h-full text-start bg-white rounded-2xl border {{ $isSelected ? 'border-[#0A2540] ring-2 ring-[#0A2540]/10' : 'border-slate-100' }} shadow-sm p-5 hover:border-[#0A2540]/30 hover:shadow-md transition-all cursor-pointer">
                                    <div class="flex items-start justify-between gap-3 mb-3">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-bold bg-[#1FA7A2]/5 text-[#1FA7A2] border border-[#1FA7A2]/10">
                                            {{ $service->platform?->name ?: __('other') }}
                                        </span>
                                        @if($isSelected)
                                            <span class="w-7 h-7 rounded-lg bg-[#0A2540] text-white flex items-center justify-center">
                                                <i class="fas fa-check text-xs"></i>
                                            </span>
                                        @endif
                                    </div>
                                    <h3 class="text-base font-black text-[#0A2540] leading-snug mb-2">{{ $title }}</h3>
                                    <p class="text-xs text-slate-500 font-medium leading-relaxed line-clamp-3 min-h-[3.8rem]">
                                        {{ $excerpt ? Str::limit(strip_tags($excerpt), 120) : __('service_no_excerpt') }}
                                    </p>
                                    <div class="mt-4 flex flex-wrap items-center gap-2 text-[11px] text-slate-500 font-bold">
                                        @if($service->duration)
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-slate-50 border border-slate-100">
                                                <i class="far fa-clock text-[#1FA7A2]"></i>{{ $service->duration }}
                                            </span>
                                        @endif
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-slate-50 border border-slate-100">
                                            <i class="fas fa-tag text-[#1FA7A2]"></i>
                                            @if((float) ($service->price ?? 0) > 0)
                                                {{ $service->price }} {{ __('SAR') }}
                                            @else
                                                {{ __('cost_after_review') ?: 'تحدد بعد المراجعة' }}
                                            @endif
                                        </span>
                                    </div>
                                    <span class="mt-4 w-full inline-flex items-center justify-center gap-2 rounded-xl py-2.5 text-xs font-black {{ $isSelected ? 'bg-[#0A2540] text-white' : 'bg-[#0A2540]/5 text-[#0A2540] group-hover:bg-[#0A2540] group-hover:text-white' }} transition-all">
                                        {{ __('select_service') ?: 'اختيار الخدمة' }}
                                        <i class="fas fa-arrow-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }} text-[10px]"></i>
                                    </span>
                                </button>
                            @empty
                                <div class="md:col-span-2 xl:col-span-3 rounded-2xl border border-dashed border-slate-200 bg-white p-10 text-center">
                                    <div class="w-14 h-14 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400 mx-auto mb-3">
                                        <i class="fas fa-search text-xl"></i>
                                    </div>
                                    <h3 class="text-sm font-black text-slate-700 mb-1">{{ __('no_services_found') ?: 'لا توجد خدمات مطابقة' }}</h3>
                                    <p class="text-xs text-slate-500 font-medium">{{ __('try_different_filter') ?: 'جرّب فلترًا مختلفًا أو ابحث بكلمة أخرى.' }}</p>
                                </div>
                            @endforelse
                        </div>

                    {{-- Step 2: Request Details --}}
                    @elseif($step === 2 && $selectedService)
                        @php
                            $serviceTitle = $locale === 'en'
                                ? ($selectedService->title_en ?? $selectedService->title_ar ?? $selectedService->slug)
                                : ($selectedService->title_ar ?? $selectedService->title_en ?? $selectedService->slug);
                            $serviceExcerpt = $locale === 'en'
                                ? ($selectedService->excerpt_en ?? $selectedService->excerpt_ar)
                                : ($selectedService->excerpt_ar ?? $selectedService->excerpt_en);
                        @endphp
                        <form wire:submit="reviewServiceRequest" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 space-y-5">
                            <div class="rounded-2xl bg-[#1FA7A2]/5 border border-[#1FA7A2]/10 p-4 flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="text-[11px] font-bold text-[#1FA7A2] mb-1">{{ __('selected_service_label') ?: 'الخدمة المختارة' }}</div>
                                    <div class="text-sm font-black text-[#0A2540] truncate">{{ $serviceTitle }}</div>
                                    @if($serviceExcerpt)
                                        <p class="text-xs text-slate-500 font-medium leading-relaxed mt-2">{{ Str::limit(strip_tags($serviceExcerpt), 140) }}</p>
                                    @endif
                                </div>
                                <button type="button" wire:click="changeServiceRequestService" class="text-[11px] font-black text-[#1FA7A2] hover:underline shrink-0">{{ __('change_service') ?: 'تغيير الخدمة' }}</button>
                            </div>

                            <div class="space-y-1">
                                <label class="text-xs font-bold text-slate-600">{{ __('request_details_label') ?: 'تفاصيل الطلب' }} <span class="text-rose-500">*</span></label>
                                <textarea wire:model.blur="serviceRequestNotes" rows="6" class="w-full rounded-xl bg-slate-50 border border-slate-200 focus:border-[#0A2540] focus:ring-2 focus:ring-[#0A2540]/10 text-sm font-medium py-2.5 outline-none resize-none transition-all" placeholder="{{ __('dashboard_service_request_placeholder') ?: 'اكتب تفاصيل الطلب أو أي ملاحظات يحتاجها الفريق.' }}"></textarea>
                                @error('serviceRequestNotes') <span class="text-[11px] text-rose-500 font-bold block">{{ $message }}</span> @enderror
                            </div>

                            {{-- Phase 3: optional attachments — Etmam Step 2 parity (PDF/DOCX/JPG/PNG, 10MB each). --}}
                            <div class="space-y-2">
                                <label class="text-xs font-bold text-slate-600 flex items-center gap-1.5">
                                    <i class="fas fa-paperclip text-[#1FA7A2] text-[11px]"></i>
                                    {{ __('Request attachments (optional)') === 'Request attachments (optional)' ? 'مرفقات الطلب (اختياري)' : __('Request attachments (optional)') }}
                                </label>
                                <label for="serviceRequestAttachmentsInput"
                                       class="flex flex-col items-center justify-center gap-2 cursor-pointer rounded-2xl border-2 border-dashed border-slate-200 hover:border-[#1FA7A2]/40 hover:bg-[#1FA7A2]/5 transition-colors px-4 py-6 text-center">
                                    <i class="fas fa-cloud-upload-alt text-[#1FA7A2] text-xl"></i>
                                    <span class="text-[12px] font-bold text-slate-700">
                                        {{ __('Click to choose files') === 'Click to choose files' ? 'اضغط لاختيار ملفات' : __('Click to choose files') }}
                                    </span>
                                    <span class="text-[10px] font-medium text-slate-400">
                                        {{ __('Accepted: PDF, DOC, DOCX, JPG, PNG — max 10MB each') === 'Accepted: PDF, DOC, DOCX, JPG, PNG — max 10MB each' ? 'المسموح: PDF · DOC · DOCX · JPG · PNG — حتى 10 ميجابايت لكل ملف' : __('Accepted: PDF, DOC, DOCX, JPG, PNG — max 10MB each') }}
                                    </span>
                                    <input id="serviceRequestAttachmentsInput"
                                           type="file"
                                           multiple
                                           class="sr-only"
                                           wire:model="serviceRequestAttachments"
                                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/jpeg,image/png">
                                </label>

                                <div wire:loading wire:target="serviceRequestAttachments" class="text-[11px] font-bold text-slate-500 flex items-center gap-1.5">
                                    <i class="fas fa-circle-notch fa-spin"></i>
                                    <span>{{ __('Uploading') === 'Uploading' ? 'جاري الرفع…' : __('Uploading') }}</span>
                                </div>

                                @if(! empty($serviceRequestAttachments))
                                    <ul class="space-y-1.5 mt-2">
                                        @foreach($serviceRequestAttachments as $idx => $file)
                                            @php
                                                $hasName = is_object($file) && method_exists($file, 'getClientOriginalName');
                                                $fileName = $hasName ? $file->getClientOriginalName() : ('attachment-' . ($idx + 1));
                                                $fileSize = is_object($file) && method_exists($file, 'getSize') ? (int) $file->getSize() : 0;
                                                $fileSizeLabel = $fileSize >= 1048576
                                                    ? round($fileSize / 1048576, 1) . ' MB'
                                                    : ($fileSize >= 1024 ? round($fileSize / 1024, 1) . ' KB' : ($fileSize . ' B'));
                                            @endphp
                                            <li wire:key="srf-{{ $idx }}-{{ $fileName }}"
                                                class="flex items-center justify-between gap-2 px-3 py-2 rounded-xl bg-slate-50 border border-slate-100">
                                                <div class="flex items-center gap-2.5 min-w-0">
                                                    <span class="w-7 h-7 rounded-lg bg-[#1FA7A2]/10 text-[#1FA7A2] flex items-center justify-center shrink-0">
                                                        <i class="fas fa-file-alt text-[11px]"></i>
                                                    </span>
                                                    <div class="min-w-0">
                                                        <div class="text-[12px] font-bold text-slate-700 truncate">{{ $fileName }}</div>
                                                        <div class="text-[10px] font-mono text-slate-400">{{ $fileSizeLabel }}</div>
                                                    </div>
                                                </div>
                                                <button type="button"
                                                        wire:click="removeServiceRequestAttachment({{ $idx }})"
                                                        class="w-7 h-7 rounded-lg bg-white border border-slate-200 text-rose-500 hover:bg-rose-50 hover:border-rose-200 inline-flex items-center justify-center shrink-0"
                                                        title="{{ __('remove_file') ?: 'إزالة الملف' }}">
                                                    <i class="fas fa-times text-[10px]"></i>
                                                </button>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif

                                @error('serviceRequestAttachments.*') <span class="text-[11px] text-rose-500 font-bold block">{{ $message }}</span> @enderror
                                @error('serviceRequestAttachments') <span class="text-[11px] text-rose-500 font-bold block">{{ $message }}</span> @enderror
                            </div>

                            <div class="flex justify-between gap-3">
                                <button type="button" wire:click="changeServiceRequestService" class="h-11 px-5 rounded-xl bg-white border border-slate-200 text-sm font-black text-[#0A2540] hover:bg-slate-50">
                                    {{ __('back') ?: 'رجوع' }}
                                </button>
                                <button type="submit" class="h-11 px-6 rounded-xl bg-[#0A2540] text-white text-sm font-black hover:bg-[#0a2540]/90 inline-flex items-center gap-2"
                                        wire:loading.attr="disabled"
                                        wire:target="serviceRequestAttachments,reviewServiceRequest">
                                    {{ __('continue_to_review') ?: 'متابعة للمراجعة' }}
                                    <i class="fas fa-arrow-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }} text-[10px]"></i>
                                </button>
                            </div>
                        </form>

                    {{-- Step 3: Review & Confirm --}}
                    @elseif($step === 3 && $selectedService)
                        @php
                            $serviceTitle = $locale === 'en'
                                ? ($selectedService->title_en ?? $selectedService->title_ar ?? $selectedService->slug)
                                : ($selectedService->title_ar ?? $selectedService->title_en ?? $selectedService->slug);
                        @endphp
                        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 space-y-5">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                                <div class="rounded-xl bg-slate-50 border border-slate-100 p-3">
                                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('Service') ?: 'الخدمة' }}</div>
                                    <div class="font-black text-[#0A2540] mt-1">{{ $serviceTitle }}</div>
                                </div>
                                <div class="rounded-xl bg-slate-50 border border-slate-100 p-3">
                                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('company') ?: 'المنشأة' }}</div>
                                    <div class="font-black text-[#0A2540] mt-1 truncate">{{ $this->activeCompany?->name }}</div>
                                </div>
                                <div class="rounded-xl bg-slate-50 border border-slate-100 p-3">
                                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('requester') ?: 'مقدم الطلب' }}</div>
                                    <div class="font-black text-[#0A2540] mt-1 truncate">{{ auth()->user()?->name }}</div>
                                </div>
                                <div class="rounded-xl bg-slate-50 border border-slate-100 p-3">
                                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('duration') ?: 'المدة' }}</div>
                                    <div class="font-black text-[#0A2540] mt-1">{{ $selectedService->duration ?: (__('duration_after_review') ?: 'تحدد بعد المراجعة') }}</div>
                                </div>
                                <div class="rounded-xl bg-slate-50 border border-slate-100 p-3 md:col-span-2">
                                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('cost') ?: 'التكلفة' }}</div>
                                    <div class="font-black text-[#0A2540] mt-1">
                                        @if((float) ($selectedService->price ?? 0) > 0)
                                            {{ $selectedService->price }} {{ __('SAR') }}
                                        @else
                                            {{ __('cost_after_review') ?: 'تحدد بعد المراجعة' }}
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-xl bg-slate-50 border border-slate-100 p-3">
                                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('request_details_label') ?: 'تفاصيل الطلب' }}</div>
                                <div class="text-sm text-slate-700 font-medium mt-1 whitespace-pre-line">{{ $serviceRequestNotes }}</div>
                            </div>

                            <div class="flex justify-between gap-3">
                                <button type="button" wire:click="backToServiceRequestDetails" class="h-11 px-5 rounded-xl bg-white border border-slate-200 text-sm font-black text-[#0A2540] hover:bg-slate-50">
                                    {{ __('back') ?: 'رجوع' }}
                                </button>
                                <button type="button" wire:click="createServiceRequest" wire:loading.attr="disabled" wire:target="createServiceRequest" class="h-11 px-6 rounded-xl bg-[#0A2540] text-white text-sm font-black hover:bg-[#0a2540]/90 disabled:opacity-60 disabled:cursor-not-allowed inline-flex items-center justify-center gap-2">
                                    <span wire:loading.remove wire:target="createServiceRequest">
                                        {{ __('confirm_request') ?: 'تأكيد الطلب' }}
                                    </span>
                                    <span wire:loading wire:target="createServiceRequest" class="inline-flex items-center gap-2">
                                        <i class="fas fa-circle-notch fa-spin"></i>
                                        {{ __('processing') }}
                                    </span>
                                </button>
                            </div>
                        </div>

                    {{-- Step 4: Success --}}
                    @elseif($step === 4 && $createdServiceRequestId)
                        <div class="bg-white rounded-2xl border border-emerald-100 shadow-sm p-8 text-center space-y-5">
                            <div class="w-16 h-16 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-500 mx-auto">
                                <i class="fas fa-check-double text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-black text-[#0A2540] mb-2">{{ __('service_request_created_successfully') ?: 'تم إنشاء الطلب بنجاح' }}</h3>
                                <p class="text-sm text-slate-500 font-medium">{{ __('service_request_created_hint') ?: 'سنبدأ مراجعة الطلب وسنتواصل معك قريبًا.' }}</p>
                            </div>
                            <div class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-100">
                                <span class="text-xs font-bold text-slate-500">{{ __('request_number') ?: 'رقم الطلب' }}:</span>
                                <span class="font-mono font-black text-[#1FA7A2]">#{{ $createdServiceRequestId }}</span>
                            </div>
                            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                                <a href="{{ route('dashboard') }}?section=request-history&request_id={{ $createdServiceRequestId }}" wire:navigate class="h-11 px-6 rounded-xl bg-[#0A2540] text-white text-sm font-black hover:bg-[#0a2540]/90 inline-flex items-center justify-center gap-2">
                                    <i class="fas fa-eye text-xs"></i>
                                    {{ __('view_request') ?: 'عرض الطلب' }}
                                </a>
                                <button type="button" wire:click="startNewServiceRequest" class="h-11 px-6 rounded-xl bg-white border border-slate-200 text-sm font-black text-[#0A2540] hover:bg-slate-50 inline-flex items-center justify-center gap-2">
                                    <i class="fas fa-plus text-xs"></i>
                                    {{ __('request_another_service') ?: 'طلب خدمة أخرى' }}
                                </button>
                            </div>
                        </div>

                    {{-- Fallback (shouldn't trigger normally) --}}
                    @else
                        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 text-center">
                            <p class="text-sm font-bold text-slate-500 mb-3">{{ __('choose_service_first') ?: 'اختر خدمة للمتابعة.' }}</p>
                            <button type="button" wire:click="$set('serviceRequestStep', 1)" class="h-10 px-5 rounded-xl bg-[#0A2540] text-white text-xs font-black">{{ __('wizard_step_choose_service') ?: 'اختيار الخدمة' }}</button>
                        </div>
                    @endif
                </div>
            @endif

            {{-- 6. Request History Section --}}
            @if($section === 'request-history')
                @php
                    $serviceRequests = $this->serviceRequests;
                    $historyStats = $this->requestHistoryStats;
                    $historyStatusFilters = $this->requestHistoryStatusFilters();
                    $selectedReq = $this->selectedServiceRequest;
                    $locale = app()->getLocale() === 'en' ? 'en' : 'ar';
                @endphp
                <div class="p-6 animate__animated animate__fadeIn space-y-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-black text-[#0A2540]">{{ __('service_requests_history') }}</h2>
                            <p class="text-xs text-slate-500 font-medium mt-0.5">{{ __('service_requests_history_hint') ?: 'تابع طلبات الخدمات المرتبطة بمنشأتك النشطة.' }}</p>
                        </div>
                        <a href="{{ route('dashboard') }}?section=requests" wire:navigate class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-[#0A2540] text-white text-sm font-bold hover:bg-[#0a2540]/90">
                            <i class="fas fa-plus text-xs"></i>
                            <span>{{ __('request_service_action') ?: 'طلب خدمة' }}</span>
                        </a>
                    </div>

                    {{-- Stats banner --}}
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                        @php
                            $statCards = [
                                ['key' => 'all',              'label' => __('history_stats_all')              ?: 'الكل',              'icon' => 'fa-layer-group',  'color' => 'slate'],
                                ['key' => 'new',              'label' => __('history_stats_new')              ?: 'الجديدة',           'icon' => 'fa-star',         'color' => 'teal'],
                                ['key' => 'in_review',        'label' => __('history_stats_in_review')        ?: 'قيد المراجعة',     'icon' => 'fa-search',       'color' => 'amber'],
                                ['key' => 'pending_customer', 'label' => __('history_stats_pending_customer') ?: 'بانتظار العميل',    'icon' => 'fa-hourglass-half','color' => 'rose'],
                                ['key' => 'completed',        'label' => __('history_stats_completed')        ?: 'المكتملة',          'icon' => 'fa-check-double', 'color' => 'teal'],
                            ];
                        @endphp
                        @foreach($statCards as $stat)
                            @php
                                $statIconClass = match ($stat['color']) {
                                    'teal' => 'bg-[#1FA7A2]/10 text-[#1FA7A2]',
                                    'amber' => 'bg-amber-50 text-amber-600',
                                    'rose' => 'bg-rose-50 text-rose-500',
                                    default => 'bg-slate-50 text-slate-500',
                                };
                            @endphp
                            <button type="button"
                                    wire:click="$set('requestHistoryStatusFilter', '{{ $stat['key'] }}')"
                                    class="bg-white rounded-2xl border {{ $requestHistoryStatusFilter === $stat['key'] ? 'border-[#0A2540] ring-2 ring-[#0A2540]/10' : 'border-slate-100' }} shadow-sm p-3 text-start hover:border-[#0A2540]/30 transition-all cursor-pointer">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="min-w-0">
                                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest truncate">{{ $stat['label'] }}</div>
                                        <div class="text-xl font-black text-[#0A2540] mt-1">{{ $historyStats[$stat['key']] ?? 0 }}</div>
                                    </div>
                                    <span class="w-9 h-9 rounded-xl {{ $statIconClass }} flex items-center justify-center shrink-0">
                                        <i class="fas {{ $stat['icon'] }} text-sm"></i>
                                    </span>
                                </div>
                            </button>
                        @endforeach
                    </div>

                    {{-- Search + filter chips --}}
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 space-y-3">
                        <div class="relative">
                            <input type="search"
                                   wire:model.live.debounce.300ms="requestHistorySearch"
                                   placeholder="{{ __('request_history_search_placeholder') ?: 'ابحث برقم الطلب أو اسم الخدمة...' }}"
                                   class="w-full h-11 rounded-xl bg-slate-50 border border-slate-200 focus:border-[#0A2540] focus:ring-2 focus:ring-[#0A2540]/10 text-sm font-bold ps-11 pe-4 outline-none transition-all">
                            <i class="fas fa-search absolute top-1/2 -translate-y-1/2 start-4 text-slate-400 text-sm"></i>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @foreach($historyStatusFilters as $statusKey => $statusLabel)
                                <button type="button"
                                        wire:click="$set('requestHistoryStatusFilter', '{{ $statusKey }}')"
                                        class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $requestHistoryStatusFilter === $statusKey ? 'bg-[#0A2540] text-white' : 'bg-slate-50 text-slate-600 border border-slate-200 hover:border-[#0A2540]/30' }}">
                                    {{ $statusLabel }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Requests table --}}
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                        <table class="w-full text-sm text-start">
                            <thead class="bg-slate-50 text-slate-500 font-bold text-[11px] uppercase tracking-widest border-b border-slate-100">
                                <tr>
                                    <th class="px-4 py-3 text-start">{{ __('table_request_number') ?: 'رقم الطلب' }}</th>
                                    <th class="px-4 py-3 text-start">{{ __('Service') ?: 'الخدمة' }}</th>
                                    <th class="px-4 py-3 text-start">{{ __('table_status') }}</th>
                                    <th class="px-4 py-3 text-start hidden md:table-cell">{{ __('table_date') }}</th>
                                    <th class="px-4 py-3 text-center">{{ __('table_actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @forelse($serviceRequests as $request)
                                    @php
                                        $requestService = $request->service;
                                        $requestTitle = $requestService
                                            ? ($locale === 'en'
                                                ? ($requestService->title_en ?? $requestService->title_ar ?? $requestService->slug)
                                                : ($requestService->title_ar ?? $requestService->title_en ?? $requestService->slug))
                                            : __('Service');
                                    @endphp
                                    <tr wire:key="req-row-{{ $request->id }}" class="hover:bg-slate-50/50 transition-colors cursor-pointer" wire:click="viewServiceRequest({{ $request->id }})">
                                        <td class="px-4 py-3 font-mono font-black text-slate-700">#{{ $request->id }}</td>
                                        <td class="px-4 py-3">
                                            <div class="font-black text-slate-800 truncate">{{ $requestTitle }}</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-black bg-[#1FA7A2]/5 text-[#1FA7A2] border border-[#1FA7A2]/10">
                                                {{ $this->translateStatus($request->status) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 hidden md:table-cell text-slate-500 text-xs font-mono">
                                            {{ optional($request->created_at)->format('Y-m-d') }}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <button type="button" wire:click.stop="viewServiceRequest({{ $request->id }})" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#0A2540]/5 text-[#0A2540] hover:bg-[#0A2540] hover:text-white text-[11px] font-black transition-colors">
                                                <i class="fas fa-eye text-[10px]"></i>
                                                {{ __('view_details') ?: 'عرض التفاصيل' }}
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center">
                                            <div class="w-14 h-14 rounded-2xl bg-[#1FA7A2]/5 flex items-center justify-center text-[#1FA7A2] mx-auto mb-3">
                                                <i class="fas fa-clipboard-list text-2xl"></i>
                                            </div>
                                            <h4 class="text-sm font-black text-slate-700 mb-1">{{ __('no_service_requests_yet') ?: 'لم تقم بطلب أي خدمة حتى الآن' }}</h4>
                                            <p class="text-xs text-slate-500 font-medium mb-4">{{ __('service_requests_empty_hint') ?: 'ابدأ بطلب خدمة من النموذج هنا، وسيظهر الطلب في هذا الجدول مباشرة.' }}</p>
                                            <a href="{{ route('dashboard') }}?section=requests" wire:navigate class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#0A2540] text-white text-xs font-black">
                                                <i class="fas fa-plus"></i>
                                                {{ __('request_service_action') ?: 'اطلب خدمة جديدة' }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Details panel (inline; appears below the table when a request is selected) --}}
                    @if($selectedReq)
                        @php
                            $selSvc = $selectedReq->service;
                            $selTitle = $selSvc
                                ? ($locale === 'en' ? ($selSvc->title_en ?? $selSvc->title_ar ?? $selSvc->slug) : ($selSvc->title_ar ?? $selSvc->title_en ?? $selSvc->slug))
                                : __('Service');
                            // Phase 4: visual-only 7-step timeline that maps the existing
                            // ServiceRequest statuses (new / in_review / pending_customer /
                            // processing / completed) onto Etmam's longer journey. Database
                            // statuses are NOT changed — `state` here is presentation only.
                            $srStatus = (string) $selectedReq->status;
                            $srState = function (array $doneStatuses, ?string $currentStatus = null) use ($srStatus): string {
                                if (in_array($srStatus, $doneStatuses, true)) {
                                    return 'done';
                                }
                                if ($currentStatus !== null && $srStatus === $currentStatus) {
                                    return 'current';
                                }
                                return 'pending';
                            };
                            $timelineSteps = [
                                ['label' => __('timeline_request_created') === 'timeline_request_created' ? 'تم إنشاء الطلب' : __('timeline_request_created'),
                                 'state' => 'done'],
                                ['label' => __('timeline_in_review') === 'timeline_in_review' ? 'قيد المراجعة' : __('timeline_in_review'),
                                 'state' => $srState(['in_review', 'pending_customer', 'processing', 'completed'], 'new')],
                                ['label' => __('timeline_price_proposed') === 'timeline_price_proposed' ? 'اقتراح السعر' : __('timeline_price_proposed'),
                                 'state' => $srState(['pending_customer', 'processing', 'completed'], 'in_review')],
                                ['label' => __('timeline_awaiting_customer') === 'timeline_awaiting_customer' ? 'بانتظار موافقة العميل' : __('timeline_awaiting_customer'),
                                 'state' => $srState(['processing', 'completed'], 'pending_customer')],
                                ['label' => __('timeline_awaiting_payment') === 'timeline_awaiting_payment' ? 'بانتظار الدفع' : __('timeline_awaiting_payment'),
                                 // No dedicated DB status; treated as a transient stage between
                                 // pending_customer and processing for the visual stepper only.
                                 'state' => $srState(['processing', 'completed'])],
                                ['label' => __('timeline_in_execution') === 'timeline_in_execution' ? 'قيد التنفيذ' : __('timeline_in_execution'),
                                 'state' => $srState(['completed'], 'processing')],
                                ['label' => __('timeline_completed') === 'timeline_completed' ? 'مكتمل' : __('timeline_completed'),
                                 'state' => $srStatus === 'completed' ? 'done' : 'pending'],
                            ];
                        @endphp
                        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-5 animate__animated animate__fadeInUp" wire:key="req-detail-{{ $selectedReq->id }}">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="font-mono font-black text-[#1FA7A2]">#{{ $selectedReq->id }}</span>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-black bg-[#1FA7A2]/5 text-[#1FA7A2] border border-[#1FA7A2]/10">
                                            {{ $this->translateStatus($selectedReq->status) }}
                                        </span>
                                    </div>
                                    <h3 class="text-base font-black text-[#0A2540] truncate">{{ $selTitle }}</h3>
                                </div>
                                <button type="button" wire:click="closeServiceRequestDetails" class="w-9 h-9 rounded-lg bg-slate-50 text-slate-500 hover:bg-slate-100 hover:text-slate-700 inline-flex items-center justify-center" aria-label="{{ __('close') ?: 'إغلاق' }}">
                                    <i class="fas fa-xmark"></i>
                                </button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                                <div class="rounded-xl bg-slate-50 border border-slate-100 p-3">
                                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('company') ?: 'المنشأة' }}</div>
                                    <div class="font-black text-[#0A2540] mt-1 truncate">{{ $selectedReq->establishment_name ?? $this->activeCompany?->name }}</div>
                                </div>
                                <div class="rounded-xl bg-slate-50 border border-slate-100 p-3">
                                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('requester') ?: 'مقدم الطلب' }}</div>
                                    <div class="font-black text-[#0A2540] mt-1 truncate">{{ $selectedReq->name ?? auth()->user()?->name }}</div>
                                </div>
                                <div class="rounded-xl bg-slate-50 border border-slate-100 p-3">
                                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('table_date') }}</div>
                                    <div class="font-black text-[#0A2540] mt-1 font-mono">{{ optional($selectedReq->created_at)->format('Y-m-d H:i') }}</div>
                                </div>
                                <div class="rounded-xl bg-slate-50 border border-slate-100 p-3">
                                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('cr_number') ?: 'السجل التجاري' }}</div>
                                    <div class="font-black text-[#0A2540] mt-1 font-mono">{{ $selectedReq->cr_number ?: '—' }}</div>
                                </div>
                            </div>

                            <div class="rounded-xl bg-slate-50 border border-slate-100 p-3">
                                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">{{ __('request_details_label') ?: 'تفاصيل الطلب' }}</div>
                                <div class="text-sm text-slate-700 font-medium whitespace-pre-line">{{ $selectedReq->description ?? $selectedReq->notes ?? '—' }}</div>
                            </div>

                            {{-- Phase 3: attachments uploaded via the wizard — secure download via SecureFileController. --}}
                            @php $reqAttachments = $this->selectedServiceRequestAttachments; @endphp
                            @if($reqAttachments->isNotEmpty())
                                <div class="rounded-xl bg-white border border-slate-100 p-3">
                                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 flex items-center gap-1.5">
                                        <i class="fas fa-paperclip text-[#1FA7A2] text-[11px]"></i>
                                        {{ __('Attachments') ?: 'المرفقات' }}
                                        <span class="font-mono text-slate-400">({{ $reqAttachments->count() }})</span>
                                    </div>
                                    <ul class="space-y-1.5">
                                        @foreach($reqAttachments as $att)
                                            @php
                                                $size = (int) ($att->size ?? 0);
                                                $sizeLabel = $size >= 1048576
                                                    ? round($size / 1048576, 1) . ' MB'
                                                    : ($size >= 1024 ? round($size / 1024, 1) . ' KB' : ($size . ' B'));
                                            @endphp
                                            <li wire:key="sr-att-{{ $att->id }}"
                                                class="flex items-center justify-between gap-2 px-3 py-2 rounded-xl bg-slate-50 border border-slate-100">
                                                <div class="flex items-center gap-2.5 min-w-0">
                                                    <span class="w-7 h-7 rounded-lg bg-[#1FA7A2]/10 text-[#1FA7A2] flex items-center justify-center shrink-0">
                                                        <i class="{{ $att->icon_class ?: 'fas fa-paperclip' }} text-[11px]"></i>
                                                    </span>
                                                    <div class="min-w-0">
                                                        <div class="text-[12px] font-bold text-slate-700 truncate">{{ $att->original_name ?: ('attachment-' . $att->id) }}</div>
                                                        <div class="text-[10px] font-mono text-slate-400">{{ $sizeLabel }}</div>
                                                    </div>
                                                </div>
                                                <a href="{{ route('attachments.download', $att->id) }}"
                                                   class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-[#0A2540]/5 text-[#0A2540] hover:bg-[#0A2540] hover:text-white text-[11px] font-black shrink-0">
                                                    <i class="fas fa-download text-[10px]"></i>
                                                    <span class="hidden sm:inline">{{ __('download') ?: 'تنزيل' }}</span>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            {{-- Phase 4: messages thread between the customer and the support team. --}}
                            @php
                                $reqMessages = $this->selectedServiceRequestMessages;
                                $isAr = $locale === 'ar';
                            @endphp
                            <div class="rounded-xl bg-white border border-slate-100 p-3 space-y-3">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-1.5">
                                        <i class="fas fa-comments text-[#1FA7A2] text-[11px]"></i>
                                        {{ __('Messages') === 'Messages' ? 'الرسائل' : __('Messages') }}
                                        <span class="font-mono text-slate-400">({{ $reqMessages->count() }})</span>
                                    </div>
                                </div>

                                @if($reqMessages->isEmpty())
                                    <div class="px-2 py-5 text-center bg-slate-50 rounded-xl">
                                        <div class="w-10 h-10 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2">
                                            <i class="fas fa-comments text-base"></i>
                                        </div>
                                        <p class="text-[12px] font-black text-slate-700">{{ __('No messages yet') === 'No messages yet' ? 'لا توجد رسائل بعد' : __('No messages yet') }}</p>
                                        <p class="text-[10px] text-slate-500 font-medium mt-1">{{ __('Start the conversation with the AMR7 team below.') === 'Start the conversation with the AMR7 team below.' ? 'ابدأ المحادثة مع فريق شركة آمر سبعة لحلول الأعمال من النموذج أدناه.' : __('Start the conversation with the AMR7 team below.') }}</p>
                                    </div>
                                @else
                                    <ul class="space-y-2 max-h-72 overflow-y-auto custom-scrollbar pe-1">
                                        @foreach($reqMessages as $msg)
                                            @php
                                                $type = strtolower((string) ($msg->sender_type ?? 'client'));
                                                $isClient = $type === 'client';
                                                $isSystem = $type === 'system';
                                                $senderLabel = $isSystem
                                                    ? ($isAr ? 'تنبيه نظام' : 'System notice')
                                                    : ($isClient
                                                        ? (optional($msg->sender)->name ?: ($isAr ? 'أنت' : 'You'))
                                                        : (optional($msg->sender)->name ?: ($isAr ? 'فريق شركة آمر سبعة لحلول الأعمال' : 'Amr Seven Business Solutions team')));
                                                $bubbleBg = $isSystem
                                                    ? 'bg-amber-50 border-amber-100 text-amber-800'
                                                    : ($isClient
                                                        ? 'bg-[#0A2540]/5 border-[#0A2540]/10 text-[#0A2540]'
                                                        : 'bg-[#1FA7A2]/5 border-[#1FA7A2]/10 text-[#1FA7A2]');
                                                $alignClass = $isClient ? 'items-end' : 'items-start';
                                            @endphp
                                            <li wire:key="sr-msg-{{ $msg->id }}" class="flex flex-col {{ $alignClass }}">
                                                <div class="max-w-[88%] rounded-2xl border px-3 py-2 {{ $bubbleBg }}">
                                                    <div class="flex items-center justify-between gap-3 mb-1">
                                                        <span class="text-[10px] font-black uppercase tracking-widest opacity-70">{{ $senderLabel }}</span>
                                                        <span class="text-[10px] font-mono opacity-60">{{ optional($msg->created_at)->diffForHumans() }}</span>
                                                    </div>
                                                    <div class="text-[12px] font-medium leading-relaxed whitespace-pre-line">{{ $msg->body }}</div>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif

                                {{-- Customer reply form — backoffice uses Filament; this textarea posts as sender_type=client. --}}
                                <form wire:submit="submitServiceRequestReply" class="space-y-2 pt-2 border-t border-slate-100">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                        {{ __('Reply to the team') === 'Reply to the team' ? 'اكتب ردك للفريق' : __('Reply to the team') }}
                                    </label>
                                    <textarea wire:model.blur="serviceRequestReplyBody" rows="3"
                                              maxlength="5000"
                                              class="w-full rounded-xl bg-slate-50 border border-slate-200 focus:border-[#0A2540] focus:ring-2 focus:ring-[#0A2540]/10 text-sm font-medium py-2.5 px-3 outline-none resize-none transition-all"
                                              placeholder="{{ __('Write a short message…') === 'Write a short message…' ? 'اكتب رسالة قصيرة…' : __('Write a short message…') }}"></textarea>
                                    @error('serviceRequestReplyBody')
                                        <span class="text-[11px] text-rose-500 font-bold block">{{ $message }}</span>
                                    @enderror
                                    <div class="flex justify-end">
                                        <button type="submit"
                                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#0A2540] text-white text-[12px] font-black hover:bg-[#0a2540]/90 disabled:opacity-60 disabled:cursor-not-allowed"
                                                wire:loading.attr="disabled" wire:target="submitServiceRequestReply">
                                            <span wire:loading.remove wire:target="submitServiceRequestReply" class="inline-flex items-center gap-1.5">
                                                <i class="fas fa-paper-plane text-[10px]"></i>
                                                {{ __('Send reply') === 'Send reply' ? 'إرسال الرد' : __('Send reply') }}
                                            </span>
                                            <span wire:loading wire:target="submitServiceRequestReply" class="inline-flex items-center gap-1.5">
                                                <i class="fas fa-circle-notch fa-spin text-[10px]"></i>
                                                {{ __('processing') ?: 'جاري المعالجة' }}
                                            </span>
                                        </button>
                                    </div>
                                </form>
                            </div>

                            {{-- Status timeline --}}
                            <div>
                                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">{{ __('request_timeline') ?: 'سجل الحالة' }}</div>
                                <div class="space-y-2">
                                    @foreach($timelineSteps as $tStep)
                                        <div class="flex items-center gap-3">
                                            <span class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 {{ $tStep['state'] === 'done' ? 'bg-emerald-500 text-white' : ($tStep['state'] === 'current' ? 'bg-[#0A2540] text-white' : 'bg-slate-100 text-slate-400') }}">
                                                <i class="fas {{ $tStep['state'] === 'done' ? 'fa-check' : ($tStep['state'] === 'current' ? 'fa-circle-dot' : 'fa-circle') }} text-[10px]"></i>
                                            </span>
                                            <span class="text-sm font-bold {{ $tStep['state'] === 'pending' ? 'text-slate-400' : 'text-[#0A2540]' }}">{{ $tStep['label'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            {{-- 7. Financial Section --}}
            @if($section === 'financial')
                <div class="p-8 animate__animated animate__fadeIn">
                    <div class="flex justify-between items-center mb-8">
                        <h2 class="text-2xl font-black text-slate-800">{{ __('fs_requests_title') }}</h2>
                        <a href="{{ route('financial-statements.create') }}" class="bg-[#1FA7A2] hover:bg-[#167F7B] text-white text-sm font-bold py-3 px-6 rounded-xl flex items-center gap-2 shadow-lg shadow-[#1FA7A2]/20 hover:-translate-y-0.5 transition-all">
                            <i class="fas fa-plus"></i> {{ __('btn_new_fs_request') }}
                        </a>
                    </div>

                    <div class="overflow-hidden rounded-2xl border border-slate-100 shadow-sm">
                        <table class="w-full text-sm text-start">
                            <thead class="bg-slate-50 text-slate-500 font-black text-xs uppercase tracking-widest border-b border-slate-100">
                                <tr>
                                    <th class="p-4 text-start">{{ __('table_ref') }}</th>
                                    <th class="p-4 text-start">{{ __('table_period') }}</th>
                                    <th class="p-4 text-start">{{ __('table_status') }}</th>
                                    <th class="p-4 text-start">{{ __('table_date') }}</th>
                                    <th class="p-4 text-center">{{ __('table_actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @forelse($this->financialStatementRequests as $request)
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="p-4">
                                            <span class="font-mono text-slate-600 font-bold bg-slate-100 px-3 py-1 rounded-lg text-xs border border-slate-200">
                                                #{{ $request->id }}
                                            </span>
                                        </td>
                                        <td class="p-4 font-bold text-slate-700">
                                            {{ $request->fiscal_year ?? '---' }}
                                        </td>
                                        <td class="p-4">
                                            @php
                                                $statusColor = match($request->status) {
                                                    'pending' => 'bg-amber-100 text-amber-700',
                                                    'completed' => 'bg-emerald-100 text-emerald-700',
                                                    'rejected' => 'bg-rose-100 text-rose-700',
                                                    default => 'bg-slate-100 text-slate-600'
                                                };
                                            @endphp
                                            <span class="text-[10px] font-black px-3 py-1.5 rounded-lg {{ $statusColor }} uppercase tracking-wider">
                                                {{ __('status_' . $request->status) }}
                                            </span>
                                        </td>
                                        <td class="p-4 text-slate-500 text-xs font-mono font-bold">
                                            {{ $request->created_at->format('Y-m-d') }}
                                        </td>
                                        <td class="p-4 text-center">
                                            <a href="{{ route('financial-statements.show', $request->public_id ?? $request->id) }}" class="text-[#1FA7A2] bg-[#1FA7A2]/10 hover:bg-[#1FA7A2] hover:text-white px-4 py-2 rounded-lg font-bold text-xs transition-colors">
                                                {{ __('btn_view_details') }}
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center bg-slate-50/30">
                                            {{-- Polish: empty state للقوائم المالية --}}
                                            <div class="flex flex-col items-center gap-3">
                                                <div class="w-14 h-14 rounded-2xl bg-[#1FA7A2]/5 flex items-center justify-center text-[#1FA7A2]">
                                                    <i class="fas fa-file-invoice-dollar text-2xl"></i>
                                                </div>
                                                <div>
                                                    <h4 class="text-sm font-black text-slate-700 mb-1">{{ __('no_fs_requests') }}</h4>
                                                    <p class="text-xs text-slate-500 font-medium">{{ __('no_fs_requests_hint') }}</p>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- 5.5 Invoices Section (read-only for customer) --}}
            @if($section === 'invoices')
                @php
                    $invoices = $this->invoices;
                    $isAr = app()->getLocale() === 'ar';
                    $paymentsTableExists = \Illuminate\Support\Facades\Schema::hasTable('payments');
                @endphp
                <div class="p-6 animate__animated animate__fadeIn space-y-5">

                    {{-- Phase 9: header --}}
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-black text-[#0A2540]">{{ __('nav_invoices') ?: 'الفواتير' }}</h2>
                            <p class="text-xs text-slate-500 font-medium mt-0.5">
                                {{ __('invoices_intro') === 'invoices_intro' ? 'سجل فواتير منشأتك للاطلاع فقط. لإصدار فاتورة جديدة أو تعديل قائمة، تواصل مع فريق الحسابات.' : __('invoices_intro') }}
                            </p>
                        </div>
                        @if($invoices->isNotEmpty())
                            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-100 text-[11px] font-bold text-slate-600">
                                <i class="fas fa-file-invoice-dollar text-[#1FA7A2] text-[10px]"></i>
                                {{ $invoices->count() }} {{ __('invoices_count_suffix') === 'invoices_count_suffix' ? 'فاتورة' : __('invoices_count_suffix') }}
                            </span>
                        @endif
                    </div>

                    @if($invoices->isEmpty())
                        {{-- Phase 9: empty state --}}
                        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-10 text-center">
                            <div class="w-14 h-14 rounded-2xl bg-[#1FA7A2]/5 flex items-center justify-center text-[#1FA7A2] mx-auto mb-3">
                                <i class="fas fa-file-invoice-dollar text-2xl"></i>
                            </div>
                            <h3 class="text-sm font-black text-slate-700 mb-1">
                                {{ __('no_invoices_title') === 'no_invoices_title' ? 'لا توجد فواتير بعد' : __('no_invoices_title') }}
                            </h3>
                            <p class="text-xs text-slate-500 font-medium max-w-md mx-auto">
                                {{ __('no_invoices_hint') === 'no_invoices_hint' ? 'ستظهر هنا فواتير منشأتك فور إصدارها من فريق شركة آمر سبعة لحلول الأعمال.' : __('no_invoices_hint') }}
                            </p>
                        </div>
                    @else
                        {{-- Phase 9: invoices table with payment status + secondary actions --}}
                        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-slate-100 text-start text-sm">
                                    <thead class="bg-slate-50 text-[11px] font-black uppercase tracking-widest text-slate-500">
                                        <tr>
                                            <th class="px-4 py-3 text-start">{{ __('invoice_number_label') === 'invoice_number_label' ? 'رقم الفاتورة' : __('invoice_number_label') }}</th>
                                            <th class="px-4 py-3 text-start">{{ __('invoice_total_label') === 'invoice_total_label' ? 'الإجمالي' : __('invoice_total_label') }}</th>
                                            <th class="px-4 py-3 text-start">{{ __('invoice_status_label') === 'invoice_status_label' ? 'الحالة' : __('invoice_status_label') }}</th>
                                            <th class="px-4 py-3 text-start">{{ __('invoice_payment_status') === 'invoice_payment_status' ? 'حالة الدفع' : __('invoice_payment_status') }}</th>
                                            <th class="px-4 py-3 text-start hidden md:table-cell">{{ __('invoice_issue_date') === 'invoice_issue_date' ? 'تاريخ الإصدار' : __('invoice_issue_date') }}</th>
                                            <th class="px-4 py-3 text-start hidden md:table-cell">{{ __('invoice_due_date') === 'invoice_due_date' ? 'تاريخ الاستحقاق' : __('invoice_due_date') }}</th>
                                            <th class="px-4 py-3 text-center">{{ __('table_actions') ?: 'إجراءات' }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 bg-white">
                                        @foreach ($invoices as $inv)
                                            @php
                                                $totalPaid = $paymentsTableExists && method_exists($inv, 'totalPaid') ? (float) $inv->totalPaid() : 0.0;
                                                $totalAmount = (float) $inv->total;
                                                $payStatusKey = $totalAmount <= 0
                                                    ? 'unpaid'
                                                    : ($totalPaid >= $totalAmount ? 'paid' : ($totalPaid > 0 ? 'partial' : 'unpaid'));
                                                $payStatusLabel = match ($payStatusKey) {
                                                    'paid'    => ($isAr ? 'مدفوعة بالكامل' : 'Paid in full'),
                                                    'partial' => ($isAr ? 'مدفوعة جزئيًا'   : 'Partially paid'),
                                                    default   => ($isAr ? 'غير مدفوعة'      : 'Unpaid'),
                                                };
                                                $payStatusClass = match ($payStatusKey) {
                                                    'paid'    => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100',
                                                    'partial' => 'bg-amber-50 text-amber-700 ring-1 ring-amber-100',
                                                    default   => 'bg-rose-50 text-rose-600 ring-1 ring-rose-100',
                                                };
                                                $issueDate = $inv->issue_date ?? $inv->created_at;
                                                $invPayments = $paymentsTableExists ? $inv->payments()->latest('paid_at')->latest('id')->get() : collect();
                                            @endphp
                                            <tr x-data="{ open: false }" wire:key="inv-row-{{ $inv->id }}" class="hover:bg-slate-50/40 transition-colors">
                                                <td class="px-4 py-3 font-mono font-black text-slate-700">{{ $inv->invoice_number }}</td>
                                                <td class="px-4 py-3 font-bold text-slate-700">{{ number_format($totalAmount, 2) }} <span class="text-[11px] text-slate-400 font-mono">{{ $inv->currency ?: 'SAR' }}</span></td>
                                                <td class="px-4 py-3">
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-black {{ $this->getInvoiceStatusClasses($inv->status) }}">
                                                        {{ $this->getInvoiceStatusLabel($inv->status) }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-black {{ $payStatusClass }}">
                                                        {{ $payStatusLabel }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 text-slate-500 text-xs font-mono hidden md:table-cell">{{ $issueDate ? $issueDate->format('Y-m-d') : '—' }}</td>
                                                <td class="px-4 py-3 text-slate-500 text-xs font-mono hidden md:table-cell">{{ $inv->due_date?->format('Y-m-d') ?? '—' }}</td>
                                                <td class="px-4 py-3">
                                                    <div class="flex items-center justify-center gap-1.5">
                                                        <button type="button"
                                                                x-on:click="open = ! open"
                                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#0A2540]/5 text-[#0A2540] hover:bg-[#0A2540] hover:text-white text-[11px] font-black"
                                                                :aria-expanded="open.toString()">
                                                            <i class="fas fa-eye text-[10px]"></i>
                                                            <span class="hidden sm:inline" x-text="open ? '{{ __('hide_details') === 'hide_details' ? 'إخفاء التفاصيل' : __('hide_details') }}' : '{{ __('view_invoice_details') === 'view_invoice_details' ? 'عرض التفاصيل' : __('view_invoice_details') }}'"></span>
                                                        </button>
                                                        <button type="button"
                                                                disabled
                                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-50 text-slate-400 text-[11px] font-black cursor-not-allowed border border-slate-100"
                                                                title="{{ __('download_pdf_coming_soon') === 'download_pdf_coming_soon' ? 'قريبًا — تحميل PDF غير متاح بعد' : __('download_pdf_coming_soon') }}">
                                                            <i class="fas fa-file-pdf text-[10px]"></i>
                                                            <span class="hidden sm:inline">{{ __('download_pdf_btn') === 'download_pdf_btn' ? 'تحميل PDF' : __('download_pdf_btn') }}</span>
                                                        </button>
                                                        <button type="button"
                                                                disabled
                                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-50 text-slate-400 text-[11px] font-black cursor-not-allowed border border-slate-100"
                                                                title="{{ __('online_payment_unavailable') === 'online_payment_unavailable' ? 'الدفع الإلكتروني غير مفعل حاليًا' : __('online_payment_unavailable') }}">
                                                            <i class="fas fa-credit-card text-[10px]"></i>
                                                            <span class="hidden sm:inline">{{ __('pay_online_btn') === 'pay_online_btn' ? 'الدفع الإلكتروني' : __('pay_online_btn') }}</span>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            {{-- Expandable details row — pure Alpine, no Livewire round-trip. --}}
                                            <tr x-show="open" x-cloak wire:key="inv-detail-{{ $inv->id }}" x-transition.opacity>
                                                <td colspan="7" class="px-4 py-4 bg-slate-50/60">
                                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
                                                        <div class="rounded-xl bg-white border border-slate-100 p-3">
                                                            <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('invoice_subtotal_label') === 'invoice_subtotal_label' ? 'المجموع الفرعي' : __('invoice_subtotal_label') }}</div>
                                                            <div class="font-black text-[#0A2540] mt-0.5 font-mono">{{ number_format((float) $inv->subtotal, 2) }} {{ $inv->currency ?: 'SAR' }}</div>
                                                        </div>
                                                        <div class="rounded-xl bg-white border border-slate-100 p-3">
                                                            <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('invoice_vat_label') === 'invoice_vat_label' ? 'ضريبة القيمة المضافة' : __('invoice_vat_label') }}</div>
                                                            <div class="font-black text-[#0A2540] mt-0.5 font-mono">{{ number_format((float) $inv->vat_amount, 2) }} {{ $inv->currency ?: 'SAR' }}</div>
                                                        </div>
                                                        <div class="rounded-xl bg-white border border-slate-100 p-3">
                                                            <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('invoice_paid_so_far') === 'invoice_paid_so_far' ? 'المدفوع حتى الآن' : __('invoice_paid_so_far') }}</div>
                                                            <div class="font-black text-emerald-600 mt-0.5 font-mono">{{ number_format($totalPaid, 2) }} {{ $inv->currency ?: 'SAR' }}</div>
                                                        </div>
                                                    </div>

                                                    {{-- Payments history --}}
                                                    <div class="rounded-xl bg-white border border-slate-100 p-3">
                                                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 flex items-center gap-1.5">
                                                            <i class="fas fa-coins text-[#1FA7A2] text-[10px]"></i>
                                                            {{ __('invoice_payments_history') === 'invoice_payments_history' ? 'سجل المدفوعات' : __('invoice_payments_history') }}
                                                            <span class="font-mono text-slate-400">({{ $invPayments->count() }})</span>
                                                        </div>
                                                        @if($invPayments->isEmpty())
                                                            <p class="text-[11px] text-slate-400 font-medium text-center py-3">
                                                                {{ __('no_payments_recorded') === 'no_payments_recorded' ? 'لم تُسجَّل أي مدفوعات بعد.' : __('no_payments_recorded') }}
                                                            </p>
                                                        @else
                                                            <ul class="space-y-1.5">
                                                                @foreach($invPayments as $pay)
                                                                    @php
                                                                        $payStatus = strtolower((string) ($pay->status ?? ''));
                                                                        $payStatusBadge = match ($payStatus) {
                                                                            'paid'     => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100',
                                                                            'pending'  => 'bg-amber-50 text-amber-700 ring-1 ring-amber-100',
                                                                            'failed'   => 'bg-rose-50 text-rose-600 ring-1 ring-rose-100',
                                                                            'refunded' => 'bg-slate-50 text-slate-600 ring-1 ring-slate-100',
                                                                            'manual'   => 'bg-sky-50 text-sky-700 ring-1 ring-sky-100',
                                                                            default    => 'bg-slate-50 text-slate-600 ring-1 ring-slate-100',
                                                                        };
                                                                    @endphp
                                                                    <li wire:key="pay-{{ $pay->id }}" class="flex flex-wrap items-center justify-between gap-2 px-3 py-2 rounded-lg bg-slate-50 border border-slate-100">
                                                                        <div class="flex items-center gap-2.5 min-w-0">
                                                                            <span class="font-mono font-black text-[11px] text-slate-700">{{ number_format((float) $pay->amount, 2) }} {{ $pay->currency ?: ($inv->currency ?: 'SAR') }}</span>
                                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black {{ $payStatusBadge }}">{{ ucfirst($payStatus) }}</span>
                                                                            @if($pay->provider)
                                                                                <span class="text-[10px] text-slate-500 font-bold">{{ $pay->provider }}</span>
                                                                            @endif
                                                                        </div>
                                                                        <div class="flex items-center gap-3 text-[10px] text-slate-500 font-mono">
                                                                            @if($pay->provider_reference)
                                                                                <span title="{{ __('payment_reference') === 'payment_reference' ? 'رقم المرجع' : __('payment_reference') }}">#{{ \Illuminate\Support\Str::limit($pay->provider_reference, 24, '…') }}</span>
                                                                            @endif
                                                                            <span>{{ $pay->paid_at ? $pay->paid_at->format('Y-m-d') : ($pay->created_at?->format('Y-m-d') ?? '—') }}</span>
                                                                        </div>
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        @endif
                                                    </div>

                                                    <p class="text-[10px] text-slate-400 font-medium leading-relaxed mt-3">
                                                        <i class="fas fa-info-circle me-1 opacity-70"></i>
                                                        {{ __('invoice_disabled_actions_note') === 'invoice_disabled_actions_note' ? 'تحميل PDF والدفع الإلكتروني سيُفعّلان قريبًا. للاستفسار عن الفاتورة تواصل مع فريق الحسابات.' : __('invoice_disabled_actions_note') }}
                                                    </p>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            {{-- 6. Tickets Section --}}
            @if($section === 'tickets')
                <div class="p-6 animate__animated animate__fadeIn">

                    {{-- Polish: header مدمج + ضوء — أيقونة أصغر، لا ring-4، لا shadow-lg --}}
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-[#1FA7A2]/10 flex items-center justify-center text-[#1FA7A2] shrink-0">
                                <i class="fas fa-headset"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-black text-[#0A2540]">{{ __('support_center_title') }}</h2>
                                <p class="text-xs text-slate-500 font-medium mt-0.5 flex flex-wrap items-center gap-2">
                                    <span>{{ __('open_tickets') }}: <span class="font-bold text-amber-500">{{ $this->openCount ?? 0 }}</span></span>
                                    <span class="text-slate-300">•</span>
                                    <span>{{ __('closed_tickets') ?: 'تذاكر مغلقة' }}: <span class="font-bold text-slate-500">{{ $this->closedCount ?? 0 }}</span></span>
                                </p>
                            </div>
                        </div>
                        <button type="button"
                                x-data x-on:click="$dispatch('open-modal', 'create-ticket')"
                                class="px-4 py-2.5 rounded-xl bg-[#0A2540] text-white text-sm font-bold hover:bg-[#0a2540]/90 transition-all flex items-center justify-center gap-2">
                            <i class="fas fa-plus text-xs"></i> {{ __('create_ticket') }}
                        </button>
                    </div>

                    {{-- Polish: inbox layout بـ min-h فقط (لا h ثابت 500px) --}}
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 min-h-[560px] lg:h-[calc(100vh-340px)] lg:max-h-[700px]">
                        <div class="lg:col-span-4 flex flex-col bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                            {{-- search + status filter --}}
                            <div class="p-4 border-b border-slate-100 bg-slate-50/60 space-y-3">
                                <div class="relative">
                                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="{{ __('search_ticket_placeholder') }}" class="w-full bg-white border border-slate-200 rounded-xl rtl:pl-4 rtl:pr-10 ltr:pr-4 ltr:pl-10 py-2 text-sm font-medium focus:border-[#1FA7A2] focus:ring-2 focus:ring-[#1FA7A2]/10 outline-none transition-all">
                                    <div class="absolute rtl:right-3 ltr:left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                                        <i class="fas fa-search text-xs"></i>
                                    </div>
                                </div>
                                {{-- Polish: status filter pills --}}
                                <div class="flex flex-wrap gap-1.5">
                                    @php
                                        $statusOptions = [
                                            'all'              => __('filter_all'),
                                            'open'             => $this->translateStatus('open'),
                                            'pending_agent'    => __('status_pending_agent') ?: 'بانتظار الموظف',
                                            'pending_customer' => __('status_pending_customer') ?: 'بانتظار العميل',
                                            'closed'           => $this->translateStatus('closed'),
                                        ];
                                    @endphp
                                    @foreach($statusOptions as $key => $label)
                                        <button type="button"
                                                wire:click="$set('ticketStatusFilter', '{{ $key }}')"
                                                class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition-all {{ $ticketStatusFilter === $key ? 'bg-[#0A2540] text-white' : 'bg-white text-slate-500 border border-slate-200 hover:border-[#0A2540]/30' }}">
                                            {{ $label }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <div class="flex-1 overflow-y-auto p-2 space-y-1.5 custom-scrollbar">
                                @forelse($this->tickets as $t)
                                    @php
                                        $isActive = $selectedTicket && $selectedTicket->id === $t->id;
                                        $lastActivity = $t->last_reply_at ?? $t->updated_at ?? $t->created_at;
                                        // snippet من آخر رد إن كان loaded — وإلا من الوصف
                                        $snippet = mb_substr((string) ($t->description ?? ''), 0, 60);
                                    @endphp
                                    {{-- Phase 8B: wire:key على كل بطاقة تذكرة — يضمن DOM diffing مستقر عند filter/search --}}
                                    <button type="button"
                                            wire:key="ticket-card-{{ $t->id }}"
                                            wire:click="openTicket({{ $t->id }})"
                                            class="w-full text-start p-3 rounded-xl border transition-all duration-200 group
                                            {{ $isActive ? 'bg-[#0A2540]/5 border-[#0A2540]/30 shadow-sm' : 'bg-white border-slate-100 hover:bg-slate-50 hover:border-slate-200' }}">

                                        <div class="flex justify-between items-start gap-2 mb-1">
                                            <span class="font-mono text-[10px] font-bold {{ $isActive ? 'text-[#0A2540]' : 'text-slate-400' }}">#{{ $t->ticket_number ?? $t->id }}</span>
                                            {{-- Phase 6: badges soft palette — design system unification --}}
                                            <div class="flex gap-1">
                                                @if($t->priority)
                                                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-md bg-slate-50 text-slate-600 border border-slate-100">
                                                        {{ __('priority_' . $t->priority) ?: $t->priority }}
                                                    </span>
                                                @endif
                                                <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-md border {{ ($t->status ?? '') === 'open' ? 'bg-amber-50 text-amber-700 border-amber-100' : 'bg-slate-50 text-slate-600 border-slate-100' }}">
                                                    {{ $this->translateStatus($t->status) }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="font-bold text-slate-800 text-sm mb-1 truncate">
                                            {{ $t->subject }}
                                        </div>

                                        @if($snippet)
                                            <p class="text-[11px] text-slate-500 font-medium truncate mb-1">{{ $snippet }}{{ mb_strlen((string) ($t->description ?? '')) > 60 ? '…' : '' }}</p>
                                        @endif

                                        <div class="flex items-center gap-1 text-[10px] text-slate-400 font-medium">
                                            <i class="far fa-clock text-slate-300"></i>
                                            <span class="truncate" title="{{ optional($lastActivity)->format('Y-m-d H:i') }}">
                                                @if($t->last_reply_at)
                                                    {{ __('last_reply') ?: 'آخر رد' }} {{ $t->last_reply_at->diffForHumans() }}
                                                @else
                                                    {{ optional($lastActivity)->diffForHumans() }}
                                                @endif
                                            </span>
                                        </div>
                                    </button>
                                @empty
                                    {{-- Phase 6: tighter empty state padding --}}
                                    <div class="flex flex-col items-center justify-center py-8 px-4 text-center">
                                        @if(filled($search))
                                            <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 mb-2">
                                                <i class="fas fa-search"></i>
                                            </div>
                                            <p class="text-xs font-black text-slate-600 mb-0.5">{{ __('no_search_results') ?: 'لا توجد نتائج' }}</p>
                                            <p class="text-[11px] text-slate-400 font-medium">{{ __('try_different_search') ?: 'جرّب عبارات أخرى.' }}</p>
                                        @else
                                            <div class="w-10 h-10 rounded-xl bg-[#1FA7A2]/5 flex items-center justify-center text-[#1FA7A2] mb-2">
                                                <i class="far fa-folder-open"></i>
                                            </div>
                                            <p class="text-xs font-black text-slate-700 mb-0.5">{{ __('support_empty_title') }}</p>
                                            <p class="text-[11px] text-slate-500 font-medium">{{ __('support_empty_hint') }}</p>
                                        @endif
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <div class="lg:col-span-8 h-full relative">
                            <div wire:loading.flex wire:target="openTicket" class="absolute inset-0 z-20 bg-white/80 backdrop-blur-sm rounded-2xl flex items-center justify-center">
                                <div class="flex flex-col items-center gap-3">
                                    <i class="fas fa-circle-notch fa-spin text-2xl text-[#1FA7A2]"></i>
                                    <span class="text-sm font-bold text-slate-600">{{ __('loading_ticket') }}</span>
                                </div>
                            </div>

                            @if($selectedTicket)
                                <div class="flex flex-col h-full bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden"
                                     x-data="{ scrollBottom() { if (this.$refs.chatContainer) { this.$refs.chatContainer.scrollTo({ top: this.$refs.chatContainer.scrollHeight, behavior: 'smooth' }); } } }"
                                     x-init="scrollBottom(); $wire.on('ticket-updated', () => setTimeout(() => scrollBottom(), 100))">

                                    {{-- Polish: header التذكرة أنظف — حالة وأولوية معًا --}}
                                    <div class="px-5 py-3 border-b border-slate-100 bg-white flex justify-between items-start gap-3">
                                        <div class="min-w-0 flex-1">
                                            <h3 class="text-base font-black text-[#0A2540] mb-0.5 truncate">
                                                {{ $selectedTicket->subject }}
                                            </h3>
                                            <div class="flex items-center flex-wrap gap-x-3 gap-y-1 text-[11px] font-bold text-slate-500">
                                                <span class="font-mono text-slate-400">#{{ $selectedTicket->ticket_number ?? $selectedTicket->id }}</span>
                                                @if($this->activeCompany?->name)
                                                    <span class="flex items-center gap-1">
                                                        <i class="fas fa-building text-slate-300 text-[10px]"></i>
                                                        {{ $this->activeCompany->name }}
                                                    </span>
                                                @endif
                                                @if($selectedTicket->last_reply_at ?? $selectedTicket->updated_at)
                                                    <span class="flex items-center gap-1">
                                                        <i class="far fa-clock text-slate-300 text-[10px]"></i>
                                                        {{ __('last_update') }}: {{ ($selectedTicket->last_reply_at ?? $selectedTicket->updated_at)->diffForHumans() }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex gap-1.5 shrink-0">
                                            @if($selectedTicket->priority)
                                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-slate-50 border border-slate-100 text-slate-600">
                                                    {{ __('priority_' . $selectedTicket->priority) ?: $selectedTicket->priority }}
                                                </span>
                                            @endif
                                            @if($selectedTicket->status)
                                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold border {{ $selectedTicket->status === 'open' ? 'bg-amber-50 text-amber-700 border-amber-100' : 'bg-slate-50 text-slate-600 border-slate-100' }}">
                                                    {{ $this->translateStatus($selectedTicket->status) }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div x-ref="chatContainer" class="flex-1 overflow-y-auto p-6 space-y-6 bg-[#f8fafc] custom-scrollbar scroll-smooth">
                                        <div class="flex justify-start w-full">
                                            <div class="max-w-[85%] bg-white border border-slate-200/60 p-5 rounded-2xl rtl:rounded-tr-none ltr:rounded-tl-none shadow-sm">
                                                <div class="flex items-center gap-2 mb-2">
                                                    <span class="text-xs font-bold text-[#1FA7A2]">{{ $selectedTicket->user?->name ?? __('customer_label') }}</span>
                                                    <span class="text-[10px] text-slate-400">{{ $selectedTicket->created_at->format('Y-m-d H:i') }}</span>
                                                </div>
                                                <div class="text-sm text-slate-700 leading-relaxed whitespace-pre-line">
                                                    {{ $selectedTicket->description }}
                                                </div>
                                                @if($selectedTicket->attachments && $selectedTicket->attachments->count())
                                                    <div class="mt-3 pt-3 border-t border-slate-100 space-y-1">
                                                        @foreach($selectedTicket->attachments as $att)
                                                            <a href="{{ $att->url }}" target="_blank" rel="noopener"
                                                               class="flex items-center gap-2 text-xs px-3 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 transition-colors">
                                                                <i class="fas fa-paperclip text-slate-400"></i>
                                                                <span class="truncate max-w-[220px]">{{ $att->original_name ?: 'مرفق' }}</span>
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        @foreach($selectedTicket->replies as $r)
                                            @php
                                                $isStaff = $r->user?->hasBackofficeAccess() ?? false;
                                                $isOwn = ! $isStaff && $r->user_id == auth()->id();
                                                $senderName = $isStaff
                                                    ? 'فريق شركة آمر سبعة لحلول الأعمال'
                                                    : ($isOwn ? 'أنت' : ($r->user?->name ?? 'العميل'));
                                            @endphp
                                            {{-- Phase 8B: wire:key على كل bubble — يحافظ على state الـscroll/animations عند replies جديدة --}}
                                            <div wire:key="ticket-reply-{{ $r->id }}" class="flex w-full {{ $isStaff ? 'justify-end' : 'justify-start' }}">
                                                <div class="max-w-[85%] group">
                                                    <div class="flex items-center gap-2 mb-1 px-1 {{ $isStaff ? 'flex-row-reverse' : '' }}">
                                                        <span class="text-[10px] font-bold {{ $isStaff ? 'text-[#1FA7A2]' : ($isOwn ? 'text-[#1FA7A2]' : 'text-slate-600') }}">
                                                            {{ $senderName }}
                                                        </span>
                                                        @if($isStaff)
                                                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-md bg-[#1FA7A2]/10 text-[#1FA7A2] tracking-wider flex items-center gap-1">
                                                                <i class="fas fa-headset text-[8px]"></i> دعم
                                                            </span>
                                                        @endif
                                                        <span class="text-[10px] text-slate-300 font-mono">{{ $r->created_at->format('Y-m-d H:i') }}</span>
                                                    </div>
                                                    <div class="p-4 rounded-2xl text-sm leading-relaxed whitespace-pre-line shadow-sm relative transition-all {{ $isStaff ? 'bg-[#1FA7A2] text-white rtl:rounded-tl-none ltr:rounded-tr-none shadow-md shadow-[#1FA7A2]/10' : 'bg-white border border-slate-200 text-slate-700 rtl:rounded-tr-none ltr:rounded-tl-none' }} {{ $isOwn ? 'ring-1 ring-[#1FA7A2]/15' : '' }}">
                                                        {{ $r->message }}
                                                    </div>
                                                    @if($r->attachments && $r->attachments->count())
                                                        <div class="mt-2 space-y-1">
                                                            @foreach($r->attachments as $att)
                                                                <a href="{{ $att->url }}" target="_blank" rel="noopener"
                                                                   class="flex items-center gap-2 text-xs px-3 py-2 rounded-lg transition-colors {{ $isStaff ? 'bg-white/10 hover:bg-white/20 text-white border border-white/20' : 'bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200' }}">
                                                                    <i class="fas fa-paperclip {{ $isStaff ? 'text-white/70' : 'text-slate-400' }}"></i>
                                                                    <span class="truncate max-w-[200px]">{{ $att->original_name ?: 'مرفق' }}</span>
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                        <div class="h-4"></div>
                                    </div>

                                    {{-- Phase 8B: Reply form — wire:loading.* مقصور على sendReply فقط
                                         (لا تتأثر بـsearch/filter/openTicket). validation عربية تحت الـtextarea. --}}
                                    <div class="p-4 bg-white border-t border-slate-100 z-10">
                                        <form wire:submit="sendReply" class="relative">
                                            <div class="flex gap-3 items-end bg-slate-50 p-2 rounded-2xl border border-slate-200 focus-within:border-[#1FA7A2] focus-within:ring-2 focus-within:ring-[#1FA7A2]/10 transition-all">
                                                <textarea wire:model="replyMessage" rows="1"
                                                          x-data
                                                          x-init="$nextTick(() => { $el.style.height = 'auto'; $el.style.height = Math.min($el.scrollHeight, 160) + 'px'; })"
                                                          x-on:input="$el.style.height = 'auto'; $el.style.height = Math.min($el.scrollHeight, 160) + 'px'"
                                                          x-on:reset.window="$el.style.height = 'auto'"
                                                          class="w-full bg-transparent border-none focus:ring-0 p-3 text-slate-800 font-medium placeholder-slate-400 resize-none max-h-40 custom-scrollbar overflow-y-auto" placeholder="{{ __('write_reply_placeholder') }}"></textarea>
                                                <button type="submit"
                                                        wire:loading.attr="disabled" wire:target="sendReply"
                                                        class="p-3 bg-[#1FA7A2] text-white rounded-xl shadow-lg shadow-[#1FA7A2]/20 hover:shadow-[#1FA7A2]/40 hover:scale-105 transition-all disabled:opacity-50 disabled:cursor-not-allowed shrink-0">
                                                    <i class="fas fa-paper-plane rtl:rotate-180" wire:loading.remove wire:target="sendReply"></i>
                                                    <i class="fas fa-circle-notch fa-spin" wire:loading wire:target="sendReply"></i>
                                                </button>
                                            </div>
                                            @error('replyMessage')
                                                <p class="mt-1.5 text-[11px] text-rose-500 font-bold flex items-center gap-1">
                                                    <i class="fas fa-circle-exclamation text-[10px]"></i>
                                                    <span>{{ $message }}</span>
                                                </p>
                                            @enderror
                                            @error('replyAttachments.*')
                                                <p class="mt-1.5 text-[11px] text-rose-500 font-bold flex items-center gap-1">
                                                    <i class="fas fa-circle-exclamation text-[10px]"></i>
                                                    <span>{{ $message }}</span>
                                                </p>
                                            @enderror
                                        </form>
                                    </div>
                                </div>
                            @else
                                {{-- Polish: empty state عند عدم اختيار تذكرة — هادئ، لا pulse مزعج --}}
                                <div class="h-full flex flex-col items-center justify-center bg-white rounded-2xl border border-slate-100 shadow-sm text-center px-6">
                                    <div class="w-16 h-16 rounded-2xl bg-[#0A2540]/5 flex items-center justify-center text-[#0A2540] mb-4">
                                        <i class="far fa-comments text-2xl"></i>
                                    </div>
                                    <h3 class="font-black text-base text-slate-700 mb-1">{{ __('select_ticket_title') }}</h3>
                                    <p class="text-xs font-medium text-slate-500 max-w-xs">{{ __('select_ticket_hint') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- Phase E: AI Review Section — يُحمَّل فقط عند فتح التبويب + مرئي لـ backoffice فقط --}}
            @if($section === 'ai-review' && auth()->user() && method_exists(auth()->user(), 'hasBackofficeAccess') && auth()->user()->hasBackofficeAccess())
                <livewire:dashboard.ai-review-panel :key="'ai-review-panel-' . $activeCompanyId" />
            @endif

        </div>
    </div>

    {{-- Modals Zone using Alpine & Livewire --}}

    {{-- 1. Edit Company Modal — Polish: unified style --}}
    <div x-data="{ open: false }"
         x-on:open-modal.window="if($event.detail === 'edit-company') open = true"
         x-on:close-modal.window="if($event.detail === 'edit-company') open = false"
         x-on:keydown.escape.window="if(open) open = false"
         x-cloak class="relative z-[9999]">
        <div x-show="open" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" x-transition.opacity x-on:click="open = false"></div>
        <div x-show="open" class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="open" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100" class="relative w-full max-w-2xl bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden">
                    {{-- Header موحَّد: px-6 py-4 + Navy title --}}
                    <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-start">
                        <div>
                            <h3 class="text-lg font-black text-[#0A2540]">{{ __('edit_company_info') }}</h3>
                            <p class="text-xs text-slate-500 font-medium mt-0.5">{{ __('edit_company_hint') ?: 'حدّث البيانات الأساسية لملف منشأتك.' }}</p>
                        </div>
                        <button type="button" x-on:click="open = false" class="w-8 h-8 rounded-lg text-slate-400 hover:text-rose-500 hover:bg-slate-50 flex items-center justify-center"><i class="fas fa-times text-sm"></i></button>
                    </div>

                    {{-- Body p-6 — Polish: قسم "البيانات الأساسية" واضح + hint للحقول الأخرى --}}
                    <form wire:submit="saveCompanyInfo">
                        <div class="p-6 max-h-[60vh] overflow-y-auto custom-scrollbar space-y-5">
                            {{-- Section: البيانات الأساسية --}}
                            <div>
                                <div class="flex items-center gap-2 mb-3 pb-2 border-b border-slate-100">
                                    <div class="w-7 h-7 rounded-lg bg-[#1FA7A2]/10 flex items-center justify-center text-[#1FA7A2]">
                                        <i class="fas fa-id-card text-xs"></i>
                                    </div>
                                    <h4 class="text-xs font-black text-[#0A2540]">{{ __('basic_information') }}</h4>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="md:col-span-2 space-y-1">
                                        <label class="text-xs font-bold text-slate-600">{{ __('label_company_name') }}</label>
                                        <input type="text" wire:model="name" class="w-full bg-slate-50 border border-slate-200 focus:border-[#0A2540] focus:ring-2 focus:ring-[#0A2540]/10 rounded-xl px-4 py-2.5 font-medium text-sm outline-none transition-all">
                                        @error('name') <span class="text-[11px] text-rose-500 font-bold block">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-xs font-bold text-slate-600">{{ __('label_unified_number') }}</label>
                                        <input type="text" wire:model="unified_number" class="w-full bg-slate-50 border border-slate-200 focus:border-[#0A2540] focus:ring-2 focus:ring-[#0A2540]/10 rounded-xl px-4 py-2.5 font-medium font-mono text-sm outline-none transition-all">
                                        @error('unified_number') <span class="text-[11px] text-rose-500 font-bold block">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-xs font-bold text-slate-600">{{ __('label_tax_number') }}</label>
                                        <input type="text" wire:model="tax_number" class="w-full bg-slate-50 border border-slate-200 focus:border-[#0A2540] focus:ring-2 focus:ring-[#0A2540]/10 rounded-xl px-4 py-2.5 font-medium font-mono text-sm outline-none transition-all">
                                        @error('tax_number') <span class="text-[11px] text-rose-500 font-bold block">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-xs font-bold text-slate-600">{{ __('label_city') }}</label>
                                        <input type="text" wire:model="city" class="w-full bg-slate-50 border border-slate-200 focus:border-[#0A2540] focus:ring-2 focus:ring-[#0A2540]/10 rounded-xl px-4 py-2.5 font-medium text-sm outline-none transition-all">
                                        @error('city') <span class="text-[11px] text-rose-500 font-bold block">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="md:col-span-2 space-y-1">
                                        <label class="text-xs font-bold text-slate-600">{{ __('label_national_address') }}</label>
                                        <input type="text" wire:model="address" class="w-full bg-slate-50 border border-slate-200 focus:border-[#0A2540] focus:ring-2 focus:ring-[#0A2540]/10 rounded-xl px-4 py-2.5 font-medium text-sm outline-none transition-all">
                                        @error('address') <span class="text-[11px] text-rose-500 font-bold block">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Info: حقول التأمينات/التأمين الطبي يديرها فريق الدعم --}}
                            <div class="bg-slate-50 border border-slate-100 rounded-xl p-3 flex gap-2 text-[11px] text-slate-500 font-medium">
                                <i class="fas fa-circle-info text-[#1FA7A2] mt-0.5 shrink-0"></i>
                                <span>{{ __('edit_company_other_sections_hint') ?: 'بيانات التأمينات والتأمين الطبي تتم إدارتها من فريق الدعم. تواصل معنا عبر تذكرة جديدة عند الحاجة.' }}</span>
                            </div>
                        </div>

                        {{-- Footer موحَّد --}}
                        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex justify-end gap-2">
                            <button type="button" x-on:click="open = false" class="px-5 py-2.5 rounded-xl text-slate-600 font-bold text-sm hover:bg-slate-100 border border-slate-200">{{ __('modal_cancel') }}</button>
                            <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#0A2540] text-white font-bold text-sm hover:bg-[#0a2540]/90 disabled:opacity-60 disabled:cursor-not-allowed" wire:loading.attr="disabled" wire:target="saveCompanyInfo">
                                <span wire:loading.remove wire:target="saveCompanyInfo">{{ __('modal_save') }}</span>
                                <span wire:loading wire:target="saveCompanyInfo"><i class="fas fa-circle-notch fa-spin"></i></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. Add User Modal — Polish: unified style --}}
    <div x-data="{ open: false }"
         x-on:open-modal.window="if($event.detail === 'add-user') open = true"
         x-on:close-modal.window="if($event.detail === 'add-user') open = false"
         x-on:keydown.escape.window="if(open) open = false"
         x-cloak class="relative z-[9999]">
        <div x-show="open" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" x-transition.opacity x-on:click="open = false"></div>
        <div x-show="open" class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="open" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100" class="relative w-full max-w-lg bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-start">
                        <div>
                            <h3 class="text-lg font-black text-[#0A2540]">{{ __('btn_add_user') }}</h3>
                            <p class="text-xs text-slate-500 font-medium mt-0.5">{{ __('add_user_hint') ?: 'أضف مستخدمًا مسجلًا مسبقًا للوصول إلى لوحة منشأتك.' }}</p>
                        </div>
                        <button type="button" x-on:click="open = false" class="w-8 h-8 rounded-lg text-slate-400 hover:text-rose-500 hover:bg-slate-50 flex items-center justify-center"><i class="fas fa-times text-sm"></i></button>
                    </div>

                    <form wire:submit="addUserToCompany">
                        <div class="p-6 space-y-4">
                            <div class="space-y-1">
                                <label class="text-xs font-bold text-slate-600">{{ __('label_email') }} <span class="text-rose-500">*</span></label>
                                <input type="email" wire:model="newUserEmail" class="w-full bg-slate-50 border border-slate-200 focus:border-[#0A2540] focus:ring-2 focus:ring-[#0A2540]/10 rounded-xl px-4 py-2.5 font-medium text-sm outline-none transition-all" required>
                                @error('newUserEmail') <span class="text-[11px] text-rose-500 font-bold block">{{ $message }}</span> @enderror
                            </div>
                            <div class="space-y-1">
                                <label class="text-xs font-bold text-slate-600">{{ __('table_role') }}</label>
                                <select wire:model="newUserRole" class="w-full bg-slate-50 border border-slate-200 focus:border-[#0A2540] focus:ring-2 focus:ring-[#0A2540]/10 rounded-xl px-4 py-2.5 font-medium text-sm outline-none transition-all">
                                    <option value="employee">{{ __('role_user') }}</option>
                                    <option value="admin">{{ __('role_admin') }}</option>
                                </select>
                                @error('newUserRole') <span class="text-[11px] text-rose-500 font-bold block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex justify-end gap-2">
                            <button type="button" x-on:click="open = false" class="px-5 py-2.5 rounded-xl text-slate-600 font-bold text-sm hover:bg-slate-100 border border-slate-200">{{ __('modal_cancel') }}</button>
                            <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#0A2540] text-white font-bold text-sm hover:bg-[#0a2540]/90 disabled:opacity-60 disabled:cursor-not-allowed" wire:loading.attr="disabled" wire:target="addUserToCompany">
                                <span wire:loading.remove wire:target="addUserToCompany">{{ __('add_user_action') ?: 'إضافة المستخدم' }}</span>
                                <span wire:loading wire:target="addUserToCompany"><i class="fas fa-circle-notch fa-spin"></i></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Phase A: Invite Member Modal — opens an invitation flow that
         supports unregistered emails. Plain token is surfaced once via
         $lastInvitationLink which renders in the users section. --}}
    <div x-data="{ open: false }"
         x-on:open-modal.window="if($event.detail === 'invite-member') open = true"
         x-on:close-modal.window="if($event.detail === 'invite-member') open = false"
         x-on:keydown.escape.window="if(open) open = false"
         x-on:notify.window="if(open && ($event.detail?.[0]?.type === 'success' || $event.detail?.type === 'success')) open = false"
         x-cloak class="relative z-[9999]">
        <div x-show="open" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" x-transition.opacity x-on:click="open = false"></div>
        <div x-show="open" class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="open" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100" class="relative w-full max-w-lg bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-start">
                        <div>
                            <h3 class="text-lg font-black text-[#0A2540]">
                                {{ __('invite_member_action') === 'invite_member_action' ? 'دعوة موظف' : __('invite_member_action') }}
                            </h3>
                            <p class="text-xs text-slate-500 font-medium mt-0.5">
                                {{ __('invite_member_hint') === 'invite_member_hint' ? 'سيتم إنشاء رابط دعوة. انسخه وأرسله للموظف بأي وسيلة. ليس عليه أن يكون مسجلاً مسبقًا.' : __('invite_member_hint') }}
                            </p>
                        </div>
                        <button type="button" x-on:click="open = false" class="w-8 h-8 rounded-lg text-slate-400 hover:text-rose-500 hover:bg-slate-50 flex items-center justify-center"><i class="fas fa-times text-sm"></i></button>
                    </div>

                    <form wire:submit="sendCompanyInvitation">
                        <div class="p-6 space-y-4">
                            <div class="space-y-1">
                                <label class="text-xs font-bold text-slate-600">{{ __('label_email') }} <span class="text-rose-500">*</span></label>
                                <input type="email" wire:model="inviteEmail" dir="ltr"
                                       class="w-full bg-slate-50 border border-slate-200 focus:border-[#0A2540] focus:ring-2 focus:ring-[#0A2540]/10 rounded-xl px-4 py-2.5 font-medium text-sm outline-none transition-all" required>
                                @error('inviteEmail') <span class="text-[11px] text-rose-500 font-bold block">{{ $message }}</span> @enderror
                            </div>
                            <div class="space-y-1">
                                <label class="text-xs font-bold text-slate-600">{{ __('table_role') }}</label>
                                <select wire:model="inviteRole" class="w-full bg-slate-50 border border-slate-200 focus:border-[#0A2540] focus:ring-2 focus:ring-[#0A2540]/10 rounded-xl px-4 py-2.5 font-medium text-sm outline-none transition-all">
                                    <option value="employee">{{ __('role_user') }}</option>
                                    <option value="admin">{{ __('role_admin') }}</option>
                                </select>
                                @error('inviteRole') <span class="text-[11px] text-rose-500 font-bold block">{{ $message }}</span> @enderror
                            </div>
                            <div class="rounded-xl bg-slate-50 border border-slate-100 px-3 py-2.5 flex items-start gap-2 text-[11px] text-slate-600 font-medium">
                                <i class="fas fa-info-circle text-slate-400 mt-0.5"></i>
                                <span>{{ __('invite_member_notice') === 'invite_member_notice' ? 'البريد الإلكتروني هو وسيلة التحقق. لن نرسل بريدًا تلقائيًا الآن — ستحصل على رابط للنسخ بعد الإنشاء.' : __('invite_member_notice') }}</span>
                            </div>
                        </div>

                        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex justify-end gap-2">
                            <button type="button" x-on:click="open = false" class="px-5 py-2.5 rounded-xl text-slate-600 font-bold text-sm hover:bg-slate-100 border border-slate-200">{{ __('modal_cancel') }}</button>
                            <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#0A2540] text-white font-bold text-sm hover:bg-[#0a2540]/90 disabled:opacity-60 disabled:cursor-not-allowed" wire:loading.attr="disabled" wire:target="sendCompanyInvitation">
                                <span wire:loading.remove wire:target="sendCompanyInvitation">
                                    {{ __('send_invitation') === 'send_invitation' ? 'إنشاء رابط الدعوة' : __('send_invitation') }}
                                </span>
                                <span wire:loading wire:target="sendCompanyInvitation"><i class="fas fa-circle-notch fa-spin"></i></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Phase B (UI): Permissions Manager Modal — Matrix editor for one
         non-admin employee at a time. Bound to $permissionsMatrix in the
         Livewire component; admin/owner rows are intentionally rejected
         by openPermissionsManager() so this modal is never opened for
         them. --}}
    <div x-data="{ open: false }"
         x-on:open-modal.window="if($event.detail === 'permissions-manager') open = true"
         x-on:close-modal.window="if($event.detail === 'permissions-manager') open = false"
         x-on:keydown.escape.window="if(open) open = false"
         x-cloak class="relative z-[9999]">
        <div x-show="open" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" x-transition.opacity x-on:click="open = false"></div>
        <div x-show="open" class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="open" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100" class="relative w-full max-w-2xl bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden">
                    @php
                        $permTarget = $this->permissionsTargetUser;
                        $permGroups = \App\Support\CompanyPermissions::GROUPS;
                        $permActions = \App\Support\CompanyPermissions::ACTIONS;
                        $groupLabels = [
                            'dashboard'    => __('perm_group_dashboard') === 'perm_group_dashboard' ? 'الرئيسية' : __('perm_group_dashboard'),
                            'services'     => __('perm_group_services')  === 'perm_group_services'  ? 'الخدمات'   : __('perm_group_services'),
                            'requests'     => __('perm_group_requests')  === 'perm_group_requests'  ? 'الطلبات'   : __('perm_group_requests'),
                            'documents'    => __('perm_group_documents') === 'perm_group_documents' ? 'الوثائق'   : __('perm_group_documents'),
                            'invoices'     => __('perm_group_invoices')  === 'perm_group_invoices'  ? 'الفواتير'  : __('perm_group_invoices'),
                            'tickets'      => __('perm_group_tickets')   === 'perm_group_tickets'   ? 'التذاكر'   : __('perm_group_tickets'),
                            'users'        => __('perm_group_users')     === 'perm_group_users'     ? 'المستخدمون' : __('perm_group_users'),
                            'financial'    => __('perm_group_financial') === 'perm_group_financial' ? 'القوائم المالية' : __('perm_group_financial'),
                            'profile'      => __('perm_group_profile')   === 'perm_group_profile'   ? 'ملف المنشأة' : __('perm_group_profile'),
                            'packages'     => __('perm_group_packages')  === 'perm_group_packages'  ? 'الباقات'   : __('perm_group_packages'),
                            'support'      => __('perm_group_support')   === 'perm_group_support'   ? 'الدعم'     : __('perm_group_support'),
                            'subscription' => __('perm_group_subscription') === 'perm_group_subscription' ? 'الاشتراك' : __('perm_group_subscription'),
                        ];
                        $actionLabels = [
                            'view'   => __('perm_action_view')   === 'perm_action_view'   ? 'عرض'   : __('perm_action_view'),
                            'create' => __('perm_action_create') === 'perm_action_create' ? 'إنشاء' : __('perm_action_create'),
                            'update' => __('perm_action_update') === 'perm_action_update' ? 'تعديل' : __('perm_action_update'),
                            'delete' => __('perm_action_delete') === 'perm_action_delete' ? 'حذف'   : __('perm_action_delete'),
                        ];
                    @endphp

                    <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-start">
                        <div>
                            <h3 class="text-lg font-black text-[#0A2540]">
                                {{ __('manage_permissions_title') === 'manage_permissions_title' ? 'إدارة صلاحيات الموظف' : __('manage_permissions_title') }}
                            </h3>
                            @if($permTarget)
                                <p class="text-xs text-slate-500 font-medium mt-0.5">
                                    {{ $permTarget->name ?? $permTarget->email }} ·
                                    <span dir="ltr" class="font-mono">{{ $permTarget->email }}</span>
                                </p>
                            @endif
                        </div>
                        <button type="button" wire:click="closePermissionsManager" class="w-8 h-8 rounded-lg text-slate-400 hover:text-rose-500 hover:bg-slate-50 flex items-center justify-center"><i class="fas fa-times text-sm"></i></button>
                    </div>

                    <div class="p-6 space-y-4 max-h-[60vh] overflow-y-auto">
                        <p class="text-[11px] text-slate-500 font-medium">
                            {{ __('manage_permissions_hint') === 'manage_permissions_hint' ? 'حدّد الصلاحيات لكل مجموعة. صلاحيات مسؤولي المنشأة كاملة دائمًا ولا تظهر هنا.' : __('manage_permissions_hint') }}
                        </p>

                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="text-[10px] font-black text-slate-500 uppercase tracking-widest border-b border-slate-100">
                                        <th class="p-2 text-start min-w-[140px]">{{ __('perm_group_label') === 'perm_group_label' ? 'المجموعة' : __('perm_group_label') }}</th>
                                        @foreach($permActions as $a)
                                            <th class="p-2 text-center">{{ $actionLabels[$a] ?? $a }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    @foreach($permGroups as $g)
                                        <tr class="hover:bg-slate-50/40">
                                            <td class="p-2 font-bold text-slate-700">{{ $groupLabels[$g] ?? $g }}</td>
                                            @foreach($permActions as $a)
                                                @php
                                                    $checked = is_array($permissionsMatrix[$g] ?? null)
                                                        && in_array($a, $permissionsMatrix[$g], true);
                                                @endphp
                                                <td class="p-2 text-center">
                                                    <button type="button"
                                                            wire:click="togglePermission('{{ $g }}', '{{ $a }}')"
                                                            class="inline-flex items-center justify-center w-7 h-7 rounded-lg border-2 transition-all
                                                                   {{ $checked
                                                                        ? 'bg-[#0A2540] border-[#0A2540] text-white'
                                                                        : 'bg-white border-slate-200 text-slate-300 hover:border-[#0A2540]/40' }}"
                                                            :aria-pressed="{{ $checked ? 'true' : 'false' }}">
                                                        @if($checked)
                                                            <i class="fas fa-check text-[10px]"></i>
                                                        @endif
                                                    </button>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="rounded-xl bg-slate-50 border border-slate-100 px-3 py-2 flex items-start gap-2 text-[11px] text-slate-600 font-medium">
                            <i class="fas fa-info-circle text-slate-400 mt-0.5"></i>
                            <span>{{ __('permissions_save_note') === 'permissions_save_note' ? 'لا يتم حفظ الصلاحيات إلا بالضغط على زر الحفظ. لإلغاء التخصيص اضغط "إعادة إلى الافتراضي".' : __('permissions_save_note') }}</span>
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex flex-wrap justify-end gap-2">
                        <button type="button" wire:click="resetUserPermissionsToDefault"
                                wire:confirm="{{ __('confirm_reset_permissions') === 'confirm_reset_permissions' ? 'سيتم إعادة الصلاحيات إلى الافتراضي. متابعة؟' : __('confirm_reset_permissions') }}"
                                class="px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-sm font-bold">
                            <i class="fas fa-rotate-left text-[10px] me-1.5"></i>
                            {{ __('reset_to_default') === 'reset_to_default' ? 'إعادة إلى الافتراضي' : __('reset_to_default') }}
                        </button>
                        <button type="button" wire:click="closePermissionsManager"
                                class="px-5 py-2.5 rounded-xl text-slate-600 font-bold text-sm hover:bg-slate-100 border border-slate-200">
                            {{ __('modal_cancel') }}
                        </button>
                        <button type="button" wire:click="saveUserPermissions"
                                class="px-6 py-2.5 rounded-xl bg-[#0A2540] text-white font-bold text-sm hover:bg-[#0a2540]/90 disabled:opacity-60 disabled:cursor-not-allowed"
                                wire:loading.attr="disabled" wire:target="saveUserPermissions">
                            <span wire:loading.remove wire:target="saveUserPermissions">
                                {{ __('save_permissions') === 'save_permissions' ? 'حفظ الصلاحيات' : __('save_permissions') }}
                            </span>
                            <span wire:loading wire:target="saveUserPermissions"><i class="fas fa-circle-notch fa-spin"></i></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. Add Compliance Doc Modal — Polish: unified style + safe upload state --}}
    <div x-data="{ open: false, uploading: false, progress: 0, uploadError: '' }"
         x-on:open-modal.window="if($event.detail === 'add-doc') { open = true; uploadError = ''; progress = 0; uploading = false }"
         x-on:close-modal.window="if($event.detail === 'add-doc' && !uploading) open = false"
         x-on:compliance-document-created.window="open = false; uploading = false; progress = 0; uploadError = ''"
         x-on:livewire-upload-start.window="uploading = true; progress = 0; uploadError = ''"
         x-on:livewire-upload-progress.window="progress = ($event.detail?.progress ?? 0)"
         x-on:livewire-upload-finish.window="uploading = false; progress = 100"
         x-on:livewire-upload-cancel.window="uploading = false; progress = 0"
         x-on:livewire-upload-error.window="uploading = false; progress = 0; uploadError = @js(__('upload_failed_hint') ?: 'تعذّر رفع الوثيقة. تحقق من حجم الملف أو نوعه ثم حاول مرة أخرى.')"
         x-on:keydown.escape.window="if(open && !uploading) open = false"
         wire:key="modal-add-doc"
         x-cloak class="relative z-[9999]">
        <div x-show="open" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" x-transition.opacity x-on:click="if(!uploading) open = false"></div>
        <div x-show="open" class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="open" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100" class="relative w-full max-w-lg bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-start">
                        <div>
                            <h3 class="text-lg font-black text-[#0A2540]">{{ __('upload_new_document') }}</h3>
                            <p class="text-xs text-slate-500 font-medium mt-0.5">PDF, JPG, PNG · {{ __('upload_size_hint', ['max' => 10]) }}</p>
                        </div>
                        <button type="button" x-on:click="if(!uploading) open = false" x-bind:disabled="uploading" class="w-8 h-8 rounded-lg text-slate-400 hover:text-rose-500 hover:bg-slate-50 flex items-center justify-center disabled:opacity-40 disabled:cursor-not-allowed"><i class="fas fa-times text-sm"></i></button>
                    </div>

                    <form wire:submit="saveComplianceDoc">
                        <div class="p-6 space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-slate-600">{{ __('document_type') }} <span class="text-rose-500">*</span></label>
                                    <select wire:model.live="docType" class="w-full bg-slate-50 border border-slate-200 focus:border-[#0A2540] focus:ring-2 focus:ring-[#0A2540]/10 rounded-xl px-4 py-2.5 font-medium text-sm outline-none transition-all">
                                        <option value="cr">{{ __('doc_type_commercial_register') ?: 'سجل تجاري' }}</option>
                                        <option value="articles_of_association">{{ __('doc_type_articles_of_association') ?: 'عقد تأسيس' }}</option>
                                        <option value="zakat">{{ __('doc_type_zakat_certificate') ?: 'شهادة زكاة' }}</option>
                                        <option value="tax">{{ __('doc_type_tax_certificate') ?: 'شهادة ضريبة' }}</option>
                                        <option value="gosi">{{ __('doc_type_gosi_certificate') ?: 'شهادة تأمينات' }}</option>
                                        <option value="medical_insurance">{{ __('doc_type_medical_insurance') ?: 'تأمين طبي' }}</option>
                                        <option value="municipal_license">{{ __('doc_type_municipal_license') ?: 'رخصة بلدية' }}</option>
                                        <option value="investment_license">{{ __('doc_type_investment_license') ?: 'رخصة استثمار' }}</option>
                                        <option value="power_of_attorney">{{ __('doc_type_power_of_attorney') ?: 'وكالة' }}</option>
                                        <option value="other">{{ __('doc_type_other') ?: 'أخرى' }}</option>
                                    </select>
                                    @error('docType') <span class="text-[11px] text-rose-500 font-bold block">{{ $message }}</span> @enderror
                                </div>
                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-slate-600">{{ __('document_number_label') ?: 'رقم الوثيقة' }} <span class="text-slate-400 font-medium text-[10px]">({{ __('optional') ?: 'اختياري' }})</span></label>
                                    <input type="text" wire:model.blur="docNumber" class="w-full bg-slate-50 border border-slate-200 focus:border-[#0A2540] focus:ring-2 focus:ring-[#0A2540]/10 rounded-xl px-4 py-2.5 font-medium text-sm outline-none transition-all font-mono" placeholder="—">
                                    @error('docNumber') <span class="text-[11px] text-rose-500 font-bold block">{{ $message }}</span> @enderror
                                </div>
                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-slate-600">{{ __('document_issuer_label') ?: 'الجهة المصدِرة' }} <span class="text-slate-400 font-medium text-[10px]">({{ __('optional') ?: 'اختياري' }})</span></label>
                                    <input type="text" wire:model.blur="docIssuer" class="w-full bg-slate-50 border border-slate-200 focus:border-[#0A2540] focus:ring-2 focus:ring-[#0A2540]/10 rounded-xl px-4 py-2.5 font-medium text-sm outline-none transition-all" placeholder="{{ __('document_issuer_placeholder') ?: 'مثل: وزارة التجارة' }}">
                                    @error('docIssuer') <span class="text-[11px] text-rose-500 font-bold block">{{ $message }}</span> @enderror
                                </div>
                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-slate-600">{{ __('document_issue_date_label') ?: 'تاريخ الإصدار' }} <span class="text-slate-400 font-medium text-[10px]">({{ __('optional') ?: 'اختياري' }})</span></label>
                                    <input type="date" dir="ltr" wire:model.blur="docIssueDate" class="w-full bg-slate-50 border border-slate-200 focus:border-[#0A2540] focus:ring-2 focus:ring-[#0A2540]/10 rounded-xl px-4 py-2.5 font-medium text-sm outline-none text-start transition-all">
                                    @error('docIssueDate') <span class="text-[11px] text-rose-500 font-bold block">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            {{-- Expiry date with quick chips + "no expiry" toggle. --}}
                            <div class="space-y-2"
                                 x-data="{
                                     noExpiry: @entangle('docExpiry').defer === null || @entangle('docExpiry').defer === '',
                                     setMonths(m) { const d = new Date(); d.setMonth(d.getMonth() + m); this.noExpiry = false; @this.set('docExpiry', d.toISOString().slice(0,10)); },
                                     setYears(y)  { const d = new Date(); d.setFullYear(d.getFullYear() + y); this.noExpiry = false; @this.set('docExpiry', d.toISOString().slice(0,10)); },
                                     clearExpiry() { this.noExpiry = true; @this.set('docExpiry', null); }
                                 }">
                                @php
                                    // For "official" doc types, expiry stays required server-side. For the rest, it's optional.
                                    $expiryRequired = in_array((string) $docType, ['cr','commercial_register','commercial_register_certificate','tax','zakat','gosi','medical_insurance','municipal_license','investment_license'], true);
                                @endphp
                                <label class="text-xs font-bold text-slate-600 flex items-center justify-between flex-wrap gap-2">
                                    <span>
                                        {{ __('expiry_date') }}
                                        @if($expiryRequired)
                                            <span class="text-rose-500">*</span>
                                        @else
                                            <span class="text-slate-400 font-medium text-[10px]">({{ __('optional') ?: 'اختياري' }})</span>
                                        @endif
                                    </span>
                                    @unless($expiryRequired)
                                        <label class="inline-flex items-center gap-1.5 cursor-pointer normal-case">
                                            <input type="checkbox" x-model="noExpiry" @change="if (noExpiry) clearExpiry()" class="rounded border-slate-300 text-[#0A2540] focus:ring-[#0A2540]/30">
                                            <span class="text-[11px] font-bold text-slate-500">{{ __('doc_no_expiry') ?: 'لا يوجد تاريخ انتهاء' }}</span>
                                        </label>
                                    @endunless
                                </label>

                                {{-- Quick chips --}}
                                <div class="flex flex-wrap gap-1.5" x-show="!noExpiry">
                                    <button type="button" @click="setMonths(3)"  class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-[#0A2540]/10 text-slate-700 hover:text-[#0A2540] text-[11px] font-bold border border-slate-200 hover:border-[#0A2540]/40 transition-colors">{{ __('expiry_chip_3m') ?: '3 أشهر' }}</button>
                                    <button type="button" @click="setMonths(6)"  class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-[#0A2540]/10 text-slate-700 hover:text-[#0A2540] text-[11px] font-bold border border-slate-200 hover:border-[#0A2540]/40 transition-colors">{{ __('expiry_chip_6m') ?: '6 أشهر' }}</button>
                                    <button type="button" @click="setYears(1)"   class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-[#0A2540]/10 text-slate-700 hover:text-[#0A2540] text-[11px] font-bold border border-slate-200 hover:border-[#0A2540]/40 transition-colors">{{ __('expiry_chip_1y') ?: 'سنة' }}</button>
                                    <button type="button" @click="setYears(2)"   class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-[#0A2540]/10 text-slate-700 hover:text-[#0A2540] text-[11px] font-bold border border-slate-200 hover:border-[#0A2540]/40 transition-colors">{{ __('expiry_chip_2y') ?: 'سنتان' }}</button>
                                    <button type="button" @click="setYears(3)"   class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-[#0A2540]/10 text-slate-700 hover:text-[#0A2540] text-[11px] font-bold border border-slate-200 hover:border-[#0A2540]/40 transition-colors">{{ __('expiry_chip_3y') ?: '3 سنوات' }}</button>
                                    <button type="button" @click="setYears(5)"   class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-[#0A2540]/10 text-slate-700 hover:text-[#0A2540] text-[11px] font-bold border border-slate-200 hover:border-[#0A2540]/40 transition-colors">{{ __('expiry_chip_5y') ?: '5 سنوات' }}</button>
                                </div>

                                <input type="date" dir="ltr" wire:model.blur="docExpiry" x-bind:disabled="noExpiry" class="w-full bg-slate-50 border border-slate-200 focus:border-[#0A2540] focus:ring-2 focus:ring-[#0A2540]/10 rounded-xl px-4 py-2.5 font-medium text-sm outline-none text-start transition-all disabled:opacity-50 disabled:cursor-not-allowed">

                                @if($docExpiry)
                                    <p class="text-[11px] text-[#1FA7A2] font-bold">
                                        <i class="far fa-calendar-check me-1"></i>
                                        {{ \Carbon\Carbon::parse($docExpiry)->translatedFormat('d F Y') }}
                                    </p>
                                @endif
                                <p class="text-[11px] text-slate-400 font-medium" x-show="noExpiry" x-cloak>{{ __('expiry_no_expiry_hint') ?: 'هذه الوثيقة لن يُسجَّل لها تاريخ انتهاء.' }}</p>
                                @error('docExpiry') <span class="text-[11px] text-rose-500 font-bold block">{{ $message }}</span> @enderror
                            </div>

                            <div class="space-y-1">
                                <label class="text-xs font-bold text-slate-600">{{ __('document_notes_label') ?: 'ملاحظات' }} <span class="text-slate-400 font-medium text-[10px]">({{ __('optional') ?: 'اختياري' }})</span></label>
                                <textarea wire:model.blur="docNotes" rows="3" class="w-full bg-slate-50 border border-slate-200 focus:border-[#0A2540] focus:ring-2 focus:ring-[#0A2540]/10 rounded-xl px-4 py-2.5 font-medium text-sm outline-none resize-none transition-all" placeholder="{{ __('document_notes_placeholder') ?: 'أي ملاحظات داخلية حول الوثيقة...' }}"></textarea>
                                @error('docNotes') <span class="text-[11px] text-rose-500 font-bold block">{{ $message }}</span> @enderror
                            </div>
                            <div class="space-y-1">
                                <label class="text-xs font-bold text-slate-600">{{ __('document_file') }} <span class="text-rose-500">*</span></label>
                                {{-- Phase 3: حذف required لمنع tooltip متصفح إنجليزي — server validate يحرّس --}}
                                <input type="file" wire:model="docFile" x-bind:disabled="uploading" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-sm text-slate-500 file:me-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-[#0A2540]/5 file:text-[#0A2540] hover:file:bg-[#0A2540]/10 cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed">
                                @if($docFile)
                                    <span class="text-[11px] text-slate-500 font-bold block truncate">
                                        <i class="fas fa-paperclip me-1 text-[#1FA7A2]"></i>
                                        {{ __('selected_file_label') }}: {{ $docFile->getClientOriginalName() }}
                                    </span>
                                @else
                                    <span class="text-[11px] text-slate-400 font-medium block">{{ __('upload_drop_or_choose_hint') ?: 'اختر ملف PDF أو JPG أو PNG (حد أقصى 10 ميجابايت).' }}</span>
                                @endif
                                @error('docFile') <span class="text-[11px] text-rose-500 font-bold block">{{ $message }}</span> @enderror
                                <span x-show="uploadError" x-text="uploadError" class="text-[11px] text-rose-500 font-bold block"></span>
                            </div>

                            {{-- Phase 6: AI disclaimer prominent alert — أكبر وأوضح، يستحق وزن alert كامل --}}
                            @if($this->isAiExtractionSupported($docType))
                                <div class="bg-[#1FA7A2]/5 border-2 border-[#1FA7A2]/20 rounded-2xl p-4 flex items-start gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-[#1FA7A2]/15 text-[#1FA7A2] flex items-center justify-center shrink-0">
                                        <i class="fas fa-wand-magic-sparkles text-sm"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h4 class="text-[13px] font-black text-[#0A2540] mb-1">
                                            {{ __('ai_auto_extraction_title') ?: 'سيتم تحليل الوثيقة تلقائيًا بالذكاء الاصطناعي.' }}
                                        </h4>
                                        <p class="text-[12px] text-slate-600 font-medium leading-relaxed">
                                            {{ __('ai_manual_approval_full_hint') ?: 'سيتم تحليل الوثيقة واستخراج بياناتها تلقائيًا بواسطة الذكاء الاصطناعي. لن يتم تحديث ملف المنشأة إلا بعد مراجعتك واعتمادك للبيانات.' }}
                                        </p>
                                    </div>
                                </div>
                            @endif

                            <div x-show="uploading" x-cloak class="space-y-1">
                                <div class="flex items-center justify-between text-[11px] font-bold text-slate-600">
                                    <span><i class="fas fa-circle-notch fa-spin me-1 text-[#1FA7A2]"></i>{{ __('upload_in_progress') }}</span>
                                    <span x-text="progress + '%'"></span>
                                </div>
                                <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-[#1FA7A2] transition-all duration-200" x-bind:style="`width: ${progress}%`"></div>
                                </div>
                            </div>
                        </div>

                        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between gap-3">
                            {{-- Polish: مساحة يسار للحالة (rfu progress / error) بدلاً من تشويه زر الحفظ --}}
                            <div class="flex-1 min-w-0 text-[11px] font-bold">
                                <span x-show="uploading" x-cloak class="text-[#1FA7A2] inline-flex items-center gap-1.5">
                                    <i class="fas fa-circle-notch fa-spin"></i>
                                    <span>{{ __('upload_in_progress') }}</span>
                                </span>
                                <span x-show="!uploading && uploadError" x-cloak x-text="uploadError" class="text-rose-500"></span>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <button type="button"
                                        x-on:click="if(!uploading) open = false"
                                        x-bind:disabled="uploading"
                                        class="px-5 py-2.5 rounded-xl text-slate-600 font-bold text-sm hover:bg-slate-100 border border-slate-200 disabled:opacity-40 disabled:cursor-not-allowed">
                                    {{ __('modal_cancel') }}
                                </button>
                                <button type="submit"
                                        class="px-6 py-2.5 rounded-xl bg-[#0A2540] text-white font-bold text-sm hover:bg-[#0a2540]/90 disabled:opacity-60 disabled:cursor-not-allowed inline-flex items-center gap-2"
                                        wire:loading.attr="disabled" wire:target="saveComplianceDoc"
                                        x-bind:disabled="uploading">
                                    <span wire:loading.remove wire:target="saveComplianceDoc" x-show="!uploading">{{ __('modal_save') }}</span>
                                    <span wire:loading wire:target="saveComplianceDoc" class="inline-flex items-center gap-1.5">
                                        <i class="fas fa-circle-notch fa-spin"></i> <span>{{ __('saving') ?: 'جاري الحفظ...' }}</span>
                                    </span>
                                    <span x-show="uploading" x-cloak class="inline-flex items-center gap-1.5">
                                        <i class="fas fa-cloud-arrow-up"></i> <span>{{ __('uploading_short') ?: 'جاري الرفع' }}</span>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. Create Ticket Modal — Polish: unified style --}}
    <div x-data="{ open: false }"
         x-on:open-modal.window="if($event.detail === 'create-ticket') open = true"
         x-on:close-modal.window="if($event.detail === 'create-ticket') open = false"
         x-on:keydown.escape.window="if(open) open = false"
         x-cloak class="relative z-[9999]">
        <div x-show="open" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" x-transition.opacity x-on:click="open = false"></div>
        <div x-show="open" class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="open" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100" class="relative w-full max-w-lg bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-start">
                        <div>
                            <h3 class="text-lg font-black text-[#0A2540]">{{ __('create_ticket') }}</h3>
                            <p class="text-xs text-slate-500 font-medium mt-0.5">{{ __('create_ticket_hint') ?: 'صف استفسارك بوضوح ليتمكن فريق شركة آمر سبعة لحلول الأعمال من المتابعة.' }}</p>
                        </div>
                        <button type="button" x-on:click="open = false" class="w-8 h-8 rounded-lg text-slate-400 hover:text-rose-500 hover:bg-slate-50 flex items-center justify-center"><i class="fas fa-times text-sm"></i></button>
                    </div>

                    <form wire:submit="createTicket">
                        <div class="p-6 space-y-4">
                            <div class="space-y-1">
                                <label class="text-xs font-bold text-slate-600">{{ __('ticket_subject') }} <span class="text-rose-500">*</span></label>
                                <input type="text" wire:model.blur="newTicketSubject" class="w-full bg-slate-50 border border-slate-200 focus:border-[#0A2540] focus:ring-2 focus:ring-[#0A2540]/10 rounded-xl px-4 py-2.5 font-medium text-sm outline-none transition-all">
                                @error('newTicketSubject') <span class="text-[11px] text-rose-500 font-bold block">{{ $message }}</span> @enderror
                            </div>
                            <div class="space-y-1">
                                <label class="text-xs font-bold text-slate-600">{{ __('ticket_details') }} <span class="text-rose-500">*</span></label>
                                <textarea wire:model.blur="newTicketMessage" rows="5" class="w-full bg-slate-50 border border-slate-200 focus:border-[#0A2540] focus:ring-2 focus:ring-[#0A2540]/10 rounded-xl px-4 py-2.5 font-medium text-sm outline-none resize-none transition-all"></textarea>
                                @error('newTicketMessage') <span class="text-[11px] text-rose-500 font-bold block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex justify-end gap-2">
                            <button type="button" x-on:click="open = false" class="px-5 py-2.5 rounded-xl text-slate-600 font-bold text-sm hover:bg-slate-100 border border-slate-200">{{ __('modal_cancel') }}</button>
                            <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#0A2540] text-white font-bold text-sm hover:bg-[#0a2540]/90 disabled:opacity-60 disabled:cursor-not-allowed" wire:loading.attr="disabled" wire:target="createTicket">
                                <span wire:loading.remove wire:target="createTicket">{{ __('send_reply') === 'إرسال الرد' ? 'إرسال التذكرة' : __('btn_submit_ticket') }}</span>
                                <span wire:loading wire:target="createTicket"><i class="fas fa-circle-notch fa-spin"></i></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Toast bridge: surfaces $this->dispatch('notify', ...) as a SweetAlert toast (Swal is loaded globally in app.js). --}}
    <script>
        (function () {
            if (window.__amr7DashboardNotifyBound) return;
            window.__amr7DashboardNotifyBound = true;

            const showToast = (detail) => {
                if (!window.Swal) return;
                const payload = Array.isArray(detail) ? (detail[0] ?? {}) : (detail ?? {});
                const type = (payload && payload.type) ? String(payload.type) : 'success';
                const message = (payload && payload.message) ? String(payload.message) : '';
                if (!message) return;

                const icon = ({ success: 'success', error: 'error', warning: 'warning', info: 'info' })[type] || 'info';

                window.Swal.fire({
                    toast: true,
                    position: 'top',
                    icon: icon,
                    title: message,
                    showConfirmButton: false,
                    timer: type === 'error' ? 4500 : 2800,
                    timerProgressBar: true,
                });
            };

            window.addEventListener('notify', (e) => showToast(e.detail));
            document.addEventListener('livewire:initialized', () => {
                if (window.Livewire && typeof window.Livewire.on === 'function') {
                    window.Livewire.on('notify', (detail) => showToast(detail));
                }
            });
        })();
    </script>
</div>
