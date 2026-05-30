<?php

namespace App\Mail;

use App\Models\ServiceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class AdminNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $formData;
    public string $title;

    public function __construct(ServiceRequest|array $data, ?string $attachmentPath = null, string $title = 'إشعار طلب جديد')
    {
        if ($data instanceof ServiceRequest) {
            $this->formData = [
                'id' => $data->id,
                'service_id' => $data->service_id,
                'service_name' => $data->service?->title ?? $data->service?->name,
                'user_id' => $data->user_id,
                'company_id' => $data->company_id,
                'name' => $data->name,
                'email' => $data->email,
                'phone' => $data->phone,
                'applicant_type' => $data->applicant_type,
                'establishment_name' => $data->establishment_name,
                'cr_number' => $data->cr_number,
                'description' => $data->description,
                'status' => $data->status,
                'payment_method' => $data->payment_method ?? null,
                'attachment' => $attachmentPath ?: $data->attachment,
                'created_at' => $data->created_at,
            ];
        } else {
            $this->formData = $data;

            if ($attachmentPath && empty($this->formData['attachment'])) {
                $this->formData['attachment'] = $attachmentPath;
            }
        }

        $this->title = $title;
    }

    public function build()
    {
        $url = config('app.url') . '/amr7/service-requests';
        $subject = $this->title === 'إشعار طلب جديد' ? 'طلب خدمة جديد' : $this->title;

        $email = $this->subject($subject . ' - شركة آمر سبعة')
            ->view('emails.operations.service-request-admin')
            ->text('emails.plain.operations-notification')
            ->with([
                'emailTitle' => $subject,
                'name' => $this->formData['name'] ?? null,
                'phone' => $this->formData['phone'] ?? null,
                'email' => $this->formData['email'] ?? null,
                'serviceName' => $this->formData['service_name'] ?? $this->formData['service'] ?? null,
                'subject' => $this->formData['subject'] ?? $this->formData['establishment_name'] ?? null,
                'requestMessage' => $this->formData['message'] ?? $this->formData['description'] ?? null,
                'url' => $url,
                'title' => $subject,
                'intro' => 'وصل طلب خدمة جديد من موقع آمر سبعة.',
                'actionLabel' => 'فتح الطلب في لوحة التحكم',
                'lines' => [
                    'الاسم' => $this->formData['name'] ?? null,
                    'الجوال' => $this->formData['phone'] ?? null,
                    'البريد' => $this->formData['email'] ?? null,
                    'الخدمة المختارة' => $this->formData['service_name'] ?? $this->formData['service'] ?? null,
                    'الموضوع' => $this->formData['subject'] ?? $this->formData['establishment_name'] ?? null,
                    'الرسالة' => $this->formData['message'] ?? $this->formData['description'] ?? null,
                ],
            ]);

        if (!empty($this->formData['attachment'])) {
            $attachment = $this->formData['attachment'];

            if (Storage::disk('public')->exists($attachment)) {
                $email->attachFromStorageDisk('public', $attachment);
            } elseif (Storage::disk('private')->exists($attachment)) {
                $email->attachFromStorageDisk('private', $attachment);
            }
        }

        return $email;
    }
}
