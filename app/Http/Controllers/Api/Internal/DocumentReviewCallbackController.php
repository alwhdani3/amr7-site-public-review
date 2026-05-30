<?php

namespace App\Http\Controllers\Api\Internal;

use App\Http\Controllers\Controller;
use App\Models\AiDocumentExtraction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Phase 8 — Internal n8n callback for AI document extraction.
 *
 *   POST /api/internal/document-review/callback
 *
 * Security:
 *   - Verifies a static token via `X-AMR7-N8N-Token` header.
 *   - Token is read from config('ai.callback_token'), which itself
 *     comes from env('AI_CALLBACK_TOKEN'). No secret is ever in code.
 *   - If the token is empty or mismatched, returns 403.
 *
 * Side effects (intentionally minimal):
 *   - Updates a single AiDocumentExtraction row.
 *   - Stores the full n8n payload inside extracted_json (already an `array` cast).
 *   - Maps the n8n processing_status onto an existing extraction status
 *     (does NOT introduce new enum values here — that's Phase 9+).
 *   - **Never** writes to the companies table. The client must click
 *     "أعتمد صحة البيانات" inside the AI Review panel for the data to
 *     reach the company profile (App\Livewire\Dashboard\AiReviewPanel::clientApprove).
 */
class DocumentReviewCallbackController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        // 1) Token gate (inline — no new middleware file in Phase 8).
        $expected = (string) config('ai.callback_token', '');
        $provided = (string) $request->header('X-AMR7-N8N-Token', '');

        if ($expected === '' || $provided === '' || ! hash_equals($expected, $provided)) {
            Log::warning('ai.callback.unauthorized', [
                'token_provided' => $provided !== '',
                'token_expected' => $expected !== '',
                'ip'             => $request->ip(),
            ]);

            return response()->json([
                'ok'      => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        // 2) Payload validation. We accept the shape documented in
        //    docs/amr7-document-ai-n8n-workflow.md. Fields beyond these
        //    are preserved as-is inside extracted_json.
        $validator = Validator::make($request->all(), [
            'ok'                => 'required|boolean',
            'request_id'        => 'nullable|string|max:128',
            'document_id'       => 'required|integer|min:1',
            'company_id'        => 'nullable|integer|min:1',
            'document_type'     => 'nullable|string|max:64',
            'processing_status' => 'nullable|string|max:64',
            'confidence'        => 'nullable|numeric|min:0|max:1',
            'fields'            => 'nullable|array',
            'missing_fields'    => 'nullable|array',
            'warnings'          => 'nullable|array',
            'ai_summary_ar'     => 'nullable|string|max:2000',
            'error_code'        => 'nullable|string|max:64',
            'error_message_ar'  => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ok'      => false,
                'message' => 'Invalid payload.',
                'errors'  => $validator->errors()->all(),
            ], 422);
        }

        $payload = $validator->validated();

        // 3) Lookup the extraction (we identify by document_id;
        //    Phase 9+ may switch to request_id once that column exists).
        $extraction = AiDocumentExtraction::query()
            ->where('document_id', (int) $payload['document_id'])
            ->whereIn('status', [
                AiDocumentExtraction::STATUS_PENDING,
                AiDocumentExtraction::STATUS_PROCESSING,
            ])
            ->latest('id')
            ->first();

        if (! $extraction) {
            return response()->json([
                'ok'      => false,
                'message' => 'No pending extraction found for this document.',
            ], 404);
        }

        // 4) Map n8n processing_status onto our existing status enum.
        //    Phase 8 keeps the existing column values. Forward-compat values
        //    (ready_for_client_approval / low_confidence / etc.) are preserved
        //    inside extracted_json.processing_status_raw for later promotion.
        $newStatus = $this->mapStatus($payload['processing_status'] ?? null, ($payload['ok'] ?? false));

        $extracted = $payload;
        // tag the n8n raw status separately so we don't lose it
        $extracted['processing_status_raw'] = $payload['processing_status'] ?? null;
        $extracted['_received_at']          = now()->toIso8601String();

        $update = [
            'status'           => $newStatus,
            'extracted_json'   => $extracted,
            'confidence_score' => $payload['confidence'] ?? null,
        ];

        if (! ($payload['ok'] ?? false)) {
            $update['error_message'] = mb_substr(
                (string) ($payload['error_message_ar'] ?? $payload['error_code'] ?? 'AI processing failed'),
                0,
                1000
            );
        }

        $extraction->update($update);

        Log::info('ai.callback.processed', [
            'extraction_id'  => $extraction->id,
            'document_id'    => $extraction->document_id,
            'correlation_id' => $extraction->correlation_id ?? null,
            'mapped_status'  => $newStatus,
            'ok'             => (bool) ($payload['ok'] ?? false),
        ]);

        return response()->json([
            'ok'             => true,
            'extraction_id'  => $extraction->id,
            'mapped_status'  => $newStatus,
            'correlation_id' => $extraction->correlation_id ?? null,
        ]);
    }

    /**
     * Phase 8: existing status enum is {pending, processing, ready_for_review,
     * approved, rejected, failed}. We alias n8n's richer vocabulary onto these.
     */
    protected function mapStatus(?string $n8nStatus, bool $ok): string
    {
        if (! $ok) {
            return AiDocumentExtraction::STATUS_FAILED;
        }

        return match ($n8nStatus) {
            'ready_for_client_approval',
            'ready_for_review',
            'needs_client_review' => AiDocumentExtraction::STATUS_READY_FOR_REVIEW,
            'low_confidence',
            'needs_reupload',
            'failed',
            'unsupported'         => AiDocumentExtraction::STATUS_FAILED,
            'processing'          => AiDocumentExtraction::STATUS_PROCESSING,
            default               => AiDocumentExtraction::STATUS_READY_FOR_REVIEW,
        };
    }
}
