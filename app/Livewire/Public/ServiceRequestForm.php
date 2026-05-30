<?php

namespace App\Livewire\Public;

use App\Mail\AdminNotificationMail;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Traits\HasSEO;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class ServiceRequestForm extends Component
{
    use WithFileUploads;
    use HasSEO;

    public ?int $service_id = null;
    public ?Service $service = null;

    public string $applicant_type = 'person';
    public string $applicant_name = '';
    public string $applicant_email = '';
    public string $phone = '';
    public ?string $notes = null;

    public string $establishment_name = '';
    public string $cr_number = '';

    public $attachment = null;
    public bool $agreed_terms = false;

    public function mount(?int $serviceId = null): void
    {
        if ($serviceId) {
            $this->service = Service::query()
                ->whereKey($serviceId)
                ->where('is_active', true)
                ->firstOrFail();

            $this->service_id = $this->service->id;
        }
    }

    #[Computed]
    public function services(): Collection
    {
        if ($this->service) {
            return collect();
        }

        return Service::query()
            ->select(['id', 'title_ar', 'title_en'])
            ->where('is_active', true)
            ->orderBy('id')
            ->get();
    }

    protected function rules(): array
    {
        return [
            'applicant_type'     => 'required|in:person,company',
            'applicant_name'     => 'required|string|min:3|max:255',
            'applicant_email'    => 'nullable|email|max:255',
            'phone'              => ['required', 'string', 'min:9', 'max:30', 'regex:/^(\+?\d{1,3})?[\s\-]?\d{7,14}$/'],
            'service_id'         => 'required|exists:services,id',
            'notes'              => 'required|string|min:5|max:5000',
            'establishment_name' => 'nullable|string|max:255',
            'cr_number'          => 'nullable|string|max:50',
            'attachment'         => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'agreed_terms'       => 'accepted',
        ];
    }

    protected array $messages = [
        'applicant_name.required' => 'الاسم مطلوب.',
        'applicant_name.min'      => 'الاسم يجب أن يكون 3 أحرف على الأقل.',
        'applicant_email.email'   => 'البريد الإلكتروني غير صحيح.',
        'phone.required'          => 'رقم الجوال مطلوب.',
        'phone.regex'             => 'رقم الجوال غير صحيح.',
        'service_id.required'     => 'يرجى اختيار الخدمة.',
        'service_id.exists'       => 'الخدمة المختارة غير موجودة.',
        'notes.required'          => 'يرجى كتابة تفاصيل الطلب.',
        'notes.min'               => 'تفاصيل الطلب قصيرة جدًا.',
        'attachment.mimes'        => 'صيغة الملف غير مدعومة.',
        'attachment.max'          => 'حجم الملف أكبر من المسموح.',
        'agreed_terms.accepted'   => 'يجب الموافقة على الشروط والأحكام.',
    ];

    public function submit(): void
    {
        $this->phone = preg_replace('/[\s\-]/', '', $this->phone) ?: $this->phone;

        $validated = $this->validate();

        $path = null;

        if ($this->attachment) {
            try {
                $ext = strtolower((string) $this->attachment->getClientOriginalExtension());
                $safeName = 'sr_' . now()->format('Ymd_His') . '_' . Str::random(8) . ($ext ? '.' . $ext : '');

                $path = $this->attachment->storeAs(
                    'service-requests/attachments/' . now()->format('Y/m'),
                    $safeName,
                    'private'
                );
            } catch (\Throwable $e) {
                Log::error('ServiceRequestForm upload error', [
                    'message' => $e->getMessage(),
                ]);

                $this->addError('attachment', 'تعذر رفع الملف. جرّب ملفًا آخر.');
                return;
            }
        }

        try {
            $notes = is_string($this->notes) ? trim($this->notes) : '';
            $email = trim((string) $this->applicant_email);

            $desc = "اسم مقدم الطلب: " . $this->applicant_name . "\n"
                . "البريد الإلكتروني: " . ($email !== '' ? $email : '-') . "\n"
                . "رقم الجوال: " . $this->phone . "\n"
                . "نوع مقدم الطلب: " . $this->applicant_type . "\n";

            if ($this->establishment_name !== '') {
                $desc .= "اسم المنشأة: " . $this->establishment_name . "\n";
            }

            if ($this->cr_number !== '') {
                $desc .= "رقم السجل التجاري: " . $this->cr_number . "\n";
            }

            $desc .= "--------------------------\n" . $notes;

            $request = ServiceRequest::create([
                'user_id'            => auth()->id(),
                'company_id'         => null,
                'service_id'         => $validated['service_id'],
                'payment_method'     => 'bank_transfer',
                'status'             => 'pending',
                'name'               => $this->applicant_name,
                'email'              => $email !== '' ? $email : null,
                'phone'              => $this->phone,
                'description'        => $desc,
                'attachment'         => $path,
                'applicant_type'     => $this->applicant_type,
                'establishment_name' => $this->establishment_name !== '' ? $this->establishment_name : null,
                'cr_number'          => $this->cr_number !== '' ? $this->cr_number : null,
            ]);

            try {
                Log::info('ServiceRequestForm: before mail send', [
                    'request_id' => $request->id,
                    'to' => config('mail.admin_notification_email', 'info@amr-7.sa'),
                ]);

                Mail::to(config('mail.admin_notification_email', 'info@amr-7.sa'))
                    ->send(new AdminNotificationMail($request, $path));

                Log::info('ServiceRequestForm: mail sent successfully', [
                    'request_id' => $request->id,
                    'to' => config('mail.admin_notification_email', 'info@amr-7.sa'),
                ]);
            } catch (\Throwable $e) {
                Log::error('ServiceRequestForm mail error', [
                    'request_id' => $request->id ?? null,
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
            }

            $selectedServiceId = $this->service?->id;

            $this->reset([
                'applicant_type',
                'applicant_name',
                'applicant_email',
                'phone',
                'notes',
                'attachment',
                'establishment_name',
                'cr_number',
                'agreed_terms',
            ]);

            if ($selectedServiceId) {
                $this->service_id = $selectedServiceId;
                $this->service = Service::query()
                    ->whereKey($selectedServiceId)
                    ->where('is_active', true)
                    ->first();
            } else {
                $this->service_id = null;
                $this->service = null;
            }

            $this->dispatch(
                'service-request-sent',
                message: 'تم إرسال طلبك بنجاح! سنتواصل معك قريبًا.'
            );
        } catch (\Throwable $e) {
            Log::error('ServiceRequestForm DB error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            $this->addError('general', 'حدث خطأ فني، يرجى المحاولة لاحقًا.');
        }
    }

    public function clearAttachment(): void
    {
        $this->reset('attachment');
        $this->resetValidation('attachment');
    }

    public function render()
    {
        return view('livewire.public.service-request-form', [
            'services' => $this->services,
        ]);
    }
}
