<?php

namespace App\Console\Commands;

use App\Models\CompanyDocument;
use App\Notifications\CompanyDocumentExpiringNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * P1.4 — تنبيه وثائق المنشأة عند الاقتراب من الانتهاء.
 *
 * يفحص الوثائق على 4 مراحل: expired / 7d / 30d / 60d.
 * لا يُرسل تنبيه نفس المرحلة مرتين (يقارن مع alert_stage المُسجّل).
 *
 * يُرسل عبر قناة database فقط للأعضاء النشطين من ذوي pivot role = admin.
 *
 * استخدام:
 *   php artisan docs:alert-expiring                # تنفيذ فعلي
 *   php artisan docs:alert-expiring --dry-run      # عرض بدون إرسال أو تحديث
 */
class AlertExpiringDocuments extends Command
{
    protected $signature = 'docs:alert-expiring
                            {--dry-run : عرض ما سيتم بدون إرسال أو تعديل}';

    protected $description = 'تنبيه أصحاب المنشأة عن الوثائق المنتهية أو القريبة من الانتهاء (60d/30d/7d/expired).';

    public function handle(): int
    {
        $now = Carbon::now();
        $dry = (bool) $this->option('dry-run');

        $processed = 0;
        $sent = 0;

        // ترتيب المراحل من الأشد (expired) إلى الأخف (60d) — مهم لمنع
        // تجاوز مرحلة أقدم لأخرى أحدث عند تشغيل متأخر.
        $stages = [
            'expired' => fn ($q) => $q->whereDate('expiry_date', '<=', $now->toDateString()),
            '7d'      => fn ($q) => $q->whereDate('expiry_date', '>', $now->toDateString())
                                       ->whereDate('expiry_date', '<=', $now->copy()->addDays(7)->toDateString()),
            '30d'     => fn ($q) => $q->whereDate('expiry_date', '>', $now->copy()->addDays(7)->toDateString())
                                       ->whereDate('expiry_date', '<=', $now->copy()->addDays(30)->toDateString()),
            '60d'     => fn ($q) => $q->whereDate('expiry_date', '>', $now->copy()->addDays(30)->toDateString())
                                       ->whereDate('expiry_date', '<=', $now->copy()->addDays(60)->toDateString()),
        ];

        foreach ($stages as $stage => $scopeBuilder) {
            $query = CompanyDocument::query()
                ->whereNotNull('expiry_date')
                ->where('alert_stage', '!=', $stage);

            $scopeBuilder($query);

            $documents = $query->with('company.users')->get();

            foreach ($documents as $document) {
                $processed++;

                $this->line(sprintf(
                    '[%s] doc#%d company#%s expiry=%s prev_stage=%s',
                    $stage,
                    $document->id,
                    (string) ($document->company_id ?? '?'),
                    $document->expiry_date?->toDateString() ?? '-',
                    (string) ($document->alert_stage ?? 'none'),
                ));

                if ($dry) {
                    continue;
                }

                $recipients = $document->company?->users()
                    ->wherePivot('is_active', true)
                    ->wherePivot('role', 'admin')
                    ->get();

                if ($recipients) {
                    foreach ($recipients as $user) {
                        try {
                            $user->notify(new CompanyDocumentExpiringNotification($document, $stage));
                            $sent++;
                        } catch (\Throwable $e) {
                            $this->error('Notify failed for user#' . $user->id . ': ' . $e->getMessage());
                        }
                    }
                }

                $document->update([
                    'alert_stage'        => $stage,
                    'alert_last_sent_at' => $now,
                ]);
            }
        }

        $this->info(sprintf(
            'Processed %d documents, sent %d notifications%s.',
            $processed,
            $sent,
            $dry ? ' (dry-run)' : ''
        ));

        return self::SUCCESS;
    }
}
