<?php

namespace App\Livewire\Dashboard;

use App\Models\AiDocumentExtraction;
use App\Services\Ai\DocumentExtractionService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Phase E — شاشة مراجعة استخراجات AI.
 *
 * تفتح ضمن dashboard?section=ai-review (يضاف لاحقًا في Dashboard.php
 * أو يُستخدم كصفحة مستقلة).
 */
class AiReviewPanel extends Component
{
    public ?int $editingId = null;

    /** @var array<string, string|null> */
    public array $overrides = [];

    /**
     * Phase 7: العميل يعتمد البيانات بنفسه — هذا المصفوف يحفظ
     * إقرار العميل لكل extraction قبل تفعيل زر "أعتمد صحة البيانات".
     * @var array<int, bool>
     */
    public array $clientAttestations = [];

    public function mount(): void
    {
        abort_unless(Auth::check(), 401);
    }

    #[Computed]
    public function extractions()
    {
        $user = Auth::user();
        $isBackoffice = $user && method_exists($user, 'hasBackofficeAccess') && $user->hasBackofficeAccess();

        $query = AiDocumentExtraction::query()
            ->where('status', AiDocumentExtraction::STATUS_READY_FOR_REVIEW);

        // Polish: العميل غير backoffice يرى فقط استخراجات منشأته النشطة
        if (! $isBackoffice && $user) {
            $activeCompanyId = (int) ($user->active_company_id ?? session('active_company_id') ?? 0);
            $query->where('company_id', $activeCompanyId);
        }

        return $query->latest()->limit(20)->get();
    }

    #[Computed]
    public function isBackoffice(): bool
    {
        $user = Auth::user();
        return $user && method_exists($user, 'hasBackofficeAccess') && $user->hasBackofficeAccess();
    }

    /**
     * ترجمة مفاتيح AI fields + document types إلى عربي مقروء.
     * Phase 2: أُضيفت فروع document_type (cr/tax/gosi/...) لمنع ظهور raw key مثل "tax".
     */
    public function translateAiField(string $key): string
    {
        return match ($key) {
            // Field keys
            'company_name'                    => 'اسم المنشأة',
            'commercial_registration_number'  => 'رقم السجل التجاري',
            'unified_number'                  => 'الرقم الموحد 700',
            'tax_number'                      => 'الرقم الضريبي',
            'city'                            => 'المدينة',
            'business_activity'               => 'نشاط المنشأة',
            'cr_issued_at'                    => 'تاريخ إصدار السجل',
            'cr_expires_at'                   => 'تاريخ انتهاء السجل',
            'gosi_subscription_number'        => 'رقم اشتراك التأمينات',
            'medical_insurance_company'       => 'شركة التأمين الطبي',
            'medical_insurance_policy_number' => 'رقم وثيقة التأمين الطبي',
            'medical_insurance_starts_at'     => 'بداية التأمين الطبي',
            'medical_insurance_ends_at'       => 'نهاية التأمين الطبي',

            // Document types — يستهلكهم الـheader في ai-review-panel.blade.php
            'cr'                              => 'السجل التجاري',
            'tax'                             => 'الزكاة والضريبة',
            'gosi'                            => 'شهادة التأمينات الاجتماعية',
            'medical_insurance'               => 'وثيقة التأمين الطبي',
            'national_address'                => 'العنوان الوطني',
            'articles_of_association'         => 'عقد التأسيس',
            'invoice'                         => 'فاتورة',
            'contract'                        => 'عقد',
            'other'                           => 'وثيقة أخرى',

            default                           => $key,
        };
    }

    public function startEditing(int $id): void
    {
        $this->editingId = $id;
        $this->overrides = [];

        $extraction = AiDocumentExtraction::find($id);
        if (! $extraction || ! is_array($extraction->extracted_json)) {
            return;
        }

        foreach (($extraction->extracted_json['fields'] ?? []) as $key => $payload) {
            $this->overrides[$key] = (string) ($payload['value'] ?? '');
        }
    }

    public function cancelEditing(): void
    {
        $this->editingId = null;
        $this->overrides = [];
    }

    public function approve(int $id, DocumentExtractionService $service): void
    {
        $extraction = AiDocumentExtraction::findOrFail($id);

        // صلاحية: فقط backoffice/super_admin قد يعتمد. نعتمد helper موجود.
        $user = Auth::user();
        $canApprove = $user
            && method_exists($user, 'hasBackofficeAccess')
            && $user->hasBackofficeAccess();

        abort_unless($canApprove, 403);

        $service->approve($extraction->id, (int) $user->id, $this->overrides);

        $this->cancelEditing();

        $this->dispatch('ai-extraction-approved', id: $id);
    }

    /**
     * Phase 7: العميل يعتمد بيانات الوثيقة بنفسه — لا حاجة لمراجعة الموظف.
     * شروط:
     *   1. المستخدم authenticated.
     *   2. الـextraction يخص شركة عضو فيها فعليًا (active pivot).
     *   3. العميل ضغط checkbox "أقر بأن البيانات صحيحة" قبل الإرسال.
     * يستعمل نفس DocumentExtractionService::approve لكتابة القيم على companies،
     * مع تمرير user_id الخاص بالعميل كـapprover_user_id للـaudit trail.
     */
    public function clientApprove(int $id, DocumentExtractionService $service): void
    {
        $extraction = AiDocumentExtraction::findOrFail($id);

        $user = Auth::user();
        abort_unless($user, 403);

        // 1) إقرار العميل مطلوب
        if (! ($this->clientAttestations[$id] ?? false)) {
            $this->dispatch('notify', type: 'error', message: 'يجب تأكيد الإقرار بصحة البيانات أولًا.');
            return;
        }

        // 2) العميل عضو فعّال في الشركة التي يخصها الاستخراج
        $isOwner = $user->companies()
            ->whereKey((int) $extraction->company_id)
            ->wherePivot('is_active', true)
            ->exists();

        abort_unless($isOwner, 403);

        $service->approve($extraction->id, (int) $user->id, $this->overrides);

        $this->cancelEditing();
        unset($this->clientAttestations[$id]);

        $this->dispatch('ai-extraction-approved', id: $id);
        $this->dispatch('notify', type: 'success', message: 'تم اعتماد البيانات وتحديث ملف المنشأة.');
    }

    public function reject(int $id, DocumentExtractionService $service): void
    {
        $extraction = AiDocumentExtraction::findOrFail($id);

        $user = Auth::user();
        $canApprove = $user
            && method_exists($user, 'hasBackofficeAccess')
            && $user->hasBackofficeAccess();

        abort_unless($canApprove, 403);

        $service->reject($extraction->id, (int) $user->id);

        $this->cancelEditing();

        $this->dispatch('ai-extraction-rejected', id: $id);
        $this->dispatch('notify', type: 'success', message: 'تم رفض الاستخراج. لن يتم تحديث ملف المنشأة.');
    }

    /**
     * Polish (hotfix): إعادة تحليل الوثيقة — تُنشئ extraction جديد للوثيقة نفسها
     * وتُرسل Job جديد. مسموح فقط لـ backoffice.
     */
    public function retry(int $id, DocumentExtractionService $service): void
    {
        $extraction = AiDocumentExtraction::findOrFail($id);

        $user = Auth::user();
        $canApprove = $user
            && method_exists($user, 'hasBackofficeAccess')
            && $user->hasBackofficeAccess();

        abort_unless($canApprove, 403);

        // ضع الاستخراج الحالي كمرفوض كي لا يتعارض، ثم قم بإصدار استخراج جديد.
        try {
            $service->reject($extraction->id, (int) $user->id);
        } catch (\Throwable $e) {
            // تجاهل لو فشل reject (مثلاً انتقل لحالة أخرى) — نستمر بإصدار محاولة جديدة.
        }

        $document = $extraction->document; // علاقة موجودة في AiDocumentExtraction
        if (! $document) {
            $this->dispatch('notify', type: 'error', message: 'تعذّر العثور على الوثيقة الأصلية لإعادة التحليل.');
            return;
        }

        try {
            $newExtraction = $service->queue($document, (int) $user->id);
            \App\Jobs\ProcessCompanyDocumentExtraction::dispatch($newExtraction->id);
            $this->dispatch('notify', type: 'success', message: 'تم بدء إعادة تحليل الوثيقة. ستظهر النتيجة قريبًا.');
            $this->dispatch('ai-extraction-retried', id: $newExtraction->id);
        } catch (\Throwable $e) {
            $this->dispatch('notify', type: 'error', message: 'تعذّر بدء إعادة التحليل. حاول لاحقًا.');
        }
    }

    public function render()
    {
        return view('livewire.dashboard.ai-review-panel', [
            'extractions' => $this->extractions,
        ]);
    }
}
