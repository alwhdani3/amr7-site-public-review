<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Log;

/**
 * Phase E — Client آمن لاستخراج بيانات الوثائق عبر n8n.
 *
 * ⚠️ لا اتصال خارجي فعلي في هذه الجولة. الاتصال يُفعَّل فقط حين:
 *   - config('ai.enabled') = true
 *   - config('ai.provider') = 'n8n'
 *   - config('ai.n8n.webhook') مضبوط
 *
 * بدون استيفاء الشروط، يعيد نتيجة واضحة بأنها غير جاهزة ولا تُعرض كنجاح.
 */
class N8nDocumentExtractionClient
{
    public function isReady(): bool
    {
        return $this->readinessStatus() === 'ready';
    }

    /**
     * Three-state readiness check used by isReady() and surfaced explicitly
     * in extract() so the caller can distinguish:
     *   - disabled      : AI_EXTRACTION_ENABLED is false (operator turned it off)
     *   - not_configured: enabled but missing provider/webhook/api_key
     *   - ready         : enabled + all required config present
     */
    public function readinessStatus(): string
    {
        if (! (bool) config('ai.enabled')) {
            return 'disabled';
        }

        if (config('ai.provider') !== 'n8n') {
            return 'not_configured';
        }

        if (! filled(config('ai.n8n.webhook'))) {
            return 'not_configured';
        }

        return 'ready';
    }

    /**
     * Returns a uniform JSON envelope with {value, confidence} fields.
     * When disabled or misconfigured, returns a typed unavailable result —
     * callers must NOT treat these as a successful extraction. The actual
     * n8n call only fires once readinessStatus() === 'ready'.
     */
    public function extract(string $documentType, ?string $filePath = null, array $context = []): array
    {
        $status = $this->readinessStatus();

        if ($status === 'disabled') {
            Log::info('AI extraction skipped (disabled)', [
                'document_type' => $documentType,
            ]);

            return $this->unavailableResult($documentType, 'disabled', 'التحليل الذكي غير مفعل حاليًا');
        }

        if ($status === 'not_configured') {
            Log::warning('AI extraction is enabled but n8n webhook/provider configuration is incomplete.', [
                'document_type' => $documentType,
                'provider'      => config('ai.provider'),
                'has_webhook'   => filled(config('ai.n8n.webhook')),
            ]);

            return $this->unavailableResult($documentType, 'not_configured', 'إعداد التحليل الذكي غير مكتمل.');
        }

        // status === 'ready'. The real outbound HTTP call to n8n is intentionally
        // left commented until the operator approves activation — wiring it
        // here without an explicit go-ahead would burn n8n / model quota and
        // could leak document content to the workflow.
        //
        // When approved, replace the Log::warning + return below with:
        //
        //   $response = Http::timeout(config('ai.n8n.timeout', 60))
        //       ->withHeaders([
        //           'Authorization' => 'Bearer ' . config('ai.n8n.api_key'),
        //           'X-AMR7-Document-Type' => $documentType,
        //       ])
        //       ->attach('file', file_get_contents($filePath), basename($filePath))
        //       ->post(config('ai.n8n.webhook'), $context);
        //
        //   return $this->normalize($response->json(), $documentType);

        Log::warning('AI extraction reached ready state but outbound call is gated until operator approval.', [
            'document_type' => $documentType,
        ]);

        return $this->unavailableResult($documentType, 'placeholder', 'خدمة التحليل لم تُربط بعد');
    }

    /**
     * Schema موحَّد مع حالة واضحة تمنع اعتباره جاهزًا للمراجعة.
     */
    protected function unavailableResult(string $documentType, string $status, string $message): array
    {
        $emptyField = ['value' => '', 'confidence' => 0];

        return [
            'document_type' => $documentType,
            'status' => $status,
            'message' => $message,
            'fields' => [
                'company_name'                    => $emptyField,
                'commercial_registration_number'  => $emptyField,
                'unified_number'                  => $emptyField,
                'tax_number'                      => $emptyField,
                'city'                            => $emptyField,
                'business_activity'               => $emptyField,
                'cr_issued_at'                    => $emptyField,
                'cr_expires_at'                   => $emptyField,
                'gosi_subscription_number'        => $emptyField,
                'medical_insurance_company'       => $emptyField,
                'medical_insurance_policy_number' => $emptyField,
                'medical_insurance_starts_at'     => $emptyField,
                'medical_insurance_ends_at'       => $emptyField,
            ],
            'warnings'    => [$status],
            'raw_summary' => $message,
        ];
    }
}
