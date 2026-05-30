<?php

namespace App\Livewire\Public;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage; // 👈 استدعاء واجهة التخزين

class LandingCompanyFormationForm extends Component
{
    use WithFileUploads;

    #[Validate('required|string|min:3|max:150', message: 'الاسم مطلوب')]
    public $name = '';

    #[Validate('required|string|min:9|max:30', message: 'رقم الجوال مطلوب')]
    public $phone = '';

    #[Validate('nullable|string|max:80')]
    public $city = 'الرياض';

    #[Validate('required|string|min:10|max:2000', message: 'اكتب تفاصيل طلبك')]
    public $notes = '';

    // تم إضافة mimes لملفات الوورد تحسباً لرفع سير ذاتية أو مسودات عقود
    #[Validate('nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240')]
    public $attachment = null;

    public function submit()
    {
        $this->validate();

        $filePath = null;
        $hasAttachment = false;
        
        // 1) معالجة المرفق (تخزين آمن ومخفي عن العامة)
        try {
            if ($this->attachment) {
                // 🔒 التخزين في القرص المحلي (Private) لحماية بيانات العملاء
                $filePath = $this->attachment->store('leads/company-formation', 'local');
                $hasAttachment = true;
            }
        } catch (\Throwable $e) {
            Log::error("Attachment Error: " . $e->getMessage());
        }

        // 2) إرسال الإيميل
        try {
            $subject = "Lead: تأسيس شركة - {$this->name}";
            $attachmentNote = $hasAttachment ? "يوجد ملف مرفق في هذا الإيميل." : "لا يوجد مرفقات.";
            
            $body = "طلب جديد من صفحة الهبوط (تأسيس الشركات)\n" .
                    "===================================\n\n" .
                    "الاسم: {$this->name}\n" .
                    "الجوال: {$this->phone}\n" .
                    "المدينة: {$this->city}\n" .
                    "التفاصيل:\n{$this->notes}\n\n" .
                    "المرفقات: {$attachmentNote}\n";

            Mail::raw($body, function ($mail) use ($subject, $filePath) {
                $mail->to('info@amr-7.sa')
                     ->subject($subject);
                
                // 📎 إرفاق الملف فعلياً وقراءته من القرص الخاص الآمن
                if ($filePath && Storage::disk('local')->exists($filePath)) {
                    $mail->attach(Storage::disk('local')->path($filePath));
                }
            });

        } catch (\Throwable $e) {
            Log::error("Mail Error: " . $e->getMessage());
        }

        // 3) تجهيز رسالة الواتساب الشاملة (لضمان وصول كامل السياق لموظف المبيعات)
        $waMessage = urlencode(
            "*طلب تأسيس شركة (من الموقع)*\n" .
            "الاسم: {$this->name}\n" .
            "الجوال: {$this->phone}\n" .
            "المدينة: {$this->city}\n" . // إضافة المدينة لرسالة الواتس
            "--------------\n" .
            "{$this->notes}"
        );
        
        $waLink = "https://wa.me/966505336956?text={$waMessage}";

        // 4) إعادة التوجيه وفتح الواتساب
        $this->reset(['name', 'phone', 'city', 'notes', 'attachment']);
        
        // Flash Session قبل الـ Dispatch لضمان ظهور الرسالة فور العودة للموقع
        session()->flash('landing_success', 'تم استلام طلبك بنجاح! سيتم تحويلك للواتساب الآن...');
        
        $this->dispatch('open-whatsapp', url: $waLink);
    }

    public function render()
    {
        return view('livewire.public.landing-company-formation-form');
    }
}