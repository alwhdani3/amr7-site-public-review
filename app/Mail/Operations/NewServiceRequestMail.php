<?php

namespace App\Mail\Operations;

use App\Models\ServiceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewServiceRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ServiceRequest|array $requestData,
        public ?string $url = null,
        public string $subjectLine = 'طلب خدمة جديد - شركة آمر سبعة لحلول الأعمال',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine);
    }

    public function content(): Content
    {
        $data = $this->toArray($this->requestData);
        $url = $this->url ?: config('app.url') . '/amr7/service-requests';

        return new Content(
            view: 'emails.operations.service-request-admin',
            text: 'emails.plain.operations-notification',
            with: [
                'emailTitle' => 'طلب خدمة جديد',
                'name' => $data['name'] ?? null,
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'serviceName' => $data['service_name'] ?? $data['service'] ?? null,
                'subject' => $data['subject'] ?? $data['establishment_name'] ?? null,
                'requestMessage' => $data['message'] ?? $data['description'] ?? null,
                'url' => $url,
                'title' => 'طلب خدمة جديد',
                'intro' => 'وصل طلب خدمة جديد من موقع آمر سبعة.',
                'actionLabel' => 'فتح الطلب في لوحة التحكم',
                'lines' => [
                    'الاسم' => $data['name'] ?? null,
                    'الجوال' => $data['phone'] ?? null,
                    'البريد' => $data['email'] ?? null,
                    'الخدمة المختارة' => $data['service_name'] ?? $data['service'] ?? null,
                    'الموضوع' => $data['subject'] ?? $data['establishment_name'] ?? null,
                    'الرسالة' => $data['message'] ?? $data['description'] ?? null,
                ],
            ],
        );
    }

    private function toArray(ServiceRequest|array $data): array
    {
        if (is_array($data)) {
            return $data;
        }

        return [
            'name' => $data->name,
            'phone' => $data->phone,
            'email' => $data->email,
            'service_name' => $data->service?->title ?? $data->service?->name,
            'subject' => $data->establishment_name,
            'description' => $data->description,
        ];
    }
}
