<div class="min-h-screen bg-slate-50 pt-24 pb-20 font-['Tajawal'] relative" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    
    <div class="fixed inset-0 pointer-events-none z-0">
        <div class="absolute top-0 right-0 w-1/3 h-1/3 bg-[#1FA7A2]/5 blur-3xl rounded-full"></div>
        <div class="absolute bottom-0 left-0 w-1/3 h-1/3 bg-amber-500/5 blur-3xl rounded-full"></div>
    </div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

 @php
    $statusMeta   = $this->statusMeta;
    $backUrl      = $this->isStaff ? route('financial-statements.index') : route('financial-statements.portal');
    $uiLabel      = $this->uiStatusLabel;
    $uiColor      = $this->uiStatusColor; // amber | emerald | teal
    $reqCount     = $this->requiredDocumentsTotal;
    $reqUploaded  = $this->uploadedRequiredCount;
    $reqPct       = $reqCount > 0 ? (int) round(($reqUploaded / $reqCount) * 100) : 0;

    // Hero badge: custom amber/emerald banners only during the upload phase.
    // For every other status (in_review, completed, rejected, …) fall back to
    // the existing $statusMeta map so we never miscolor a rejected request.
    if ($this->isAwaitingClientUpload && ! $this->isReadyForReview) {
        $heroBadge = 'bg-amber-50 text-amber-700 border-amber-200';
        $heroIcon  = 'fa-hourglass-half';
    } elseif ($this->isAwaitingClientUpload && $this->isReadyForReview) {
        $heroBadge = 'bg-emerald-50 text-emerald-700 border-emerald-200';
        $heroIcon  = 'fa-circle-check';
    } else {
        $heroBadge = $statusMeta['class'];
        $heroIcon  = $statusMeta['icon'];
    }
    $heroProgressBg = match($uiColor) {
        'amber'   => 'from-amber-400 to-amber-500',
        'emerald' => 'from-emerald-400 to-emerald-500',
        default   => 'from-[#1FA7A2] to-[#1FA7A2]',
    };
@endphp

        {{-- Hero status card — request id, company, fiscal year, ui status, progress, CTA --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-8 animate__animated animate__fadeInDown">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 mb-2 flex-wrap">
                        <span class="px-2.5 py-0.5 rounded-md bg-slate-100 text-slate-500 text-xs font-bold border border-slate-200">
                            {{ __('request_details') }}
                        </span>
                        <span class="text-slate-300">|</span>
                        <span class="text-slate-400 text-xs font-bold font-mono tracking-wider">
                            {{ __('tracking_number') }}: <span class="text-[#1FA7A2]">{{ $request->public_id ?? $request->id }}</span>
                        </span>
                        <span class="text-slate-300">|</span>
                        <span class="text-slate-400 text-xs font-bold tracking-wider">
                            {{ __('label_fiscal_year') }}: <span class="text-[#1FA7A2] font-mono">{{ $request->fiscal_year }}</span>
                        </span>
                    </div>

                    <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 mb-4 truncate">{{ $request->company_name }}</h1>

                    @php
                        // Surface the current timeline stage as a subtitle so a client whose
                        // request has moved past upload (e.g. in_review) sees clearly which
                        // step the team is on, not just the high-level status badge.
                        $currentStage = collect($this->timelineSteps)->firstWhere('state', 'current');
                    @endphp
                    <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3">
                        <div class="inline-flex items-center px-4 py-1.5 rounded-full border {{ $heroBadge }}">
                            <i class="fas {{ $heroIcon }} rtl:ml-2 ltr:mr-2 text-xs"></i>
                            <span class="text-sm font-bold">{{ $uiLabel }}</span>
                        </div>
                        @if($currentStage && ! $this->isAwaitingClientUpload && ! $this->isRejectedFlow)
                            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500">
                                <i class="fas fa-location-dot text-[10px] text-slate-400"></i>
                                {{ __('fs_hero_current_stage') ?: 'المرحلة الحالية' }}: <span class="text-[#1FA7A2]">{{ $currentStage['label'] }}</span>
                            </span>
                        @endif
                    </div>
                </div>

                <a href="{{ $backUrl }}"
                   class="group inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold hover:bg-slate-50 hover:text-[#1FA7A2] hover:border-[#1FA7A2] transition-all duration-300 shrink-0">
                    <i class="fas fa-arrow-right rtl:rotate-180 text-slate-400 group-hover:text-[#1FA7A2] transition-colors"></i>
                    {{ __('btn_back_to_list') }}
                </a>
            </div>

            @if($reqCount > 0 && $this->isAwaitingClientUpload)
                <div class="mt-5 pt-5 border-t border-slate-100">
                    <div class="flex items-center justify-between text-[11px] font-bold text-slate-500 mb-1.5">
                        <span>{{ __('fs_hero_progress_label') ?: 'اكتمال المستندات الأساسية' }}</span>
                        <span class="font-mono text-slate-700">{{ $reqUploaded }}/{{ $reqCount }} ({{ $reqPct }}%)</span>
                    </div>
                    <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-l {{ $heroProgressBg }} transition-all duration-700" style="width: {{ $reqPct }}%"></div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Phase 8C: Visual Timeline — 6 مراحل، تظهر done/current/pending أو rejected --}}
        @php $timelineSteps = $this->timelineSteps; $isRejectedFlow = $this->isRejectedFlow; @endphp
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-8 animate__animated animate__fadeInUp">
            <div class="flex items-center justify-between mb-5">
                <h3 class="font-bold text-slate-800 flex items-center gap-2">
                    <i class="fas fa-route text-[#1FA7A2]"></i>
                    <span>{{ __('fs_timeline_title') ?: 'مراحل الطلب' }}</span>
                </h3>
                @if($isRejectedFlow)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-100">
                        <i class="fas fa-circle-xmark text-[10px]"></i>
                        <span>{{ $statusMeta['label'] }}</span>
                    </span>
                @endif
            </div>

            {{-- Desktop: horizontal stepper. Mobile: vertical stack. --}}
            <div class="hidden md:flex items-start justify-between relative">
                {{-- خط الـtimeline الأساسي --}}
                <div class="absolute top-5 left-0 right-0 h-0.5 bg-slate-100 z-0"></div>
                @foreach($timelineSteps as $step)
                    @php
                        $isDone    = $step['state'] === 'done';
                        $isCurrent = $step['state'] === 'current';
                        $isPending = $step['state'] === 'pending';
                        $isReject  = $step['state'] === 'rejected';

                        $circleClass = $isDone
                            ? 'bg-emerald-500 text-white border-emerald-500'
                            : ($isCurrent
                                ? 'bg-[#1FA7A2] text-white border-[#1FA7A2] ring-4 ring-[#1FA7A2]/15'
                                : ($isReject
                                    ? 'bg-slate-100 text-slate-400 border-slate-200'
                                    : 'bg-white text-slate-400 border-slate-200'));
                        $labelClass  = ($isCurrent || $isDone) ? 'text-[#0A2540] font-black' : 'text-slate-500 font-bold';
                        $iconUse     = $isDone ? 'fa-check' : $step['icon'];
                    @endphp
                    <div wire:key="fs-timeline-step-{{ $step['key'] }}" class="relative z-10 flex flex-col items-center text-center flex-1">
                        <div class="w-10 h-10 rounded-full border-2 flex items-center justify-center text-sm transition-all {{ $circleClass }}">
                            <i class="fas {{ $iconUse }} text-xs"></i>
                        </div>
                        <p class="mt-2 text-[11px] {{ $labelClass }} px-1 leading-tight">{{ $step['label'] }}</p>
                        @if($step['timestamp'])
                            <p class="text-[10px] text-slate-400 font-mono mt-0.5">{{ $step['timestamp'] }}</p>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- Mobile vertical --}}
            <ol class="md:hidden space-y-3 relative ms-3 border-s-2 border-slate-100">
                @foreach($timelineSteps as $step)
                    @php
                        $isDone    = $step['state'] === 'done';
                        $isCurrent = $step['state'] === 'current';
                        $isReject  = $step['state'] === 'rejected';
                        $dotClass = $isDone
                            ? 'bg-emerald-500 border-emerald-500'
                            : ($isCurrent
                                ? 'bg-[#1FA7A2] border-[#1FA7A2] ring-2 ring-[#1FA7A2]/20'
                                : ($isReject ? 'bg-slate-100 border-slate-200' : 'bg-white border-slate-300'));
                        $labelClass = ($isCurrent || $isDone) ? 'text-[#0A2540] font-black' : 'text-slate-500 font-bold';
                    @endphp
                    <li wire:key="fs-timeline-step-mobile-{{ $step['key'] }}" class="relative ps-4 pb-1">
                        <span class="absolute -start-1.5 top-1 w-3 h-3 rounded-full border-2 {{ $dotClass }}"></span>
                        <div class="flex items-baseline justify-between gap-2">
                            <p class="text-xs {{ $labelClass }}">{{ $step['label'] }}</p>
                            @if($step['timestamp'])
                                <span class="text-[10px] text-slate-400 font-mono shrink-0">{{ $step['timestamp'] }}</span>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <div class="lg:col-span-8 space-y-8 animate__animated animate__fadeInUp">
                
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                        <h3 class="font-bold text-slate-800 flex items-center gap-2">
                            <i class="fas fa-folder-open text-[#1FA7A2]"></i> {{ __('documents_and_files') }}
                        </h3>
                        <p class="text-xs text-slate-500 mt-1">{{ __('upload_required_documents_desc') }}</p>
                    </div>

                    <div class="p-6 space-y-8">
                        
                        <div>
                            <div class="flex items-center gap-2 mb-4">
                                <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                <h4 class="text-sm font-bold text-slate-700">{{ __('required_documents_label') }}</h4>
                                <span class="ms-auto text-[11px] font-bold text-slate-500">
                                    {{ $this->uploadedRequiredCount }}/{{ $this->requiredDocumentsTotal }}
                                </span>
                            </div>

                            <div class="grid grid-cols-1 gap-4">
                                @foreach($this->requiredDocuments as $key => $doc)
                                    @php
                                        $isUploaded = $doc['uploaded'];
                                        $isPending  = $doc['pending'] && ! $isUploaded;
                                        $uploadedFile = $doc['file'];
                                        $cardClass = $isUploaded
                                            ? 'bg-emerald-50/40 border-emerald-200'
                                            : ($isPending ? 'bg-amber-50/40 border-amber-200' : 'bg-white border-slate-200');
                                    @endphp
                                    <div wire:key="fs-req-card-{{ $key }}-{{ $uploadVersion }}"
                                         class="border rounded-xl p-4 transition-all hover:shadow-md {{ $cardClass }}">

                                        {{-- Header row: label + status badge --}}
                                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-3">
                                            <div class="flex items-start gap-3 min-w-0">
                                                <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0
                                                    {{ $isUploaded ? 'bg-emerald-100 text-emerald-600' : ($isPending ? 'bg-amber-100 text-amber-600' : 'bg-slate-100 text-slate-400') }}">
                                                    <i class="fas {{ $isUploaded ? 'fa-circle-check' : ($isPending ? 'fa-hourglass-half' : 'fa-file-upload') }}"></i>
                                                </div>
                                                <div class="min-w-0">
                                                    <h5 class="text-sm font-bold text-slate-800 truncate">{{ $doc['label'] }}</h5>
                                                    @if($doc['description'])
                                                        <p class="text-[11px] text-slate-500 mt-0.5 leading-snug">{{ $doc['description'] }}</p>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="shrink-0">
                                                @if($isUploaded)
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 text-[11px] font-black border border-emerald-200">
                                                        <i class="fas fa-check text-[9px]"></i> {{ __('fs_doc_badge_uploaded') ?: 'تم الرفع' }}
                                                    </span>
                                                @elseif($isPending)
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-100 text-amber-700 text-[11px] font-black border border-amber-200">
                                                        <i class="fas fa-circle-dot text-[9px]"></i> {{ __('fs_doc_badge_pending') ?: 'بانتظار التأكيد' }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-rose-50 text-rose-700 text-[11px] font-black border border-rose-200">
                                                        <i class="fas fa-circle-exclamation text-[9px]"></i> {{ __('fs_doc_badge_required') ?: 'مطلوب' }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- State A: Uploaded — show file row + replace button --}}
                                        @if($isUploaded && $uploadedFile)
                                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white border border-emerald-100 rounded-lg p-3">
                                                <div class="flex items-center gap-3 min-w-0">
                                                    <i class="fas fa-paperclip text-emerald-600 shrink-0"></i>
                                                    <div class="min-w-0">
                                                        <a href="{{ route('financial-statements.file.download', $uploadedFile->id) }}"
                                                           class="text-xs font-bold text-[#1FA7A2] hover:underline truncate block"
                                                           title="{{ $uploadedFile->original_name }}">
                                                            {{ Str::limit($uploadedFile->original_name, 48) }}
                                                        </a>
                                                        <span class="text-[10px] text-slate-400 block mt-0.5">
                                                            {{ optional($uploadedFile->created_at)->diffForHumans() }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <label class="cursor-pointer shrink-0 inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-slate-200 bg-white text-slate-600 text-[11px] font-bold hover:border-[#1FA7A2] hover:text-[#1FA7A2] transition-all">
                                                    <i class="fas fa-rotate"></i>
                                                    <span>{{ __('fs_doc_btn_replace') ?: 'استبدال الملف' }}</span>
                                                    <input type="file" class="hidden" wire:model="docs.{{ $key }}">
                                                </label>
                                            </div>

                                        {{-- State B: Pending — show picked file + upload/cancel buttons --}}
                                        @elseif($isPending)
                                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white border border-amber-200 rounded-lg p-3">
                                                <div class="flex items-center gap-3 min-w-0">
                                                    <i class="fas fa-file-circle-plus text-amber-600 shrink-0"></i>
                                                    <div class="min-w-0">
                                                        <span class="text-xs font-bold text-slate-800 truncate block" title="{{ $this->docs[$key]->getClientOriginalName() }}">
                                                            {{ Str::limit($this->docs[$key]->getClientOriginalName(), 48) }}
                                                        </span>
                                                        @php $sizeKb = (int) round(((int) $this->docs[$key]->getSize()) / 1024); @endphp
                                                        <span class="text-[10px] text-slate-400 block mt-0.5 font-mono">
                                                            {{ $sizeKb >= 1024 ? round($sizeKb/1024, 2).' MB' : $sizeKb.' KB' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-2 shrink-0">
                                                    <button type="button"
                                                            wire:click="removePendingDoc('{{ $key }}')"
                                                            wire:loading.attr="disabled"
                                                            wire:target="uploadSingle('{{ $key }}'),removePendingDoc('{{ $key }}')"
                                                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-slate-200 bg-white text-slate-500 text-[11px] font-bold hover:border-rose-300 hover:text-rose-600 transition-all disabled:opacity-50">
                                                        <i class="fas fa-xmark"></i>
                                                        <span>{{ __('fs_doc_btn_remove') ?: 'إزالة' }}</span>
                                                    </button>
                                                    <button type="button"
                                                            wire:click="uploadSingle('{{ $key }}')"
                                                            wire:loading.attr="disabled"
                                                            wire:target="uploadSingle('{{ $key }}')"
                                                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#1FA7A2] text-white text-xs font-black hover:bg-[#167F7B] shadow-sm hover:shadow-md transition-all disabled:opacity-60 disabled:cursor-not-allowed">
                                                        <span wire:loading.remove wire:target="uploadSingle('{{ $key }}')">
                                                            <i class="fas fa-cloud-arrow-up me-1"></i> {{ __('fs_doc_btn_upload_now') ?: 'رفع الآن' }}
                                                        </span>
                                                        <span wire:loading wire:target="uploadSingle('{{ $key }}')">
                                                            <i class="fas fa-spinner fa-spin me-1"></i> {{ __('fs_doc_btn_uploading') ?: 'جاري الرفع…' }}
                                                        </span>
                                                    </button>
                                                </div>
                                            </div>

                                        {{-- State C: Empty — show single "اختيار ملف" button --}}
                                        @else
                                            <label class="cursor-pointer flex items-center justify-center gap-2 px-4 py-3 rounded-lg border-2 border-dashed border-slate-200 bg-white text-slate-500 text-xs font-bold hover:border-[#1FA7A2] hover:text-[#1FA7A2] hover:bg-[#1FA7A2]/5 transition-all">
                                                <span wire:loading.remove wire:target="docs.{{ $key }}">
                                                    <i class="fas fa-file-arrow-up me-1.5"></i> {{ __('fs_doc_btn_choose') ?: 'اختيار ملف للرفع' }}
                                                </span>
                                                <span wire:loading wire:target="docs.{{ $key }}">
                                                    <i class="fas fa-spinner fa-spin me-1.5"></i> {{ __('processing') }}
                                                </span>
                                                <input type="file" class="hidden" wire:model="docs.{{ $key }}">
                                            </label>
                                        @endif

                                        @error("docs.$key")
                                            <p class="text-rose-600 text-[11px] mt-2 font-bold flex items-center gap-1.5">
                                                <i class="fas fa-circle-exclamation"></i> {{ $message }}
                                            </p>
                                        @enderror
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Optional named documents — collapsed by default, no impact on 3/3 checklist. --}}
                        <div x-data="{ open: false }">
                            <button @click="open = !open" type="button" class="flex items-center gap-2 mb-4 w-full text-start group">
                                <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                                <h4 class="text-sm font-bold text-slate-700 group-hover:text-[#1FA7A2] transition-colors">{{ __('fs_optional_named_docs') ?: __('optional_documents_label') }}</h4>
                                <span class="text-[10px] text-slate-400 font-bold">({{ __('fs_optional_hint') ?: 'اختياري — لا يؤثر على إكمال الطلب' }})</span>
                                <i class="fas fa-chevron-down text-slate-400 text-xs transition-transform ms-auto" :class="{'rotate-180': open}"></i>
                            </button>

                            <div x-show="open" x-collapse class="grid grid-cols-1 gap-3">
                                @foreach($this->optionalDocs as $key => $label)
                                    @php
                                        $uploadedFile = $filesByKey->get($key)?->first();
                                        $isPending    = isset($docs[$key]) && $docs[$key] !== null && ! $uploadedFile;
                                    @endphp
                                    <div wire:key="fs-opt-card-{{ $key }}-{{ $uploadVersion }}"
                                         class="border border-dashed rounded-xl p-3 transition-colors {{ $uploadedFile ? 'bg-amber-50/30 border-amber-200 border-solid' : ($isPending ? 'bg-amber-50/20 border-amber-200' : 'border-slate-200 hover:bg-slate-50') }}">
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="flex items-center gap-3 min-w-0">
                                                <i class="fas fa-file-alt {{ $uploadedFile ? 'text-amber-500' : 'text-slate-300' }} shrink-0"></i>
                                                <div class="min-w-0">
                                                    <span class="text-sm font-bold text-slate-700 truncate block">{{ $label }}</span>
                                                    @if($uploadedFile)
                                                        <a href="{{ route('financial-statements.file.download', $uploadedFile->id) }}" class="text-[11px] text-[#1FA7A2] hover:underline truncate block" title="{{ $uploadedFile->original_name }}">
                                                            <i class="fas fa-paperclip me-1"></i>{{ Str::limit($uploadedFile->original_name, 40) }}
                                                        </a>
                                                    @elseif($isPending)
                                                        <span class="text-[11px] text-amber-700 font-bold truncate block" title="{{ $docs[$key]->getClientOriginalName() }}">
                                                            <i class="fas fa-hourglass-half me-1"></i>{{ Str::limit($docs[$key]->getClientOriginalName(), 40) }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-1.5 shrink-0">
                                                @if($isPending)
                                                    <button type="button"
                                                            wire:click="removePendingDoc('{{ $key }}')"
                                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 text-slate-500 hover:border-rose-300 hover:text-rose-600 transition-colors"
                                                            title="{{ __('fs_doc_btn_remove') ?: 'إزالة' }}">
                                                        <i class="fas fa-xmark text-xs"></i>
                                                    </button>
                                                    <button type="button"
                                                            wire:click="uploadSingle('{{ $key }}')"
                                                            wire:loading.attr="disabled"
                                                            wire:target="uploadSingle('{{ $key }}')"
                                                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-[#1FA7A2] text-white text-[11px] font-black hover:bg-[#167F7B] transition-colors disabled:opacity-60">
                                                        <span wire:loading.remove wire:target="uploadSingle('{{ $key }}')">
                                                            <i class="fas fa-cloud-arrow-up"></i> {{ __('fs_doc_btn_upload_now') ?: 'رفع الآن' }}
                                                        </span>
                                                        <span wire:loading wire:target="uploadSingle('{{ $key }}')">
                                                            <i class="fas fa-spinner fa-spin"></i>
                                                        </span>
                                                    </button>
                                                @else
                                                    <label class="cursor-pointer text-[#1FA7A2] hover:bg-[#1FA7A2]/10 p-2 rounded-lg transition-colors" title="{{ $uploadedFile ? (__('fs_doc_btn_replace') ?: 'استبدال الملف') : (__('fs_doc_btn_choose') ?: 'اختيار ملف') }}">
                                                        <i class="fas {{ $uploadedFile ? 'fa-rotate' : 'fa-cloud-upload-alt' }}" wire:loading.remove wire:target="docs.{{ $key }}"></i>
                                                        <i class="fas fa-spinner fa-spin" wire:loading wire:target="docs.{{ $key }}"></i>
                                                        <input type="file" class="hidden" wire:model="docs.{{ $key }}">
                                                    </label>
                                                @endif
                                            </div>
                                        </div>
                                        @error("docs.$key") <span class="text-rose-600 text-[10px] block mt-1.5 font-bold">{{ $message }}</span> @enderror
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Invoices / extra-files batch group — multi-file, never affects 3/3. --}}
                        <div class="bg-slate-50 rounded-xl p-5 border border-slate-200">
                            <div class="flex justify-between items-start mb-4 gap-3">
                                <div class="min-w-0">
                                    <h4 class="text-sm font-bold text-slate-800">{{ __('fs_invoices_section_title') ?: __('label_invoices_group') }}</h4>
                                    <p class="text-xs text-slate-500 mt-1">{{ __('multi_upload_hint') }}</p>
                                </div>
                                <label class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 bg-[#1FA7A2] text-white text-xs font-bold rounded-lg hover:bg-[#167F7B] shadow-md transition-all shrink-0">
                                    <i class="fas fa-plus"></i> {{ __('btn_add_files') }}
                                    <input type="file" multiple wire:model="multiDocs.invoices" class="hidden">
                                </label>
                            </div>

                            @if(isset($multiDocs['invoices']) && count($multiDocs['invoices']))
                                <div class="mb-4 bg-white p-4 rounded-lg border border-amber-200 space-y-3">
                                    <div class="flex items-center justify-between gap-3 flex-wrap">
                                        <p class="text-xs font-black text-amber-700 flex items-center gap-1.5">
                                            <i class="fas fa-hourglass-half"></i>
                                            {{ count($multiDocs['invoices']) }} {{ __('fs_files_pending_count') ?: 'ملف بانتظار الرفع' }}
                                        </p>
                                        <div class="flex items-center gap-2">
                                            <button type="button"
                                                    wire:click="clearPendingInvoices"
                                                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-slate-200 bg-white text-slate-500 text-[11px] font-bold hover:border-rose-300 hover:text-rose-600 transition-all">
                                                <i class="fas fa-xmark"></i> {{ __('fs_doc_btn_remove') ?: 'إزالة' }}
                                            </button>
                                            <button type="button"
                                                    wire:click="uploadInvoices"
                                                    wire:loading.attr="disabled"
                                                    wire:target="uploadInvoices"
                                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#1FA7A2] text-white text-xs font-black hover:bg-[#167F7B] shadow-sm transition-all disabled:opacity-60 disabled:cursor-not-allowed">
                                                <span wire:loading.remove wire:target="uploadInvoices">
                                                    <i class="fas fa-cloud-arrow-up me-1"></i> {{ __('fs_btn_upload_files_now') ?: 'رفع الملفات الآن' }}
                                                </span>
                                                <span wire:loading wire:target="uploadInvoices">
                                                    <i class="fas fa-spinner fa-spin me-1"></i> {{ __('fs_doc_btn_uploading') ?: 'جاري الرفع…' }}
                                                </span>
                                            </button>
                                        </div>
                                    </div>
                                    <ul class="divide-y divide-amber-100 border border-amber-100 rounded-md">
                                        @foreach($multiDocs['invoices'] as $idx => $pending)
                                            @php $kb = (int) round(((int) $pending->getSize()) / 1024); @endphp
                                            <li class="flex items-center justify-between gap-3 px-3 py-2">
                                                <span class="text-[11px] font-bold text-slate-700 truncate" title="{{ $pending->getClientOriginalName() }}">
                                                    <i class="fas fa-file-circle-plus text-amber-600 me-1.5"></i>
                                                    {{ Str::limit($pending->getClientOriginalName(), 40) }}
                                                </span>
                                                <span class="text-[10px] text-slate-400 font-mono shrink-0">
                                                    {{ $kb >= 1024 ? round($kb/1024, 2).' MB' : $kb.' KB' }}
                                                </span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @php $invoices = $filesByKey->get('invoices') ?? collect(); @endphp

                            @if($invoices->count())
                                <div>
                                    <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">
                                        {{ __('fs_uploaded_files_label') ?: 'الملفات المرفوعة' }}
                                    </p>
                                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3 max-h-60 overflow-y-auto custom-scrollbar rtl:pr-1 ltr:pl-1">
                                        @foreach($invoices as $inv)
                                            <div class="bg-white p-2 rounded-lg border border-slate-200 flex items-center justify-between group">
                                                <div class="flex items-center gap-2 overflow-hidden">
                                                    <i class="fas fa-file-invoice text-slate-400"></i>
                                                    <span class="text-xs text-slate-600 truncate" title="{{ $inv->original_name }}">{{ Str::limit($inv->original_name, 15) }}</span>
                                                </div>
                                                <a href="{{ route('financial-statements.file.download', $inv->id) }}" class="text-slate-300 hover:text-[#1FA7A2] transition-colors"><i class="fas fa-download text-xs"></i></a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div class="text-center py-6 border-2 border-dashed border-slate-200 rounded-lg bg-white/50">
                                    <span class="text-xs text-slate-400">{{ __('no_invoices_uploaded_yet') }}</span>
                                </div>
                            @endif
                        </div>

                    </div>
                </div>
                {{-- Final files block — only shown after the request reaches an approved/final stage. --}}
                @php
                    $finalOutputs = $filesByKey->get('final_output') ?? collect();
                    $finalVisibleStatuses = [
                        'internal_approved', 'moci_approved',
                        'approved', 'completed', 'closed',
                    ];
                    $showFinalBlock = in_array(strtolower((string) $request->status), $finalVisibleStatuses, true);
                @endphp

                @if($showFinalBlock)
                    <div class="bg-emerald-50 rounded-xl p-5 border border-emerald-200">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <h4 class="text-sm font-bold text-emerald-800">{{ __('fs_final_files_title') ?: 'الملفات النهائية' }}</h4>
                        </div>

                        @if($finalOutputs->count())
                            <div class="space-y-3">
                                @foreach($finalOutputs as $file)
                                    <div class="bg-white p-3 rounded-lg border border-emerald-200 flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-3 overflow-hidden">
                                            <i class="fas fa-file-download text-emerald-600"></i>
                                            <div class="overflow-hidden">
                                                <div class="text-sm font-bold text-slate-800 truncate">{{ $file->original_name }}</div>
                                                <div class="text-[11px] text-slate-400">{{ $file->created_at?->diffForHumans() }}</div>
                                            </div>
                                        </div>

                                        <a href="{{ route('financial-statements.file.download', $file->id) }}"
                                           class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 transition">
                                            <i class="fas fa-download"></i>
                                            {{ __('fs_btn_download') ?: 'تحميل' }}
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-6 border-2 border-dashed border-emerald-200 rounded-lg bg-white/60">
                                <span class="text-xs text-emerald-700">{{ __('fs_final_files_pending') ?: 'سيتم توفير الملفات النهائية بعد اكتمال المراجعة.' }}</span>
                            </div>
                        @endif
                    </div>
                @endif

                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 flex flex-col h-[600px] overflow-hidden">
                    <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                        <div>
                            <h3 class="font-bold text-slate-800 flex items-center gap-2">
                                <i class="fas fa-comments text-[#1FA7A2]"></i> {{ __('chat_history_header') }}
                            </h3>
                            <p class="text-xs text-slate-500 mt-1">{{ __('chat_history_desc') }}</p>
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto p-5 space-y-4 custom-scrollbar bg-slate-50/30 flex flex-col-reverse">
                        @forelse($messages as $msg)
                            @php
                                $isMe = $msg->sender_id == auth()->id();
                                $isAdmin = $msg->sender_type === 'staff';
                            @endphp

                            <div class="flex {{ $isMe ? 'justify-end' : 'justify-start' }} w-full">
                                <div class="flex items-end gap-2 max-w-[85%] {{ $isMe ? 'flex-row-reverse' : 'flex-row' }}">
                                    
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 border shadow-sm {{ $isMe ? 'bg-[#1FA7A2] text-white border-[#1FA7A2]' : ($isAdmin ? 'bg-slate-800 text-white border-slate-800' : 'bg-gray-200 text-gray-600') }}">
                                        <i class="fas {{ $isAdmin ? 'fa-headset' : 'fa-user' }} text-xs"></i>
                                    </div>

                                    <div class="{{ $isMe ? 'bg-[#1FA7A2] text-white rtl:rounded-l-2xl rtl:rounded-tr-2xl ltr:rounded-r-2xl ltr:rounded-tl-2xl' : 'bg-white border border-slate-200 text-slate-700 rtl:rounded-r-2xl rtl:rounded-tl-2xl ltr:rounded-l-2xl ltr:rounded-tr-2xl' }} p-3 shadow-sm relative group">
                                        <div class="text-sm leading-relaxed whitespace-pre-line">{{ $msg->body }}</div>
                                        <div class="text-[10px] mt-1 opacity-70 flex justify-end font-mono">
                                            {{ $msg->created_at->format('H:i') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="flex-1 flex flex-col items-center justify-center text-slate-300">
                                <i class="far fa-comment-dots text-5xl mb-3 opacity-50"></i>
                                <p class="text-sm">{{ __('no_messages_yet') }}</p>
                            </div>
                        @endforelse
                    </div>

                    <div class="p-4 border-t border-slate-100 bg-white">
                        <div class="flex gap-2">
                            <input type="text" wire:model="message" 
                                   class="flex-1 h-12 bg-slate-50 border border-slate-200 rounded-xl px-4 focus:bg-white focus:border-[#1FA7A2] focus:ring-2 focus:ring-[#1FA7A2]/10 outline-none transition-all placeholder:text-slate-400" 
                                   placeholder="{{ __('write_reply_placeholder') }}"
                                   wire:keydown.enter="sendMessage">
                            
                            <button class="h-12 px-6 bg-[#1FA7A2] hover:bg-[#167F7B] text-white rounded-xl font-bold shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all flex items-center gap-2 disabled:opacity-50" 
                                    wire:click="sendMessage"
                                    wire:loading.attr="disabled"
                                    wire:target="sendMessage">
                                <span wire:loading.remove wire:target="sendMessage"><i class="fas fa-paper-plane rtl:rotate-180"></i></span>
                                <span wire:loading wire:target="sendMessage"><i class="fas fa-spinner fa-spin"></i></span>
                            </button>
                        </div>
                    </div>
                </div>

            </div>

            <div class="lg:col-span-4 space-y-8 animate__animated animate__fadeInUp animate__delay-1s">

                {{-- Sidebar checklist — single source of truth is $this->requiredDocuments. --}}
                @php
                    $requiredDocuments     = $this->requiredDocuments;
                    $requiredCount         = $this->requiredDocumentsTotal;
                    $uploadedRequiredCount = $this->uploadedRequiredCount;
                    $missingRequired       = collect($requiredDocuments)->reject(fn ($d) => $d['uploaded'])->keys();
                    $progressPct           = $requiredCount > 0 ? (int) round(($uploadedRequiredCount / $requiredCount) * 100) : 0;
                    $optionalKeys          = array_keys($this->optionalDocs);
                    $hasInvoices           = ($filesByKey->get('invoices') ?? collect())->isNotEmpty();
                    $hasFinal              = ($filesByKey->get('final_output') ?? collect())->isNotEmpty();
                @endphp
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 lg:sticky lg:top-24">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-bold text-slate-800 flex items-center gap-2">
                            <i class="fas fa-clipboard-check text-[#1FA7A2]"></i>
                            <span>{{ __('fs_checklist_title') ?: 'متطلبات إكمال الطلب' }}</span>
                        </h3>
                        @if($requiredCount > 0)
                            <span class="inline-flex items-center gap-1 text-[10px] font-black px-2 py-0.5 rounded-md bg-[#1FA7A2]/10 text-[#1FA7A2] border border-[#1FA7A2]/15">
                                {{ $uploadedRequiredCount }}/{{ $requiredCount }}
                            </span>
                        @endif
                    </div>

                    @if($requiredCount > 0)
                        <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden mb-4">
                            <div class="h-full bg-gradient-to-l from-[#1FA7A2] to-[#1FA7A2] transition-all duration-700" style="width: {{ $progressPct }}%"></div>
                        </div>
                    @endif

                    {{-- Required documents — same source as upload cards. --}}
                    @if($requiredCount > 0)
                        <div class="mb-4">
                            <h4 class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">{{ __('fs_checklist_required') ?: 'المستندات الأساسية' }}</h4>
                            <ul class="space-y-1.5">
                                @foreach($requiredDocuments as $key => $doc)
                                    <li wire:key="fs-checklist-req-{{ $key }}" class="flex items-center gap-2 text-[12px]">
                                        @if($doc['uploaded'])
                                            <span class="w-4 h-4 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0">
                                                <i class="fas fa-check text-[8px]"></i>
                                            </span>
                                            <span class="text-slate-700 font-bold truncate">{{ $doc['label'] }}</span>
                                        @else
                                            <span class="w-4 h-4 rounded-full border-2 border-amber-300 bg-amber-50 flex items-center justify-center shrink-0">
                                                <i class="fas fa-clock text-[7px] text-amber-600"></i>
                                            </span>
                                            <span class="text-slate-500 font-medium truncate">{{ $doc['label'] }}</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Optional + invoices group --}}
                    @if(count($optionalKeys) > 0 || $hasInvoices)
                        <div class="mb-4">
                            <h4 class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">{{ __('fs_checklist_optional') ?: 'فواتير ومستندات إضافية' }}</h4>
                            <ul class="space-y-1.5">
                                @foreach($this->optionalDocs as $key => $label)
                                    @php $isUploaded = ($filesByKey->get($key) ?? collect())->isNotEmpty(); @endphp
                                    <li wire:key="fs-checklist-opt-{{ $key }}" class="flex items-center gap-2 text-[12px]">
                                        @if($isUploaded)
                                            <span class="w-4 h-4 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0">
                                                <i class="fas fa-check text-[8px]"></i>
                                            </span>
                                            <span class="text-slate-700 font-bold truncate">{{ $label }}</span>
                                        @else
                                            <span class="w-4 h-4 rounded-full border border-slate-200 bg-white flex items-center justify-center shrink-0">
                                                <i class="fas fa-minus text-[7px] text-slate-300"></i>
                                            </span>
                                            <span class="text-slate-400 font-medium truncate">{{ $label }}</span>
                                        @endif
                                    </li>
                                @endforeach
                                @if($hasInvoices || true)
                                    <li class="flex items-center gap-2 text-[12px]">
                                        @if($hasInvoices)
                                            <span class="w-4 h-4 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0">
                                                <i class="fas fa-check text-[8px]"></i>
                                            </span>
                                            <span class="text-slate-700 font-bold truncate">{{ __('label_invoices_group') ?: 'الفواتير' }}</span>
                                        @else
                                            <span class="w-4 h-4 rounded-full border border-slate-200 bg-white flex items-center justify-center shrink-0">
                                                <i class="fas fa-minus text-[7px] text-slate-300"></i>
                                            </span>
                                            <span class="text-slate-400 font-medium truncate">{{ __('label_invoices_group') ?: 'الفواتير' }}</span>
                                        @endif
                                    </li>
                                @endif
                            </ul>
                        </div>
                    @endif

                    {{-- Final outputs — hidden until the request reaches an approved/final stage. --}}
                    @php
                        $finalVisibleStatuses = [
                            'internal_approved', 'moci_approved',
                            'approved', 'completed', 'closed',
                        ];
                        $showFinalSection = in_array(strtolower((string) $request->status), $finalVisibleStatuses, true);
                    @endphp
                    @if($showFinalSection)
                        <div>
                            <h4 class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">{{ __('fs_checklist_final') ?: 'الملفات النهائية' }}</h4>
                            <div class="flex items-center gap-2 text-[12px]">
                                @if($hasFinal)
                                    <span class="w-4 h-4 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0">
                                        <i class="fas fa-check text-[8px]"></i>
                                    </span>
                                    <span class="text-slate-700 font-bold">{{ __('fs_checklist_final_ready') ?: 'الملفات النهائية متاحة للتحميل' }}</span>
                                @else
                                    <span class="w-4 h-4 rounded-full border border-slate-200 bg-white flex items-center justify-center shrink-0">
                                        <i class="fas fa-hourglass-half text-[7px] text-slate-400"></i>
                                    </span>
                                    <span class="text-slate-500 font-medium">{{ __('fs_checklist_final_pending') ?: 'سيتم توفير الملفات النهائية بعد اكتمال المراجعة' }}</span>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if($missingRequired->isNotEmpty() && ! $isRejectedFlow)
                        {{-- Still gathering documents — friendly amber banner. --}}
                        <div class="mt-4 pt-4 border-t border-slate-100 bg-amber-50/40 -mx-6 -mb-6 px-6 py-3 rounded-b-2xl">
                            <p class="text-[11px] font-bold text-amber-800 flex items-start gap-1.5">
                                <i class="fas fa-circle-exclamation text-amber-600 mt-0.5 shrink-0"></i>
                                <span>{{ __('fs_checklist_hint_required') ?: 'يرجى رفع المستندات الأساسية المتبقية لإكمال الطلب.' }}</span>
                            </p>
                        </div>
                    @elseif($requiredCount > 0 && $missingRequired->isEmpty() && ! $isRejectedFlow)
                        {{-- All required docs uploaded — show next-step banner per current status. --}}
                        @if($this->isAwaitingClientUpload)
                            {{-- Ready to submit: clear "all set" message + primary CTA. --}}
                            <div class="mt-5 pt-4 border-t border-slate-100 -mx-6 -mb-6 px-6 py-4 rounded-b-2xl bg-emerald-50/40">
                                <div class="flex items-start gap-2 mb-3">
                                    <span class="w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0 mt-0.5">
                                        <i class="fas fa-check text-[10px]"></i>
                                    </span>
                                    <div>
                                        <p class="text-[13px] font-black text-emerald-800 leading-tight">{{ __('fs_submit_ready_title') ?: 'المستندات مكتملة' }}</p>
                                        <p class="text-[11px] text-emerald-700 font-medium mt-1 leading-snug">
                                            {{ __('fs_submit_ready_desc') ?: 'يمكنك الآن إرسال الطلب لفريق المراجعة.' }}
                                        </p>
                                    </div>
                                </div>
                                <button type="button"
                                        wire:click="submitForReview"
                                        wire:loading.attr="disabled"
                                        wire:target="submitForReview"
                                        class="w-full py-3 bg-[#1FA7A2] hover:bg-[#167F7B] text-white text-sm font-black rounded-xl shadow-lg shadow-[#1FA7A2]/20 hover:shadow-xl transition-all flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed">
                                    <span wire:loading.remove wire:target="submitForReview">
                                        <i class="fas fa-paper-plane me-1"></i>
                                        {{ __('fs_submit_btn') ?: 'إرسال الطلب للمراجعة' }}
                                    </span>
                                    <span wire:loading wire:target="submitForReview">
                                        <i class="fas fa-circle-notch fa-spin me-1"></i>
                                        {{ __('fs_submit_btn_sending') ?: 'جاري الإرسال…' }}
                                    </span>
                                </button>
                            </div>
                        @else
                            {{-- Submitted: confirmation card explaining what happens next. --}}
                            <div class="mt-5 pt-4 border-t border-slate-100 -mx-6 -mb-6 px-6 py-4 rounded-b-2xl bg-[#1FA7A2]/5">
                                <div class="flex items-start gap-2">
                                    <span class="w-6 h-6 rounded-full bg-[#1FA7A2] text-white flex items-center justify-center shrink-0 mt-0.5">
                                        <i class="fas fa-circle-check text-[10px]"></i>
                                    </span>
                                    <div>
                                        <p class="text-[13px] font-black text-[#1FA7A2] leading-tight">
                                            {{ __('fs_submitted_title') ?: 'تم إرسال الطلب للمراجعة' }}
                                        </p>
                                        <p class="text-[11px] text-[#1FA7A2]/80 font-medium mt-1 leading-snug">
                                            {{ __('fs_submitted_desc') ?: 'فريقنا يراجع المستندات الآن، وسنحدّث حالة الطلب عند الانتهاء.' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2 mb-4">
                        <i class="fas fa-info-circle text-[#1FA7A2]"></i> {{ __('company_info') }}
                    </h3>

                    <div class="space-y-0 divide-y divide-slate-100 bg-slate-50 rounded-xl border border-slate-200">
                        <div class="flex justify-between items-center p-3">
                            <span class="text-xs text-slate-500">{{ __('cr_number') }}</span>
                            <span class="text-sm font-bold text-slate-800 font-mono">{{ $request->cr_number }}</span>
                        </div>
                        <div class="flex justify-between items-center p-3">
                            <span class="text-xs text-slate-500">{{ __('label_fiscal_year') }}</span>
                            <span class="text-sm font-bold text-slate-800 font-mono">{{ $request->fiscal_year }}</span>
                        </div>
                        <div class="flex justify-between items-center p-3">
                            <span class="text-xs text-slate-500">{{ __('last_update') }}</span>
                            <span class="text-xs font-bold text-slate-400 font-mono">{{ $request->updated_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2 mb-4">
                        <i class="fas fa-sticky-note text-[#1FA7A2]"></i> {{ __('your_notes_header') }}
                    </h3>
                    <div class="relative">
                        <textarea wire:model.defer="notes" rows="4" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm focus:border-[#1FA7A2] focus:ring-[#1FA7A2]/10 transition-all resize-none placeholder:text-slate-400" placeholder="{{ __('placeholder_add_notes') }}"></textarea>
                        <button wire:click="saveNotes" class="mt-2 w-full py-2 bg-slate-800 text-white text-xs font-bold rounded-lg hover:bg-slate-700 transition-colors">
                            <span wire:loading.remove wire:target="saveNotes">{{ __('btn_save_notes') }}</span>
                            <span wire:loading wire:target="saveNotes"><i class="fas fa-spinner fa-spin"></i></span>
                        </button>
                    </div>
                </div>

                @if($this->isStaff)
                    {{-- Light premium AMR7 card for staff-only actions. The card was previously dark (slate-900) which clashed with the client portal's light theme. --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-[#1FA7A2] blur-3xl opacity-[0.04] rounded-full pointer-events-none"></div>

                        <h3 class="font-black text-slate-800 flex items-center gap-2 mb-1 relative z-10">
                            <span class="w-8 h-8 rounded-lg bg-[#1FA7A2]/10 flex items-center justify-center text-[#1FA7A2]">
                                <i class="fas fa-user-shield text-sm"></i>
                            </span>
                            إجراءات داخلية للموظف
                        </h3>
                        <p class="text-[11px] text-slate-400 font-medium mb-4 relative z-10">يُستخدم هذا القسم من فريق آمر سبعة لإدارة حالة الطلب.</p>

                        <div class="space-y-4 relative z-10">
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-widest">{{ __('change_status_label') }}</label>
                                <select wire:model="new_status" class="w-full bg-slate-50 border border-slate-200 focus:border-[#1FA7A2] focus:ring-4 focus:ring-[#1FA7A2]/10 rounded-xl px-4 py-2.5 text-slate-800 font-bold text-sm outline-none transition-all">
                                    <option value="new">{{ __('status_new') }}</option>
                                    <option value="waiting_docs">{{ __('status_waiting_docs') }}</option>
                                    <option value="in_review">{{ __('status_in_review') }}</option>
                                    <option value="client_approval">{{ __('status_client_approval') }}</option>
                                    <option value="completed">{{ __('status_completed') }}</option>
                                    <option value="closed">{{ __('status_closed') }}</option>
                                    <option value="cancelled">{{ __('cancelled') }}</option>
                                </select>
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-widest">{{ __('admin_notes_label') }}</label>
                                <textarea wire:model.defer="admin_notes" rows="3" class="w-full bg-slate-50 border border-slate-200 focus:border-[#1FA7A2] focus:ring-4 focus:ring-[#1FA7A2]/10 rounded-xl px-4 py-2.5 text-slate-800 text-sm outline-none transition-all placeholder:text-slate-400" placeholder="{{ __('admin_notes_placeholder') }}"></textarea>
                            </div>

                            <button wire:click="staffUpdate" class="w-full py-2.5 bg-[#1FA7A2] hover:bg-[#167F7B] text-white text-sm font-bold rounded-xl transition-colors shadow-lg shadow-[#1FA7A2]/20 disabled:opacity-50 disabled:cursor-not-allowed" wire:loading.attr="disabled" wire:target="staffUpdate">
                                <span wire:loading.remove wire:target="staffUpdate">{{ __('btn_update_data') }}</span>
                                <span wire:loading wire:target="staffUpdate"><i class="fas fa-spinner fa-spin"></i></span>
                            </button>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #e2e8f0; border-radius: 20px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background-color: #cbd5e1; }
    </style>
</div>
