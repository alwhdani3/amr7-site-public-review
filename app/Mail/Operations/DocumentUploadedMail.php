<?php

namespace App\Mail\Operations;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DocumentUploadedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public array $document, public ?string $url = null) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'وثيقة جديدة مرفوعة - شركة آمر سبعة لحلول الأعمال');
    }

    public function content(): Content
    {
        $url = $this->url ?: config('app.url');

        return new Content(
            view: 'emails.operations.document-uploaded',
            text: 'emails.plain.operations-notification',
            with: [
                'emailTitle' => 'وثيقة جديدة مرفوعة',
                'documentType' => $this->document['document_type'] ?? $this->document['type'] ?? null,
                'companyName' => $this->document['company_name'] ?? null,
                'expiresAt' => $this->document['expires_at'] ?? null,
                'aiStatus' => $this->document['ai_status'] ?? null,
                'url' => $url,
                'title' => 'وثيقة جديدة مرفوعة',
                'intro' => 'تم رفع وثيقة جديدة في بوابة آمر سبعة.',
                'actionLabel' => 'مراجعة الوثيقة',
                'lines' => [
                    'نوع الوثيقة' => $this->document['document_type'] ?? $this->document['type'] ?? null,
                    'المنشأة' => $this->document['company_name'] ?? null,
                    'تاريخ الانتهاء' => $this->document['expires_at'] ?? null,
                    'حالة التحليل الذكي' => $this->document['ai_status'] ?? null,
                ],
            ],
        );
    }
}
