<div class="p-6 animate__animated animate__fadeIn font-['Tajawal']" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

    {{-- Polish: header مدمج --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-5">
        <div>
            <h2 class="text-lg font-black text-[#0A2540]">{{ __('ai_review_title') }}</h2>
            <p class="text-xs text-slate-500 font-medium mt-0.5">{{ __('ai_review_subtitle') }}</p>
        </div>
        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-amber-50 text-amber-700 text-[11px] font-bold border border-amber-100">
            <i class="fas fa-robot text-[10px]"></i>
            {{ __('ai_review_pending_count', ['count' => $extractions->count()]) }}
        </span>
    </div>

    {{-- Polish: تحذير دائم — لن يتم تحديث ملف المنشأة إلا بعد اعتماد البيانات --}}
    @if(! $extractions->isEmpty())
        <div class="bg-amber-50 border border-amber-100 rounded-xl px-4 py-2.5 mb-4 flex items-start gap-2 text-[12px] text-amber-800 font-medium">
            <i class="fas fa-triangle-exclamation text-amber-600 mt-0.5 shrink-0"></i>
            <span>{{ __('ai_review_warning') ?: 'لن يتم تحديث ملف المنشأة إلا بعد اعتماد البيانات يدويًا.' }}</span>
        </div>
    @endif

    @if($extractions->isEmpty())
        <div class="py-16 px-6 text-center bg-white rounded-2xl border border-dashed border-slate-200">
            <div class="w-16 h-16 rounded-2xl bg-[#1FA7A2]/5 flex items-center justify-center text-[#1FA7A2] mx-auto mb-4">
                <i class="fas fa-magic text-2xl"></i>
            </div>
            <h3 class="text-base font-black text-slate-800 mb-1">{{ __('ai_review_empty_title') }}</h3>
            <p class="text-xs text-slate-500 font-medium max-w-md mx-auto">{{ __('ai_review_empty_hint') }}</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($extractions as $extraction)
                @php
                    // Phase 2: حسبة موحَّدة قبل الـheader — نمنع ظهور badge أخضر مزعج لحالة فاشلة.
                    $fields = data_get($extraction->extracted_json, 'fields', []);
                    $overallConf = $extraction->confidence_score !== null ? (float) $extraction->confidence_score : null;
                    $nonEmptyFields = collect($fields)->filter(function ($payload) {
                        $v = is_array($payload) ? ($payload['value'] ?? null) : $payload;
                        return $v !== null && $v !== '' && trim((string) $v) !== '';
                    })->all();
                    $isLowConfidence = $overallConf !== null && $overallConf < 0.5;
                    $hasAnyValue = ! empty($nonEmptyFields);
                    $docTypeLabel = $this->translateAiField((string) ($extraction->document_type ?: ''));
                    if ($docTypeLabel === '' || $docTypeLabel === ($extraction->document_type ?: '')) {
                        // fallback نصي عربي بدلاً من إرجاع raw key
                        $docTypeLabel = $extraction->document_type ? $extraction->document_type : '—';
                    }
                @endphp
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5" wire:key="extraction-{{ $extraction->id }}">
                    {{-- Header الـ extraction --}}
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-3 mb-4 pb-3 border-b border-slate-100">
                        <div class="min-w-0">
                            <h4 class="font-black text-[#0A2540] text-base mb-0.5 truncate">
                                {{ $docTypeLabel }}
                            </h4>
                            <p class="text-[11px] text-slate-400 font-medium">
                                {{ __('uploaded_at') }}: {{ optional($extraction->created_at)->diffForHumans() }}
                            </p>
                        </div>
                        {{-- Phase 2: نُخفي الـbadge الكبير في حالة الـlow-confidence — البطاقة السفلية تتولّى التوضيح --}}
                        @if($overallConf !== null && ! $isLowConfidence && $hasAnyValue)
                            @php
                                $low  = (float) config('ai.confidence_thresholds.low', 0.7);
                                $med  = (float) config('ai.confidence_thresholds.medium', 0.9);
                                $color = $overallConf < $low ? 'bg-amber-50 text-amber-700 border-amber-100' : ($overallConf < $med ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-emerald-50 text-emerald-700 border-emerald-100');
                            @endphp
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold border {{ $color }}">
                                <i class="fas fa-gauge-high text-[10px]"></i>
                                {{ __('confidence') }}: {{ number_format($overallConf * 100, 1) }}%
                            </span>
                        @endif
                    </div>

                    @if($isLowConfidence || ! $hasAnyValue)
                        {{-- Polish: حالة الثقة المنخفضة — رسالة واضحة بدلاً من جدول مكسّر --}}
                        <div class="bg-rose-50 border border-rose-100 rounded-2xl p-5 flex items-start gap-3" x-cloak>
                            <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                                <i class="fas fa-triangle-exclamation"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <h5 class="text-sm font-black text-rose-900 mb-1">{{ __('ai_low_confidence_title') ?: 'لم يتم التعرف على بيانات كافية من الوثيقة' }}</h5>
                                <p class="text-[12px] text-rose-700 font-medium leading-relaxed">
                                    {{ __('ai_low_confidence_hint') ?: 'قد تكون الوثيقة غير واضحة أو من نوع غير مدعوم. حاول رفع نسخة أوضح من نفس الوثيقة، أو املأ البيانات يدويًا من شاشة ملف المنشأة.' }}
                                </p>
                                @if($overallConf !== null)
                                    <p class="text-[10px] text-rose-500 font-mono mt-1">
                                        {{ __('confidence') }}: {{ number_format($overallConf * 100, 1) }}%
                                    </p>
                                @endif
                            </div>
                        </div>
                    @else
                        @php
                            // Phase 8: confidence-based field highlighting.
                            // - overall < 0.85 → moderate confidence: orange ring + warning above
                            // - field-level confidence (if n8n provided) wins over overall
                            $moderateOverall = $overallConf !== null && $overallConf < 0.85;
                        @endphp

                        @if($moderateOverall)
                            {{-- Phase 8: top warning when overall confidence is moderate (0.5 ≤ conf < 0.85) --}}
                            <div class="bg-orange-50 border border-orange-200 rounded-xl px-3 py-2.5 mb-3 flex items-start gap-2 text-[12px]">
                                <i class="fas fa-triangle-exclamation text-orange-500 mt-0.5 shrink-0"></i>
                                <div class="min-w-0 flex-1">
                                    <p class="font-bold text-orange-800">{{ __('ai_moderate_confidence_title') ?: 'الثقة منخفضة — راجع الحقول التالية قبل الاعتماد' }}</p>
                                    <p class="text-[11px] text-orange-700 font-medium mt-0.5">{{ __('ai_moderate_confidence_hint') ?: 'الحقول المظللة بالبرتقالي تحتاج إلى مراجعة دقيقة قبل اعتماد البيانات.' }}</p>
                                </div>
                            </div>
                        @endif

                        {{-- Fields: عرض الحقول التي تم استخراجها فعليًا فقط --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach($nonEmptyFields as $key => $payload)
                                @php
                                    $fieldConfidence = is_array($payload) && isset($payload['confidence']) ? (float) $payload['confidence'] : null;
                                    // Phase 8: prefer field-level confidence, fall back to overall.
                                    $effectiveConf = $fieldConfidence ?? $overallConf;
                                    $isModerate    = $effectiveConf !== null && $effectiveConf < 0.85;
                                    $fieldDot      = $effectiveConf === null
                                        ? 'bg-slate-300'
                                        : ($effectiveConf < 0.7 ? 'bg-orange-400' : ($effectiveConf < 0.9 ? 'bg-amber-400' : 'bg-emerald-500'));
                                @endphp
                                <div class="space-y-1">
                                    <div class="flex items-center justify-between gap-2">
                                        <label class="text-[11px] font-bold text-slate-600">{{ $this->translateAiField((string) $key) }}</label>
                                        @if($effectiveConf !== null)
                                            <span class="flex items-center gap-1 text-[9px] font-mono text-slate-400">
                                                <span class="w-1.5 h-1.5 rounded-full {{ $fieldDot }}"></span>
                                                {{ number_format($effectiveConf * 100, 0) }}%
                                            </span>
                                        @endif
                                    </div>
                                    @if($editingId === $extraction->id)
                                        <input type="text"
                                               wire:model="overrides.{{ $key }}"
                                               class="w-full {{ $isModerate ? 'bg-orange-50 border-orange-200 focus:border-orange-400 focus:ring-orange-200' : 'bg-slate-50 border-slate-200 focus:border-[#0A2540] focus:ring-[#0A2540]/10' }} border focus:ring-2 rounded-xl px-3 py-2 font-medium text-sm outline-none">
                                    @else
                                        <div class="font-bold text-slate-800 text-sm rounded-xl px-3 py-2 truncate {{ $isModerate ? 'bg-orange-50 border border-orange-200 ring-1 ring-orange-100' : 'bg-slate-50 border border-slate-100' }}">
                                            {{ data_get($payload, 'value') ?: '—' }}
                                        </div>
                                        @if($isModerate)
                                            <p class="text-[10px] text-orange-600 font-bold mt-0.5 flex items-center gap-1">
                                                <i class="fas fa-circle-info text-[9px]"></i>
                                                {{ __('ai_field_low_confidence_hint') ?: 'الثقة منخفضة — راجع هذا الحقل قبل الاعتماد.' }}
                                            </p>
                                        @endif
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Footer actions: مختلف حسب الدور والثقة --}}
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 mt-5 pt-4 border-t border-slate-100">
                        <div class="text-[11px] font-bold text-slate-500 flex items-center gap-1.5 min-w-0">
                            @if($isLowConfidence || ! $hasAnyValue)
                                <i class="fas fa-info-circle text-rose-400"></i>
                                <span>{{ __('ai_action_hint_retry_or_reupload') ?: 'يمكنك إعادة التحليل أو رفع نسخة أوضح من الوثيقة.' }}</span>
                            @endif
                        </div>
                        <div class="flex flex-col md:flex-row md:items-center gap-2 md:flex-wrap md:justify-end">
                            @if($this->isBackoffice)
                                {{-- Backoffice --}}
                                @if($editingId === $extraction->id)
                                    <button type="button"
                                            wire:click="cancelEditing"
                                            class="px-5 py-2 rounded-xl text-slate-600 font-bold text-sm hover:bg-slate-50 border border-slate-200">
                                        {{ __('modal_cancel') }}
                                    </button>
                                    <button type="button"
                                            wire:click="approve({{ $extraction->id }})"
                                            wire:loading.attr="disabled"
                                            wire:target="approve"
                                            class="px-5 py-2 rounded-xl bg-[#0A2540] text-white font-bold text-sm hover:bg-[#0a2540]/90 disabled:opacity-50">
                                        <span wire:loading.remove wire:target="approve">{{ __('approve_extraction') }}</span>
                                        <span wire:loading wire:target="approve"><i class="fas fa-circle-notch fa-spin"></i></span>
                                    </button>
                                @else
                                    <button type="button"
                                            wire:click="reject({{ $extraction->id }})"
                                            wire:confirm="{{ __('confirm_reject_extraction') }}"
                                            class="px-4 py-2 rounded-xl bg-rose-50 text-rose-600 font-bold text-xs hover:bg-rose-100 border border-rose-100 inline-flex items-center gap-1.5">
                                        <i class="fas fa-circle-xmark text-[10px]"></i>
                                        {{ __('reject') }}
                                    </button>
                                    <button type="button"
                                            wire:click="retry({{ $extraction->id }})"
                                            wire:confirm="{{ __('confirm_retry_extraction') ?: 'إعادة التحليل ستستبدل النتيجة الحالية. هل تريد المتابعة؟' }}"
                                            wire:loading.attr="disabled"
                                            wire:target="retry"
                                            class="px-4 py-2 rounded-xl bg-amber-50 text-amber-700 font-bold text-xs hover:bg-amber-100 border border-amber-100 inline-flex items-center gap-1.5 disabled:opacity-50">
                                        <span wire:loading.remove wire:target="retry"><i class="fas fa-rotate text-[10px]"></i> {{ __('ai_retry_extraction') ?: 'إعادة التحليل' }}</span>
                                        <span wire:loading wire:target="retry"><i class="fas fa-circle-notch fa-spin"></i></span>
                                    </button>
                                    @if(! $isLowConfidence && $hasAnyValue)
                                        <button type="button"
                                                wire:click="startEditing({{ $extraction->id }})"
                                                class="px-5 py-2 rounded-xl bg-[#0A2540] text-white font-bold text-sm hover:bg-[#0a2540]/90 inline-flex items-center gap-1.5">
                                            <i class="fas fa-check text-[10px]"></i>
                                            {{ __('review_and_approve') }}
                                        </button>
                                    @endif
                                @endif
                            @else
                                {{-- Phase 7: العميل يعتمد البيانات بنفسه — لا حاجة لمراجعة الموظف.
                                     الزرار "أعتمد صحة البيانات" يتفعّل فقط بعد تأشير checkbox الإقرار. --}}
                                @if($isLowConfidence || ! $hasAnyValue)
                                    {{-- ثقة منخفضة: لا زر اعتماد بارز، فقط خيارات مساعدة --}}
                                    <a href="{{ route('dashboard') }}?section=compliance"
                                       class="px-4 py-2 rounded-xl bg-[#1FA7A2]/10 text-[#1FA7A2] font-bold text-xs hover:bg-[#1FA7A2]/20 border border-[#1FA7A2]/20 inline-flex items-center gap-1.5">
                                        <i class="fas fa-cloud-arrow-up text-[10px]"></i>
                                        {{ __('ai_reupload_document') ?: 'إعادة الرفع' }}
                                    </a>
                                    <button type="button"
                                            wire:click="startEditing({{ $extraction->id }})"
                                            class="px-4 py-2 rounded-xl bg-slate-50 text-[#0A2540] font-bold text-xs hover:bg-slate-100 border border-slate-200 inline-flex items-center gap-1.5">
                                        <i class="fas fa-pen text-[10px]"></i>
                                        {{ __('ai_edit_before_approve') ?: 'تعديل البيانات يدويًا' }}
                                    </button>
                                @elseif($editingId === $extraction->id)
                                    {{-- العميل في وضع التعديل قبل الاعتماد --}}
                                    <button type="button"
                                            wire:click="cancelEditing"
                                            class="px-4 py-2 rounded-xl text-slate-600 font-bold text-xs hover:bg-slate-50 border border-slate-200">
                                        {{ __('modal_cancel') }}
                                    </button>
                                    <label class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 cursor-pointer">
                                        <input type="checkbox" wire:model.live="clientAttestations.{{ $extraction->id }}" class="rounded border-slate-300 text-[#0A2540] focus:ring-[#0A2540]/20">
                                        <span class="text-[11px] font-bold text-slate-600">{{ __('client_attest_short') ?: 'أقر بصحة البيانات' }}</span>
                                    </label>
                                    <button type="button"
                                            wire:click="clientApprove({{ $extraction->id }})"
                                            wire:loading.attr="disabled" wire:target="clientApprove"
                                            @disabled(! ($clientAttestations[$extraction->id] ?? false))
                                            class="px-5 py-2 rounded-xl bg-[#0A2540] text-white font-bold text-sm hover:bg-[#0a2540]/90 disabled:opacity-40 disabled:cursor-not-allowed inline-flex items-center gap-1.5">
                                        <span wire:loading.remove wire:target="clientApprove"><i class="fas fa-check text-[10px]"></i> {{ __('client_approve_data') ?: 'أعتمد صحة البيانات' }}</span>
                                        <span wire:loading wire:target="clientApprove"><i class="fas fa-circle-notch fa-spin"></i></span>
                                    </button>
                                @else
                                    {{-- العرض الافتراضي: زر "تعديل قبل الاعتماد" + flow الاعتماد المباشر بـcheckbox --}}
                                    <a href="{{ route('dashboard') }}?section=compliance"
                                       class="px-4 py-2 rounded-xl bg-white text-slate-500 font-bold text-xs hover:bg-slate-50 border border-slate-200 inline-flex items-center gap-1.5">
                                        <i class="fas fa-cloud-arrow-up text-[10px]"></i>
                                        {{ __('ai_reupload_document') ?: 'إعادة الرفع' }}
                                    </a>
                                    <button type="button"
                                            wire:click="startEditing({{ $extraction->id }})"
                                            class="px-4 py-2 rounded-xl bg-slate-50 text-slate-700 font-bold text-xs hover:bg-slate-100 border border-slate-200 inline-flex items-center gap-1.5">
                                        <i class="fas fa-pen text-[10px]"></i>
                                        {{ __('ai_edit_before_approve') ?: 'تعديل قبل الاعتماد' }}
                                    </button>
                                    <label class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 cursor-pointer">
                                        <input type="checkbox" wire:model.live="clientAttestations.{{ $extraction->id }}" class="rounded border-slate-300 text-[#0A2540] focus:ring-[#0A2540]/20">
                                        <span class="text-[11px] font-bold text-slate-600">{{ __('client_attest_short') ?: 'أقر بصحة البيانات' }}</span>
                                    </label>
                                    <button type="button"
                                            wire:click="clientApprove({{ $extraction->id }})"
                                            wire:loading.attr="disabled" wire:target="clientApprove"
                                            @disabled(! ($clientAttestations[$extraction->id] ?? false))
                                            class="px-5 py-2 rounded-xl bg-[#0A2540] text-white font-bold text-sm hover:bg-[#0a2540]/90 disabled:opacity-40 disabled:cursor-not-allowed inline-flex items-center gap-1.5">
                                        <span wire:loading.remove wire:target="clientApprove"><i class="fas fa-check text-[10px]"></i> {{ __('client_approve_data') ?: 'أعتمد صحة البيانات' }}</span>
                                        <span wire:loading wire:target="clientApprove"><i class="fas fa-circle-notch fa-spin"></i></span>
                                    </button>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
