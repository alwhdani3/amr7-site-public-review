<?php

namespace App\Notifications;

use App\Models\FinancialStatementRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FinancialStatementAdminNewRequest extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public ?FinancialStatementRequest $request = null) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = $this->request
            ? route('financial-statements.show', $this->request)
            : config('app.url') . '/financial-statements/dashboard';

        return (new MailMessage)
            ->subject('طلب قائمة مالية جديد - شركة آمر سبعة لحلول الأعمال')
            ->view('emails.operations.financial-statement-request', [
                'emailTitle' => 'طلب قائمة مالية جديد',
                'companyName' => $this->request?->company_name,
                'crNumber' => $this->request?->cr_number,
                'fiscalYear' => $this->request?->fiscal_year,
                'trackingNumber' => $this->request?->public_id,
                'status' => $this->request?->status_label ?: $this->request?->status,
                'url' => $url,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
