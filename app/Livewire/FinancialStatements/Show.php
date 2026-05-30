<?php

namespace App\Livewire\FinancialStatements;

use App\Models\FinancialStatementFile;
use App\Models\FinancialStatementMessage;
use App\Models\FinancialStatementRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Traits\HasSEO;

class Show extends Component
{
    use WithFileUploads, AuthorizesRequests, HasSEO;

    public FinancialStatementRequest $request;

    /**
     * Pending picks for required/optional single-file inputs.
     * Holds Livewire TemporaryUploadedFile until the user clicks "رفع الآن".
     * Keyed by file_key (e.g. 'commercial_register').
     */
    public array $docs = [];

    /**
     * Pending picks for multi-file groups (e.g. invoices).
     */
    public array $multiDocs = ['invoices' => []];

    public int $uploadVersion = 1;

    public string $message = '';
    public string $notes = '';
    public string $admin_notes = '';
    public string $new_status = '';

    /**
     * Required documents: stable list of [key => label]. Source of truth.
     */
    public function getRequiredDocsProperty(): array
    {
        return [
            'commercial_register'    => __('label_cr_file'),
            'incorporation_contract' => __('label_contract_file'),
            'owner_id'               => __('label_owner_id'),
        ];
    }

    /**
     * Short helper text shown under each required-doc card.
     */
    public function getRequiredDocsDescriptionsProperty(): array
    {
        return [
            'commercial_register'    => __('fs_doc_desc_cr')       ?: 'صورة واضحة من السجل التجاري (PDF أو صورة).',
            'incorporation_contract' => __('fs_doc_desc_contract') ?: 'عقد التأسيس الموثّق أو آخر تعديل (PDF).',
            'owner_id'               => __('fs_doc_desc_owner_id') ?: 'هوية المالك / المدير المسؤول (PDF أو صورة).',
        ];
    }

    public function getOptionalDocsProperty(): array
    {
        return [
            'trial_balance'     => __('label_trial_balance'),
            'general_ledger'    => __('label_general_ledger'),
            'bank_statement'    => __('label_bank_statement'),
            'vat_certificate'   => __('label_vat_certificate'),
            'zakat_certificate' => __('label_zakat_certificate'),
        ];
    }

    public function getMultiGroupsProperty(): array
    {
        return [
            'invoices' => __('label_invoices_group'),
        ];
    }

    public function mount(FinancialStatementRequest $request): void
    {
        $this->request = $request;
        $this->authorize('view', $this->request);

        $this->notes = (string) ($this->request->client_notes ?? '');
        $this->admin_notes = (string) ($this->request->admin_notes ?? '');
        $this->new_status = (string) ($this->request->status ?? '');

        $this->setSeo(
            __('request_details') . ' #' . ($this->request->public_id ?? $this->request->id) . ' | ' . __('Amr 7'),
            __('request_details_header')
        );
    }

    public function getIsStaffProperty(): bool
    {
        $u = Auth::user();
        if (!$u) return false;
        $role = strtolower((string) ($u->role ?? ''));
        return (bool) $u->is_admin || in_array($role, ['admin','agent','staff','employee','support'], true);
    }

    public function getStatusMetaProperty(): array
    {
        // Phase 8C: توسيع map ليغطي قيم moc_approval/moci_approval + المراحل الداخلية.
        // الـmap موحَّد مع FinancialStatementRequest accessor (يتقبّل الـlegacy "moci_approval").
        return match($this->request->status) {
            'new'                     => ['class' => 'bg-blue-50 text-blue-700 border-blue-100',       'label' => __('status_new'),               'icon' => 'fa-star'],
            'draft'                   => ['class' => 'bg-slate-50 text-slate-600 border-slate-200',    'label' => __('status_draft'),             'icon' => 'fa-pencil'],
            'pending'                 => ['class' => 'bg-slate-50 text-slate-600 border-slate-200',    'label' => __('status_pending'),           'icon' => 'fa-clock'],
            'waiting_docs'            => ['class' => 'bg-amber-50 text-amber-700 border-amber-100',    'label' => __('status_waiting_docs'),      'icon' => 'fa-clock'],
            'files_uploaded'          => ['class' => 'bg-sky-50 text-sky-700 border-sky-100',          'label' => __('status_files_uploaded'),    'icon' => 'fa-folder-open'],
            'in_review',
            'under_review'            => ['class' => 'bg-purple-50 text-purple-700 border-purple-100', 'label' => __('status_in_review'),         'icon' => 'fa-search'],
            'client_approval'         => ['class' => 'bg-indigo-50 text-indigo-700 border-indigo-100', 'label' => __('status_client_approval'),   'icon' => 'fa-user-check'],
            'internal_approved'       => ['class' => 'bg-teal-50 text-teal-700 border-teal-100',       'label' => __('status_internal_approved'), 'icon' => 'fa-circle-check'],
            'moc_approval',
            'moci_approval',
            'moci_pending'            => ['class' => 'bg-indigo-50 text-indigo-700 border-indigo-100', 'label' => __('status_moci_pending'),      'icon' => 'fa-building-columns'],
            'moci_approved'           => ['class' => 'bg-emerald-50 text-emerald-700 border-emerald-100', 'label' => __('status_moci_approved'),   'icon' => 'fa-stamp'],
            'approved'                => ['class' => 'bg-emerald-50 text-emerald-700 border-emerald-100', 'label' => __('status_approved'),       'icon' => 'fa-circle-check'],
            'completed'               => ['class' => 'bg-emerald-50 text-emerald-700 border-emerald-100', 'label' => __('status_completed'),      'icon' => 'fa-check-circle'],
            'closed'                  => ['class' => 'bg-slate-50 text-slate-600 border-slate-200',    'label' => __('status_closed'),            'icon' => 'fa-lock'],
            'rejected'                => ['class' => 'bg-rose-50 text-rose-700 border-rose-100',       'label' => __('status_rejected'),          'icon' => 'fa-times-circle'],
            'cancelled'               => ['class' => 'bg-rose-50 text-rose-700 border-rose-100',       'label' => __('cancelled'),                'icon' => 'fa-times-circle'],
            default                   => ['class' => 'bg-slate-50 text-slate-600 border-slate-200',    'label' => __('status_processing') ?: 'قيد المعالجة', 'icon' => 'fa-circle'],
        };
    }

    /**
     * Phase 8C: Visual Timeline (6 stages). يحوّل الـstatus الحالي إلى array من
     * المراحل، كل مرحلة فيها done/current/pending + label + timestamp إن كان
     * متوفّر من status_logs. لا يخترع بيانات.
     *
     * Stages (ترتيب موحَّد):
     *   1. created      — إنشاء الطلب (مكتمل دائماً، التاريخ من created_at)
     *   2. files        — رفع المستندات
     *   3. under_review — قيد مراجعة الفريق
     *   4. internal     — الاعتماد الداخلي
     *   5. moci         — اعتماد وزارة التجارة (MOCI)
     *   6. completed    — مكتمل
     *
     * يكشف rejected/cancelled كحالة خارج المسار بدون كسر الـtimeline.
     */
    public function getTimelineStepsProperty(): array
    {
        $status = strtolower((string) $this->request->status);

        // ترتيب رقمي لكل status على المسار. أي status غير معروف يقع في stage 0 (إنشاء).
        $statusIndex = [
            'draft'             => 0,
            'new'               => 0,
            'pending'           => 0,
            'waiting_docs'      => 1,
            'files_uploaded'    => 1,
            'in_review'         => 2,
            'under_review'      => 2,
            'client_approval'   => 2,
            'internal_approved' => 3,
            'approved'          => 3,
            'moc_approval'      => 4,
            'moci_approval'     => 4,
            'moci_pending'      => 4,
            'moci_approved'     => 5,
            'completed'         => 5,
            'closed'            => 5,
        ];

        // rejected / cancelled لا يقعون في الـtimeline العادي.
        $isRejected = in_array($status, ['rejected', 'cancelled'], true);
        $currentIdx = $isRejected ? -1 : ($statusIndex[$status] ?? 0);

        // محاولة جلب timestamps من status_logs (relation موجودة على Request).
        // لا نعتمد على وجودها — fallback إلى created_at لـstep 0 فقط.
        $logsByStatus = [];
        try {
            if (method_exists($this->request, 'statusLogs')) {
                foreach ($this->request->statusLogs as $log) {
                    $key = strtolower((string) ($log->to_status ?? ''));
                    if ($key !== '' && ! isset($logsByStatus[$key])) {
                        $logsByStatus[$key] = optional($log->created_at)->diffForHumans();
                    }
                }
            }
        } catch (\Throwable $e) {
            // statusLogs قد لا توجد أو schema قد لا تطابق — نتجاهل بأمان.
        }

        $resolveTimestamp = function (array $statusesForStage) use ($logsByStatus): ?string {
            foreach ($statusesForStage as $s) {
                if (isset($logsByStatus[$s])) {
                    return $logsByStatus[$s];
                }
            }
            return null;
        };

        $stages = [
            [
                'key'       => 'created',
                'idx'       => 0,
                'label'     => __('fs_timeline_created') ?: 'إنشاء الطلب',
                'icon'      => 'fa-circle-dot',
                'timestamp' => optional($this->request->created_at)->diffForHumans(),
            ],
            [
                'key'       => 'files',
                'idx'       => 1,
                'label'     => __('fs_timeline_files') ?: 'رفع المستندات',
                'icon'      => 'fa-folder-open',
                'timestamp' => $resolveTimestamp(['waiting_docs', 'files_uploaded']),
            ],
            [
                'key'       => 'under_review',
                'idx'       => 2,
                'label'     => __('fs_timeline_under_review') ?: 'قيد مراجعة الفريق',
                'icon'      => 'fa-search',
                'timestamp' => $resolveTimestamp(['in_review', 'under_review', 'client_approval']),
            ],
            [
                'key'       => 'internal',
                'idx'       => 3,
                'label'     => __('fs_timeline_internal_approved') ?: 'الاعتماد الداخلي',
                'icon'      => 'fa-circle-check',
                'timestamp' => $resolveTimestamp(['internal_approved', 'approved']),
            ],
            [
                'key'       => 'moci',
                'idx'       => 4,
                'label'     => __('fs_timeline_moci') ?: 'اعتماد وزارة التجارة',
                'icon'      => 'fa-building-columns',
                'timestamp' => $resolveTimestamp(['moc_approval', 'moci_approval', 'moci_pending', 'moci_approved']),
            ],
            [
                'key'       => 'completed',
                'idx'       => 5,
                'label'     => __('fs_timeline_completed') ?: 'مكتمل',
                'icon'      => 'fa-flag-checkered',
                'timestamp' => $resolveTimestamp(['completed', 'closed']),
            ],
        ];

        // تطبيق state على كل stage بناءً على المؤشر الحالي.
        return array_map(function ($stage) use ($currentIdx, $isRejected) {
            if ($isRejected) {
                $stage['state'] = 'rejected'; // الجميع يصبح neutral، الـRejected badge منفصل في الـheader.
            } elseif ($stage['idx'] < $currentIdx) {
                $stage['state'] = 'done';
            } elseif ($stage['idx'] === $currentIdx) {
                $stage['state'] = 'current';
            } else {
                $stage['state'] = 'pending';
            }
            return $stage;
        }, $stages);
    }

    /**
     * Phase 8C: حالة rejected/cancelled مرئياً.
     */
    public function getIsRejectedFlowProperty(): bool
    {
        return in_array(strtolower((string) $this->request->status), ['rejected', 'cancelled'], true);
    }

    public function saveNotes(): void
    {
        $this->authorize('view', $this->request);
        $this->validate(['notes' => 'nullable|string|max:4000']);
        $this->request->update(['client_notes' => $this->notes]);
        $this->dispatch('notify', message: __('msg_saved'), type: 'success');
    }

    /**
     * Unified snapshot of the three required documents — single source of truth
     * consumed by both upload cards and the sidebar checklist. Each entry has:
     *   required_key, label, description, uploaded (bool), file (Model|null),
     *   file_name, uploaded_at (Carbon|null), pending (bool).
     *
     * `pending` is true when the user has picked a file via the input but has
     * not clicked "رفع الآن" yet.
     */
    public function getRequiredDocumentsProperty(): array
    {
        $descriptions = $this->requiredDocsDescriptions;
        $requiredKeys = array_keys($this->requiredDocs);

        $files = $this->request->files()
            ->whereIn('file_key', $requiredKeys)
            ->latest()
            ->limit(200)
            ->get()
            ->groupBy('file_key');

        $out = [];
        foreach ($this->requiredDocs as $key => $label) {
            $file = $files->get($key)?->first();
            $out[$key] = [
                'required_key' => $key,
                'label'        => $label,
                'description'  => $descriptions[$key] ?? '',
                'uploaded'     => (bool) $file,
                'file'         => $file,
                'file_name'    => $file?->original_name,
                'uploaded_at'  => $file?->created_at,
                'pending'      => isset($this->docs[$key]) && $this->docs[$key] !== null,
            ];
        }

        return $out;
    }

    public function getRequiredDocumentsTotalProperty(): int
    {
        return count($this->requiredDocs);
    }

    public function getUploadedRequiredCountProperty(): int
    {
        return collect($this->requiredDocuments)->filter(fn ($d) => $d['uploaded'])->count();
    }

    /**
     * True when every required document has at least one uploaded file.
     * Computed from the unified requiredDocuments snapshot — same source of
     * truth as the UI, so checklist and submit button never disagree.
     */
    public function getIsReadyForReviewProperty(): bool
    {
        return $this->requiredDocumentsTotal > 0
            && $this->uploadedRequiredCount === $this->requiredDocumentsTotal;
    }

    /**
     * Client-facing status label that reflects upload progress, not just the
     * raw DB status. Three buckets:
     *   - awaiting client uploads & not complete → "بانتظار استكمال المستندات"
     *   - all required uploaded but not yet submitted → "جاهز للإرسال للمراجعة"
     *   - submitted or further along → DB status label (e.g. "قيد مراجعة الفريق")
     */
    public function getUiStatusLabelProperty(): string
    {
        if ($this->isAwaitingClientUpload && ! $this->isReadyForReview) {
            return __('fs_ui_status_awaiting_docs') ?: 'بانتظار استكمال المستندات';
        }

        if ($this->isAwaitingClientUpload && $this->isReadyForReview) {
            return __('fs_ui_status_ready_for_review') ?: 'جاهز للإرسال للمراجعة';
        }

        return (string) ($this->statusMeta['label'] ?? __('status_in_review'));
    }

    public function getUiStatusColorProperty(): string
    {
        if ($this->isAwaitingClientUpload && ! $this->isReadyForReview) {
            return 'amber';
        }
        if ($this->isAwaitingClientUpload && $this->isReadyForReview) {
            return 'emerald';
        }
        return 'teal';
    }

    /**
     * Statuses that mean "still gathering documents from the client".
     * Once we move past these, the "submit for review" button hides.
     */
    public function getIsAwaitingClientUploadProperty(): bool
    {
        $status = strtolower((string) $this->request->status);

        return in_array($status, [
            'new',
            'draft',
            'pending',
            'waiting_docs',
            'files_uploaded',
        ], true);
    }

    /**
     * Client-side action: move the request from "waiting documents" into the
     * review pipeline. Pre-conditions:
     *   - the user can view the request (authorize),
     *   - all required documents are uploaded,
     *   - the request is still awaiting client upload.
     * Uses the existing 'in_review' status — no schema changes.
     */
    public function submitForReview(): void
    {
        $this->authorize('view', $this->request);

        // Re-read latest status from DB to defend against double-submit
        // (two tabs, double-click, etc.) before evaluating gates.
        $this->request->refresh();

        if (! $this->isAwaitingClientUpload) {
            $this->dispatch('notify', message: 'الطلب لم يعد في مرحلة رفع المستندات.', type: 'error');
            return;
        }

        if (! $this->isReadyForReview) {
            $this->dispatch('notify', message: 'يرجى رفع المستندات الأساسية أولًا.', type: 'error');
            return;
        }

        $oldStatus = (string) $this->request->status;
        $newStatus = 'in_review';

        $this->request->update(['status' => $newStatus]);

        $this->request->statusLogs()->create([
            'from_status' => $oldStatus,
            'to_status'   => $newStatus,
            'changed_by'  => Auth::id(),
            'note'        => 'إرسال الطلب لمراجعة الفريق من العميل.',
        ]);

        $this->request->refresh();
        $this->dispatch('notify', message: 'تم إرسال الطلب للمراجعة بنجاح.', type: 'success');
    }

    public function staffUpdate(): void
{
    $this->authorize('update', $this->request);

    $this->validate([
        'new_status'  => 'required|string|max:50',
        'admin_notes' => 'nullable|string|max:6000',
    ]);

    $oldStatus = (string) $this->request->status;
    $newStatus = (string) $this->new_status;

    $this->request->update([
        'status' => $newStatus,
        'admin_notes' => $this->admin_notes,
    ]);

    if ($oldStatus !== $newStatus) {
        $this->request->statusLogs()->create([
            'from_status' => $oldStatus,
            'to_status' => $newStatus,
            'changed_by' => Auth::id(),
            'note' => $this->admin_notes ?: null,
        ]);
    }

    $this->dispatch('notify', message: __('msg_saved'), type: 'success');
}
    /**
     * Persist a single required/optional document picked into $docs[$key].
     * Called by the explicit "رفع الآن" button — never by wire:change. This
     * two-step pattern (pick → confirm) makes the pending state visible to the
     * client and avoids the race where a wire:change-triggered upload runs
     * before WithFileUploads finishes streaming the temporary file.
     */
    public function uploadSingle(string $key): void
    {
        $this->authorize('view', $this->request);

        if (! isset($this->docs[$key]) || $this->docs[$key] === null) {
            return;
        }

        $this->validate([
            "docs.{$key}" => 'required|file|mimes:pdf,jpg,jpeg,png,xls,xlsx|max:20480',
        ], [
            "docs.{$key}.mimes" => 'عذراً، الصيغ المقبولة هي: PDF, JPG, PNG, Excel فقط.',
            "docs.{$key}.max"   => 'يجب أن لا يتجاوز حجم الملف 20 ميجابايت.',
        ]);

        $file = $this->docs[$key];
        $path = $file->store("fs-requests/{$this->request->public_id}/{$key}", 'private');

        $existing = FinancialStatementFile::where('financial_statement_request_id', $this->request->id)
            ->where('file_key', $key)
            ->first();

        if ($existing) {
            if ($existing->path) {
                Storage::disk($existing->disk ?: 'private')->delete($existing->path);
            }
            $existing->update([
                'uploaded_by'   => Auth::id(),
                'file_key'      => $key,
                'path'          => $path,
                'original_name' => $file->getClientOriginalName(),
                'disk'          => 'private',
                'visibility'    => 'client',
                'mime'          => $file->getMimeType(),
                'size'          => (int) $file->getSize(),
                'is_final'      => false,
            ]);
        } else {
            FinancialStatementFile::create([
                'financial_statement_request_id' => $this->request->id,
                'uploaded_by'   => Auth::id(),
                'file_key'      => $key,
                'path'          => $path,
                'original_name' => $file->getClientOriginalName(),
                'disk'          => 'private',
                'visibility'    => 'client',
                'mime'          => $file->getMimeType(),
                'size'          => (int) $file->getSize(),
                'is_final'      => false,
            ]);
        }

        // Drop the pending pick and refresh relations so the next render reads
        // the freshly created/updated file rather than any cached collection.
        unset($this->docs[$key]);
        $this->request->refresh();
        $this->request->unsetRelation('files');
        $this->uploadVersion++;

        $this->dispatch('notify', message: __('msg_saved'), type: 'success');
    }

    /**
     * Drop a pending (picked-but-not-uploaded) single-doc selection.
     */
    public function removePendingDoc(string $key): void
    {
        $this->authorize('view', $this->request);
        unset($this->docs[$key]);
    }

    public function uploadInvoices(): void
    {
        $this->authorize('view', $this->request);

        $group = 'invoices';
        if (empty($this->multiDocs[$group])) {
            return;
        }

        $this->validate([
            "multiDocs.{$group}.*" => 'required|file|mimes:pdf,jpg,jpeg,png,xls,xlsx,zip,rar|max:20480',
        ], [
            "multiDocs.{$group}.*.mimes" => 'عذراً، الصيغ المقبولة هي: PDF, صور، Excel، أو ملفات مضغوطة (ZIP/RAR).',
            "multiDocs.{$group}.*.max"   => 'يجب أن لا يتجاوز حجم الملف الواحد 20 ميجابايت.',
        ]);

        foreach ($this->multiDocs[$group] as $file) {
            $path = $file->store("fs-requests/{$this->request->public_id}/{$group}", 'private');
            FinancialStatementFile::create([
                'financial_statement_request_id' => $this->request->id,
                'uploaded_by'   => Auth::id(),
                'file_key'      => $group,
                'path'          => $path,
                'original_name' => $file->getClientOriginalName(),
                'disk'          => 'private',
                'visibility'    => 'client',
                'mime'          => $file->getMimeType(),
                'size'          => (int) $file->getSize(),
                'is_final'      => false,
            ]);
        }

        $this->multiDocs[$group] = [];
        $this->request->refresh();
        $this->request->unsetRelation('files');
        $this->uploadVersion++;

        $this->dispatch('notify', message: __('msg_saved'), type: 'success');
    }

    /**
     * Drop all pending invoice picks without uploading.
     */
    public function clearPendingInvoices(): void
    {
        $this->authorize('view', $this->request);
        $this->multiDocs['invoices'] = [];
    }

    public function sendMessage(): void
    {
        $this->authorize('view', $this->request);
        $this->validate(['message' => 'required|string|min:1|max:4000']);

        FinancialStatementMessage::create([
            'financial_statement_request_id' => $this->request->id,
            'sender_id' => Auth::id(),
            'sender_type' => $this->isStaff ? 'staff' : 'client',
            'body' => $this->message,
        ]);

        $this->message = '';
    }

public function render()
{
    $filesQuery = $this->request->files()->latest();

    if (! $this->isStaff) {
        $filesQuery->where(function ($q) {
            $q->whereNull('visibility')
              ->orWhereIn('visibility', ['client', 'both']);
        });
    }

    $files = $filesQuery->get();
    $grouped = $files->groupBy('file_key');

    $allKeys = array_values(array_unique(array_merge(
        array_keys($this->requiredDocs),
        array_keys($this->optionalDocs),
        array_keys($this->multiGroups),
        ['final_output'],
    )));

    $defaults = collect($allKeys)->mapWithKeys(fn ($k) => [$k => collect()]);
    $filesByKey = $defaults->merge($grouped);

    // Cap to most recent 300 messages — the chat view loads them all in one
    // pane; older threads remain accessible via the dedicated history page.
    $messages = $this->request->messages()
        ->with('senderUser')
        ->latest()
        ->limit(300)
        ->get()
        ->reverse()
        ->values();

    return view('livewire.financial-statements.show', [
        'filesByKey' => $filesByKey,
        'messages' => $messages,
    ])->layout('layouts.app');
}
}