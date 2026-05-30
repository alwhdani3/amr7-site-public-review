<?php

namespace App\Jobs;

use App\Models\Company;
use App\Services\Gosi\GosiClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * P1.5 — مزامنة منشأة مع GOSI.
 *
 * Sandbox فقط الآن — لا اتصال خارجي.
 * الـ Job يسجّل عملية "skipped: sandbox" في gosi_sync_logs ثم يعود.
 *
 * عند الانتقال إلى Production:
 *   - عبئ GOSI_SANDBOX=false في .env.
 *   - أضف الاستدعاءات الفعلية لـ GosiClient->getEstablishmentInfo / listSubscribers.
 *   - أضف معالجة فشل + retry policy (Job يدعم $tries و backoff()).
 */
class SyncGosiEstablishment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1; // Sandbox: لا حاجة لمحاولات.
    public int $timeout = 30;

    public function __construct(public int $companyId)
    {
    }

    public function handle(GosiClient $client): void
    {
        $company = Company::find($this->companyId);
        if (! $company) {
            Log::warning("SyncGosiEstablishment skipped: company {$this->companyId} not found.");
            return;
        }

        if ($client->isSandbox()) {
            $this->logSync($company->id, 'establishment_info', 'skipped', 'sandbox mode: external call disabled', sandbox: true);
            $this->logSync($company->id, 'subscribers',         'skipped', 'sandbox mode: external call disabled', sandbox: true);
            return;
        }

        // إنتاج لاحقًا — لا نمسّ شيئًا الآن.
        $this->logSync($company->id, 'establishment_info', 'pending', 'production path not implemented yet', sandbox: false);
    }

    protected function logSync(int $companyId, string $endpoint, string $status, ?string $error, bool $sandbox): void
    {
        try {
            DB::table('gosi_sync_logs')->insert([
                'company_id'    => $companyId,
                'endpoint'      => $endpoint,
                'status'        => $status,
                'response_code' => null,
                'error'         => $error,
                'synced_at'     => now(),
                'sandbox'       => $sandbox,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        } catch (\Throwable $e) {
            // الجدول قد لا يكون أُنشئ بعد إذا لم تُشغَّل migration 120040.
            // لا نكسر الـ Job بسبب logging فقط.
            Log::warning('Failed to write gosi_sync_logs row: ' . $e->getMessage());
        }
    }
}
