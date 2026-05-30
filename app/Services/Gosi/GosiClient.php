<?php

namespace App\Services\Gosi;

use Illuminate\Support\Facades\Log;

/**
 * P1.5 — Stub آمن لـ GOSI.
 *
 * ⚠️ هذا Stub فقط. لا اتصال خارجي. لا DPoP فعلي. لا توقيع OAuth.
 *
 * المهمة الآن:
 *   - تثبيت واجهة الـ Service.
 *   - دعم gosi_sync_logs.
 *   - رفض أي عملية كتابية تلقائيًا.
 *
 * عند الانتقال إلى Production لاحقًا:
 *   1) implement getAccessToken() → POST {base_url}/oauth/token (client_credentials).
 *   2) implement getEstablishmentInfo() / listSubscribers() عبر Http::withToken().
 *   3) implement deductContributorContribution() مع DPoP header.
 *   4) ضبط GOSI_SANDBOX=false + مراجعة أمنية كاملة قبل التشغيل.
 *
 * لا تستدعِ هذا الـ Client مباشرة من Controller الإنتاج قبل اكتمال الخطوات أعلاه.
 */
class GosiClient
{
    protected string $baseUrl;
    protected string $clientId;
    protected string $clientSecret;
    protected string $apiKey;
    protected bool $sandbox;
    protected int $timeout;

    public function __construct(
        ?string $baseUrl = null,
        ?string $clientId = null,
        ?string $clientSecret = null,
        ?string $apiKey = null,
        ?bool $sandbox = null,
        ?int $timeout = null,
    ) {
        $this->baseUrl      = $baseUrl     ?? (string) config('services.gosi.base_url', '');
        $this->clientId     = $clientId    ?? (string) config('services.gosi.client_id', '');
        $this->clientSecret = $clientSecret ?? (string) config('services.gosi.client_secret', '');
        $this->apiKey       = $apiKey      ?? (string) config('services.gosi.api_key', '');
        $this->sandbox      = $sandbox     ?? (bool) config('services.gosi.sandbox', true);
        $this->timeout      = $timeout     ?? (int) config('services.gosi.timeout', 30);
    }

    public function isSandbox(): bool
    {
        return $this->sandbox;
    }

    /**
     * Stub: returns placeholder access token in sandbox.
     * Production: must POST to {base_url}/oauth/token with client_credentials grant.
     */
    public function getAccessToken(): array
    {
        if ($this->sandbox) {
            return [
                'ok'           => true,
                'sandbox'      => true,
                'access_token' => 'sandbox-token-not-real',
                'expires_in'   => 0,
                'note'         => 'GosiClient is in sandbox mode; no external call made.',
            ];
        }

        Log::warning('GosiClient.getAccessToken called outside sandbox without implementation.');

        return [
            'ok'      => false,
            'sandbox' => false,
            'error'   => 'production_not_implemented',
        ];
    }

    /**
     * Stub: returns placeholder establishment info for a CR number.
     */
    public function getEstablishmentInfo(string $crNumber): array
    {
        if ($this->sandbox) {
            return [
                'ok'        => true,
                'sandbox'   => true,
                'cr_number' => $crNumber,
                'data'      => [
                    'establishment_id' => 'SANDBOX-EST-' . $crNumber,
                    'status'           => 'placeholder',
                    'note'             => 'No external call. Replace this with real GOSI integration before going live.',
                ],
            ];
        }

        Log::warning('GosiClient.getEstablishmentInfo called outside sandbox without implementation.');

        return [
            'ok'      => false,
            'sandbox' => false,
            'error'   => 'production_not_implemented',
        ];
    }

    /**
     * Stub: returns empty subscriber list for a given establishment in sandbox.
     */
    public function listSubscribers(string $establishmentId): array
    {
        if ($this->sandbox) {
            return [
                'ok'               => true,
                'sandbox'          => true,
                'establishment_id' => $establishmentId,
                'subscribers'      => [],
                'note'             => 'Empty placeholder list in sandbox.',
            ];
        }

        return [
            'ok'      => false,
            'sandbox' => false,
            'error'   => 'production_not_implemented',
        ];
    }

    /**
     * Stub: contributor deduction is a sensitive write op.
     * Even in sandbox we refuse it explicitly to prevent accidental writes.
     */
    public function deductContributorContribution(array $payload): array
    {
        $reference = $payload['reference'] ?? null;

        Log::info('GosiClient.deductContributorContribution called (sandbox=' . ($this->sandbox ? 'true' : 'false') . ', ref=' . (string) $reference . ')');

        return [
            'ok'      => false,
            'sandbox' => $this->sandbox,
            'error'   => 'sandbox_refuses_write_operations',
            'note'    => 'Production implementation must include DPoP header, OAuth client credentials, and audit logging.',
        ];
    }
}
