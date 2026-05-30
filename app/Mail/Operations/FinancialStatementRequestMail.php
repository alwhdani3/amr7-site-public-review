<?php

namespace App\Mail\Operations;

use App\Models\FinancialStatementRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FinancialStatementRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public FinancialStatementRequest $request,
        public ?string $url = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'طلب قائمة مالية جديد - شركة آمر سبعة لحلول الأعمال');
    }

    public function content(): Content
    {
        $url = $this->url ?: route('financial-statements.show', $this->request);

        return new Content(
            view: 'emails.operations.financial-statement-request',
            text: 'emails.plain.operations-notification',
            with: [
                'emailTitle' => 'طلب قائمة مالية جديد',
                'companyName' => $this->request->company_name,
                'crNumber' => $this->request->cr_number,
                'fiscalYear' => $this->request->fiscal_year,
                'trackingNumber' => $this->request->public_id,
                'status' => $this->request->status_label ?: $this->request->status,
                'url' => $url,
                'title' => 'طلب قائمة مالية جديد',
                'intro' => 'تم تسجيل طلب جديد للقوائم المالية.',
                'actionLabel' => 'فتح الطلب',
                'lines' => [
                    'اسم المنشأة' => $this->request->company_name,
                    'رقم السجل' => $this->request->cr_number,
                    'السنة المالية' => $this->request->fiscal_year,
                    'رقم التتبع' => $this->request->public_id,
                    'الحالة' => $this->request->status_label ?: $this->request->status,
                ],
            ],
        );
    }
}
