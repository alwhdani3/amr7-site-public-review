<?php

namespace App\Notifications;

use App\Models\CompanyDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * P1.4 — إشعار وثيقة منتهية أو قريبة من الانتهاء.
 *
 * قناة database فقط الآن.
 * لا mail. لا WhatsApp. تُضاف لاحقًا في Phase 2.
 */
class CompanyDocumentExpiringNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public CompanyDocument $document,
        public string $stage, // 60d | 30d | 7d | expired
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $expiry = $this->document->expiry_date;

        return [
            'document_id'     => $this->document->id,
            'company_id'      => $this->document->company_id,
            'type'            => $this->document->type,
            'document_number' => $this->document->document_number,
            'expiry_date'     => $expiry ? $expiry->toDateString() : null,
            'stage'           => $this->stage,
            'days_remaining'  => $this->document->days_remaining,
            'title'           => $this->buildTitle(),
        ];
    }

    public function databaseType(): string
    {
        return 'company_document.expiring';
    }

    protected function buildTitle(): string
    {
        $type = (string) ($this->document->type ?? 'وثيقة');

        return match ($this->stage) {
            'expired' => "وثيقة منتهية الصلاحية: {$type}",
            '7d'      => "وثيقة تنتهي خلال 7 أيام: {$type}",
            '30d'     => "وثيقة تنتهي خلال 30 يومًا: {$type}",
            '60d'     => "تنبيه مبكّر — وثيقة تنتهي خلال 60 يومًا: {$type}",
            default   => "تنبيه وثيقة: {$type}",
        };
    }
}
