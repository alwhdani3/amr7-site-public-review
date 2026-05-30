<?php

namespace App\Services\Ai;

use App\Models\AiDocumentExtraction;
use App\Models\Company;
use App\Models\CompanyDocument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Phase E — Service لإدارة دورة حياة استخراج الوثائق.
 *
 * استخدامه:
 *   $svc->queue($document)                  → ينشئ سجل pending + يُرسل Job
 *   $svc->run($extractionId)                → ينفّذ الاستخراج عبر n8n client
 *   $svc->approve($extractionId, $user, $overrides) → ينقل القيم لـ companies
 */
class DocumentExtractionService
{
    public function __construct(protected N8nDocumentExtractionClient $client)
    {
    }

    /**
     * ينشئ سجلًا بحالة pending. الـ Job يأخذها من هنا.
     */
    public function queue(CompanyDocument $document, ?int $userId = null): AiDocumentExtraction
    {
        $attributes = [
            'company_id'    => $document->company_id,
            'document_id'   => $document->id,
            'uploaded_by'   => $userId,
            'document_type' => (string) ($document->type ?? null),
            'status'        => AiDocumentExtraction::STATUS_PENDING,
        ];

        if (Schema::hasColumn('ai_document_extractions', 'correlation_id')) {
            $attributes['correlation_id'] = (string) Str::uuid();
        }

        $extraction = AiDocumentExtraction::create($attributes);

        Log::info('ai.extraction.queued', [
            'extraction_id'  => $extraction->id,
            'document_id'    => $document->id,
            'document_type'  => $extraction->document_type,
            'correlation_id' => $extraction->correlation_id ?? null,
            'uploaded_by'    => $userId,
        ]);

        return $extraction;
    }

    /**
     * يشغّل الاستخراج لسجل موجود. يستدعى من Job.
     */
    public function run(int $extractionId): AiDocumentExtraction
    {
        /** @var AiDocumentExtraction|null $extraction */
        $extraction = AiDocumentExtraction::find($extractionId);
        if (! $extraction) {
            throw new \RuntimeException("AiDocumentExtraction {$extractionId} not found.");
        }

        $extraction->update(['status' => AiDocumentExtraction::STATUS_PROCESSING]);

        Log::info('ai.extraction.processing', [
            'extraction_id'  => $extraction->id,
            'document_id'    => $extraction->document_id,
            'correlation_id' => $extraction->correlation_id ?? null,
            'status'         => AiDocumentExtraction::STATUS_PROCESSING,
        ]);

        try {
            $filePath = optional($extraction->document)->file_path;
            $result = $this->client->extract((string) $extraction->document_type, $filePath, [
                'company_id'  => $extraction->company_id,
                'document_id' => $extraction->document_id,
            ]);

            if ($this->isUnavailableResult($result)) {
                $extraction->update([
                    'status'           => AiDocumentExtraction::STATUS_FAILED,
                    'extracted_json'   => $result,
                    'confidence_score' => $this->averageConfidence($result),
                    'error_message'    => mb_substr($result['message'] ?? 'خدمة التحليل لم تُربط بعد', 0, 1000),
                ]);

                return $extraction->fresh();
            }

            $extraction->update([
                'status'           => AiDocumentExtraction::STATUS_READY_FOR_REVIEW,
                'extracted_json'   => $result,
                'confidence_score' => $this->averageConfidence($result),
            ]);
        } catch (\Throwable $e) {
            $extraction->update([
                'status'        => AiDocumentExtraction::STATUS_FAILED,
                'error_message' => mb_substr($e->getMessage(), 0, 1000),
            ]);
        }

        return $extraction->fresh();
    }

    protected function isUnavailableResult(array $result): bool
    {
        $status = (string) ($result['status'] ?? '');
        $warnings = $result['warnings'] ?? [];

        return in_array($status, ['disabled', 'placeholder'], true)
            || (is_array($warnings) && array_intersect($warnings, ['disabled', 'placeholder']));
    }

    /**
     * يقبل الاستخراج وينقل القيم المعتمدة إلى companies — بدون كتابة مباشرة قبل هذا الاستدعاء.
     */
    public function approve(int $extractionId, int $approverUserId, array $overrides = []): AiDocumentExtraction
    {
        /** @var AiDocumentExtraction $extraction */
        $extraction = AiDocumentExtraction::findOrFail($extractionId);

        if (! $extraction->isReadyForReview()) {
            throw new \RuntimeException('Extraction is not ready for review.');
        }

        DB::transaction(function () use ($extraction, $approverUserId, $overrides) {
            // 1) دمج القيم المُعتمدة (من overrides أو من extracted_json)
            $values = $this->mergeApprovedValues($extraction->extracted_json ?? [], $overrides);

            // 2) كتابة على companies — فقط الحقول المسموح بها وغير الفارغة
            if ($extraction->company_id) {
                $this->applyToCompany($extraction->company_id, $values);
            }

            // 3) تحديث حالة الاستخراج للـ audit
            $extraction->update([
                'status'      => AiDocumentExtraction::STATUS_APPROVED,
                'approved_by' => $approverUserId,
                'approved_at' => now(),
            ]);
        });

        return $extraction->fresh();
    }

    public function reject(int $extractionId, int $approverUserId): AiDocumentExtraction
    {
        /** @var AiDocumentExtraction $extraction */
        $extraction = AiDocumentExtraction::findOrFail($extractionId);

        $extraction->update([
            'status'      => AiDocumentExtraction::STATUS_REJECTED,
            'approved_by' => $approverUserId,
            'approved_at' => now(),
        ]);

        return $extraction;
    }

    protected function mergeApprovedValues(array $extracted, array $overrides): array
    {
        $fields = $extracted['fields'] ?? [];
        $result = [];

        foreach ($fields as $key => $payload) {
            $value = $overrides[$key] ?? ($payload['value'] ?? null);
            if ($value !== null && $value !== '') {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Mapping من حقول AI schema إلى أعمدة companies.
     */
    protected function applyToCompany(int $companyId, array $values): void
    {
        $map = [
            'company_name'                    => 'name',
            'commercial_registration_number'  => 'cr_number',
            'unified_number'                  => 'unified_number',
            'tax_number'                      => 'tax_number',
            'city'                            => 'city',
            'business_activity'               => 'activity',
            'cr_issued_at'                    => 'cr_issue_date',
            'cr_expires_at'                   => 'cr_expiry_date',
            'gosi_subscription_number'        => 'gosi_subscription_number',
            'medical_insurance_company'       => 'medical_insurance_company',
            'medical_insurance_policy_number' => 'medical_insurance_policy_number',
            'medical_insurance_starts_at'     => 'medical_insurance_start_date',
            'medical_insurance_ends_at'       => 'medical_insurance_end_date',
        ];

        $payload = [];
        foreach ($map as $aiKey => $companyCol) {
            if (array_key_exists($aiKey, $values)) {
                $payload[$companyCol] = $values[$aiKey];
            }
        }

        if (empty($payload)) {
            return;
        }

        Company::where('id', $companyId)->update($payload);
    }

    protected function averageConfidence(array $result): ?float
    {
        $fields = $result['fields'] ?? [];
        if (empty($fields)) {
            return null;
        }

        $sum = 0.0;
        $count = 0;
        foreach ($fields as $f) {
            if (isset($f['confidence']) && is_numeric($f['confidence'])) {
                $sum += (float) $f['confidence'];
                $count++;
            }
        }

        return $count > 0 ? round($sum / $count, 3) : null;
    }
}
