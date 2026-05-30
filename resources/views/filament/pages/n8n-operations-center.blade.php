<x-filament-panels::page>
    @php
        $health = $this->health();
        $statusClasses = match ($health['status'] ?? 'disabled') {
            'healthy' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'failed' => 'bg-red-50 text-red-700 ring-red-200',
            'not_configured' => 'bg-amber-50 text-amber-700 ring-amber-200',
            default => 'bg-slate-50 text-slate-700 ring-slate-200',
        };
    @endphp

    <div dir="rtl" class="space-y-8">
        @if (($health['status'] ?? 'disabled') === 'disabled')
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-amber-900">
                <div class="flex items-start gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 6a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 6zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                    </svg>
                    <div class="space-y-1">
                        <p class="font-bold">n8n معطّل من بيئة التشغيل.</p>
                        <p class="text-sm leading-7">السبب: <span class="font-mono">N8N_ENABLED=false</span>. اختبار الاتصال وأزرار التشغيل التجريبي ستبقى مقفلة حتى يتم تفعيله من ملف <span class="font-mono">.env</span> ثم <span class="font-mono">php artisan config:clear</span> على الخادم. راجع <span class="font-mono">docs/n8n/README.md</span> لمعرفة خطوات التفعيل الآمنة.</p>
                    </div>
                </div>
            </div>
        @elseif (($health['status'] ?? '') === 'not_configured')
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-amber-900">
                <p class="font-bold">n8n مفعّل لكنه غير مهيّأ.</p>
                <p class="mt-1 text-sm leading-7">{{ $health['message'] ?? '' }} راجع <span class="font-mono">docs/n8n/healthz-activation.md</span> لخطوات التفعيل الآمنة.</p>
            </div>
        @elseif (($health['status'] ?? '') === 'failed')
            <div class="rounded-2xl border border-red-200 bg-red-50 p-5 text-red-900">
                <p class="font-bold">إعدادات n8n غير مكتملة.</p>
                <p class="mt-1 text-sm leading-7">{{ $health['message'] ?? '' }}</p>
            </div>
        @endif

        <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                <div class="space-y-2">
                    <p class="text-sm font-semibold text-teal-700">Amr 7 n8n Operations Center</p>
                    <h1 class="text-2xl font-bold text-slate-950">مركز التشغيل الآلي</h1>
                    <p class="max-w-3xl text-sm leading-7 text-slate-600">
                        إدارة تشغيل workflows للعقود والمحتوى والتذاكر والوثائق بدون عرض أي أسرار أو مفاتيح. التشغيل اليدوي يتوقف تلقائيًا إذا لم تكن إعدادات n8n مفعلة.
                    </p>
                </div>

                <div class="rounded-xl px-4 py-3 text-sm font-semibold ring-1 {{ $statusClasses }}">
                    {{ $health['label'] ?? 'غير مفعّل' }}
                </div>
            </div>

            <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs leading-6 text-slate-500">
                    اختبار الاتصال يستخدم <span class="font-mono">/healthz</span> فقط ولا يشغّل أي workflow.
                </p>
                <button
                    type="button"
                    wire:click="probeSandbox"
                    wire:loading.attr="disabled"
                    wire:target="probeSandbox"
                    @disabled(in_array(($health['status'] ?? 'disabled'), ['disabled', 'not_configured'], true))
                    title="{{ match ($health['status'] ?? 'disabled') {
                        'disabled' => 'اختبار الاتصال متاح فقط حين يكون N8N_ENABLED=true.',
                        'not_configured' => 'حدّد N8N_BASE_URL في .env ثم نفّذ php artisan config:clear.',
                        default => 'يستدعي /healthz فقط.',
                    } }}"
                    class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-300 disabled:text-slate-600"
                >
                    <span wire:loading.remove wire:target="probeSandbox">اختبار اتصال n8n</span>
                    <span wire:loading wire:target="probeSandbox">جاري الاختبار...</span>
                </button>
            </div>

            @php $lastSuccess = $this->lastSuccessfulRun(); @endphp
            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                <div class="rounded-xl bg-slate-50 p-4 ring-1 ring-slate-200">
                    <div class="text-xs font-semibold text-slate-500">حالة الربط</div>
                    <div class="mt-2 text-lg font-bold text-slate-950">{{ $health['label'] ?? 'غير مفعّل' }}</div>
                    <div class="mt-1 text-xs leading-6 text-slate-500">{{ $health['message'] ?? '' }}</div>
                </div>

                <div class="rounded-xl bg-slate-50 p-4 ring-1 ring-slate-200">
                    <div class="text-xs font-semibold text-slate-500">Workflows مفعّلة</div>
                    <div class="mt-2 text-lg font-bold text-slate-950">{{ $health['enabled_workflows'] ?? 0 }} / {{ $health['total_workflows'] ?? 0 }}</div>
                </div>

                <div class="rounded-xl bg-slate-50 p-4 ring-1 ring-slate-200">
                    <div class="text-xs font-semibold text-slate-500">إخفاقات آخر 24 ساعة</div>
                    <div class="mt-2 text-lg font-bold text-slate-950">{{ $this->failuresLast24Hours() }}</div>
                </div>

                <div class="rounded-xl bg-slate-50 p-4 ring-1 ring-slate-200">
                    <div class="text-xs font-semibold text-slate-500">آخر تشغيل ناجح</div>
                    <div class="mt-2 text-sm font-bold text-slate-900">{{ $lastSuccess?->created_at?->diffForHumans() ?? 'لا يوجد' }}</div>
                    <div class="mt-1 text-xs leading-6 text-slate-500">{{ $lastSuccess?->workflow_name ?? '—' }}</div>
                </div>

                <div class="rounded-xl bg-slate-50 p-4 ring-1 ring-slate-200">
                    <div class="text-xs font-semibold text-slate-500">حفظ الأسرار</div>
                    <div class="mt-2 text-sm font-bold text-teal-700">محجوبة من الواجهة والـ logs</div>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-teal-200 bg-teal-50 p-5 text-teal-900">
            <div class="flex items-start gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/>
                </svg>
                <p class="text-sm font-semibold leading-7">
                    لا يتم تشغيل أي workflow إلا إذا كان مفعّلًا من <span class="font-mono">.env</span>. كل سير عمل يبقى مقفلًا افتراضيًا، والتشغيل التجريبي يستخدم بيانات آمنة فقط ولا يلمس أي ملفات أو بيانات عملاء حقيقية.
                </p>
            </div>
        </section>

        @foreach ($this->groupedWorkflows() as $category => $workflows)
            <section class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-slate-950">{{ $this->categoryLabel($category) }}</h2>
                    <span class="rounded-full bg-teal-50 px-3 py-1 text-xs font-semibold text-teal-700 ring-1 ring-teal-100">
                        {{ count($workflows) }} workflows
                    </span>
                </div>

                <div class="grid gap-4 lg:grid-cols-2">
                    @foreach ($workflows as $workflow)
                        @php
                            $stats = $this->workflowStats($workflow['key']);
                            $lastRun = $stats['last_run'] ?? null;
                            $runnable = $this->isWorkflowRunnable($workflow['key']);
                            $lastStatus = $lastRun?->status ?? null;
                            $lastStatusClasses = match ($lastStatus) {
                                'success' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                                'failed' => 'bg-red-50 text-red-700 ring-red-200',
                                'disabled' => 'bg-slate-50 text-slate-700 ring-slate-200',
                                default => 'bg-amber-50 text-amber-700 ring-amber-200',
                            };
                            $disabledReason = null;
                            if (! $runnable) {
                                $globalStatus = $health['status'] ?? 'disabled';
                                if ($globalStatus === 'disabled') {
                                    $disabledReason = 'سبب القفل: n8n معطّل (N8N_ENABLED=false).';
                                } elseif ($globalStatus === 'not_configured') {
                                    $disabledReason = 'سبب القفل: n8n مفعّل لكن N8N_BASE_URL غير مضبوط.';
                                } elseif (! ($workflow['enabled'] ?? false)) {
                                    $disabledReason = 'سبب القفل: ' . ($workflow['disabled_reason'] ?? 'هذا الـ workflow غير مفعّل من .env. الوضع الإنتاجي يبقى مغلقًا حتى تأذن.');
                                } elseif (empty($workflow['webhook_path'])) {
                                    $disabledReason = 'سبب القفل: مسار webhook غير مضبوط.';
                                } elseif (($workflow['requires_file'] ?? false) === true) {
                                    $disabledReason = 'سبب القفل: هذا الـ workflow يحتاج ملفًا حقيقيًا. التشغيل التجريبي معطّل لعدم وجود ملف تجريبي آمن — يُشغَّل من تدفق رفع الوثائق فقط.';
                                } else {
                                    $disabledReason = 'سبب القفل: تحقق من إعدادات الاتصال العامة.';
                                }
                            }
                        @endphp

                        <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <h3 class="text-base font-bold text-slate-950">{{ $workflow['name_ar'] }}</h3>
                                    <p class="mt-2 text-sm leading-7 text-slate-600">{{ $workflow['description'] }}</p>
                                    <div class="mt-3 font-mono text-xs text-slate-500">{{ $workflow['key'] }}</div>
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-semibold text-slate-600">{{ $this->categoryLabel($workflow['category'] ?? '') }}</span>
                                        @if (($workflow['gated'] ?? false) === true)
                                            <span class="rounded-full bg-amber-50 px-2.5 py-0.5 text-[11px] font-semibold text-amber-700 ring-1 ring-amber-100" title="يستهلك حصة الذكاء الاصطناعي عند التشغيل.">يستهلك حصة AI</span>
                                        @endif
                                        @if (($workflow['requires_file'] ?? false) === true)
                                            <span class="rounded-full bg-sky-50 px-2.5 py-0.5 text-[11px] font-semibold text-sky-700 ring-1 ring-sky-100" title="يحتاج ملفًا مرفوعًا؛ لا تشغيل تجريبي.">يحتاج ملف</span>
                                        @endif
                                    </div>
                                </div>

                                <span class="shrink-0 rounded-full px-3 py-1 text-xs font-semibold ring-1 {{ ($workflow['enabled'] ?? false) ? 'bg-teal-50 text-teal-700 ring-teal-100' : 'bg-slate-50 text-slate-600 ring-slate-200' }}">
                                    {{ ($workflow['enabled'] ?? false) ? 'مفعّل' : 'غير مفعّل' }}
                                </span>
                            </div>

                            <div class="mt-5 grid gap-3 sm:grid-cols-3">
                                <div class="rounded-xl bg-slate-50 p-3 ring-1 ring-slate-200">
                                    <div class="text-xs text-slate-500">آخر تشغيل</div>
                                    <div class="mt-1 text-sm font-semibold text-slate-900">
                                        {{ $lastRun?->created_at?->diffForHumans() ?? 'لا يوجد' }}
                                    </div>
                                </div>
                                <div class="rounded-xl bg-slate-50 p-3 ring-1 ring-slate-200">
                                    <div class="text-xs text-slate-500">نجاحات</div>
                                    <div class="mt-1 text-sm font-semibold text-emerald-700">{{ $stats['successes'] }}</div>
                                </div>
                                <div class="rounded-xl bg-slate-50 p-3 ring-1 ring-slate-200">
                                    <div class="text-xs text-slate-500">إخفاقات</div>
                                    <div class="mt-1 text-sm font-semibold text-red-700">{{ $stats['failures'] }}</div>
                                </div>
                            </div>

                            <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div class="space-y-1">
                                    <span class="inline-flex w-fit rounded-full px-3 py-1 text-xs font-semibold ring-1 {{ $lastStatusClasses }}">
                                        آخر حالة: {{ $lastStatus ? $this->statusLabel($lastStatus) : 'لا يوجد' }}
                                    </span>
                                    @if ($disabledReason)
                                        <p class="max-w-xs text-xs leading-6 text-slate-500">{{ $disabledReason }}</p>
                                    @endif
                                </div>

                                <button
                                    type="button"
                                    wire:click="trigger('{{ $workflow['key'] }}')"
                                    wire:confirm="تشغيل تجريبي لـ {{ $workflow['name_ar'] ?? $workflow['key'] }} الآن؟"
                                    @disabled(! $runnable)
                                    title="{{ $disabledReason ?? 'يطلق هذا الـ workflow على n8n الآن.' }}"
                                    class="inline-flex items-center justify-center rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-700 disabled:cursor-not-allowed disabled:bg-slate-300 disabled:text-slate-600"
                                >
                                    تشغيل تجريبي
                                </button>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endforeach

        <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-bold text-slate-950">آخر السجلات</h2>
                <span class="text-xs font-semibold text-slate-500">n8n_workflow_runs</span>
            </div>

            <div class="overflow-hidden rounded-xl ring-1 ring-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-right text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Workflow</th>
                            <th class="px-4 py-3">التصنيف</th>
                            <th class="px-4 py-3">الحالة</th>
                            <th class="px-4 py-3">المدة</th>
                            <th class="px-4 py-3">الوقت</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($this->recentRuns() as $run)
                            <tr>
                                <td class="px-4 py-3 font-semibold text-slate-900">{{ $run['workflow_name'] ?? $run['workflow_key'] }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $this->categoryLabel($run['category'] ?? '') }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $this->statusLabel($run['status'] ?? '') }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $run['duration_ms'] ?? 'غير متوفر' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ isset($run['created_at']) ? \Illuminate\Support\Carbon::parse($run['created_at'])->diffForHumans() : 'غير متوفر' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500">
                                    لا توجد سجلات بعد. إذا لم يتم تشغيل migration، سيبقى هذا القسم فارغًا حتى إنشاء الجدول.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-filament-panels::page>
