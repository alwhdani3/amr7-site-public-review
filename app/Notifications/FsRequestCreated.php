<?php

namespace App\Notifications;

use App\Models\FinancialStatementRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FsRequestCreated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public FinancialStatementRequest $request,
        public string $recipientType = 'admin' // admin | client
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('financial-statements.show', $this->request);

        if ($this->recipientType === 'client') {
            return (new MailMessage)
                ->subject('تم استلام طلب القوائم المالية')
                ->greeting('مرحبًا ' . ($notifiable->name ?? ''))
                ->line('تم استلام طلب القوائم المالية الخاص بك بنجاح.')
                ->line('رقم الطلب: ' . $this->request->public_id)
                ->action('عرض الطلب', $url)
                ->line('سنقوم بمراجعته ومتابعته معك عبر المنصة.');
        }

        return (new MailMessage)
            ->subject('طلب قائمة مالية جديد - شركة آمر سبعة لحلول الأعمال')
            ->view('emails.operations.financial-statement-request', [
                'emailTitle' => 'طلب قائمة مالية جديد',
                'companyName' => $this->request->company_name,
                'crNumber' => $this->request->cr_number,
                'fiscalYear' => $this->request->fiscal_year,
                'trackingNumber' => $this->request->public_id,
                'status' => $this->request->status_label ?: $this->request->status,
                'url' => $url,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'financial_statement_created',
            'recipient_type' => $this->recipientType,
            'request_id' => $this->request->id,
            'public_id' => $this->request->public_id,
            'status' => $this->request->status,
            'url' => route('financial-statements.show', $this->request),
            'title' => $this->recipientType === 'client'
                ? 'تم استلام طلب القوائم المالية'
                : 'طلب قوائم مالية جديد',
        ];
    }
}
