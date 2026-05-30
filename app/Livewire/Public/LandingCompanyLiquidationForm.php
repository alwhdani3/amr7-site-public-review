<?php

namespace App\Livewire\Public;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage; // 👈 ضروري للتعامل الآمن مع الملفات

class LandingCompanyLiquidationForm extends Component
{
    use WithFileUploads;

    #[Validate('required|string|min:3|max:150', message: 'الاسم مطلوب')]
    public $name = '';

    #[Validate('required|string|min:9|max:30', message: 'رقم الجوال مطلوب')]
    public $phone = '';

    #[Validate('nullable|string|max:80')]
    public $city = 'غير محدد';

    #[Validate('required|string|min:10|max:2000', message: 'اكتب تفاصيل وضع الشركة (هل توجد ديون؟)')]
    public $notes = '';

    // 👈 تمت إضافة صيغ الوورد والإكسل (xls, xlsx) لأن تصفية الشركات تتضمن غالباً جداول ديون وقوائم مالية
    #[Validate('nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx|max:10240')]
    public $attachment = null;

    public function submit()
    {
        $this->validate();

        $filePath = null;
        $hasAttachment = false;
        
        // 1) معالجة المرفق (تخزين آمن ومخفي)
        try {
            if ($this->attachment) {
                // 🔒 التخزين في القرص المحلي (Private) لحماية القوائم المالية والوثائق الحساسة
                $filePath = $this->attachment->store('leads/liquidation', 'local');
                $hasAttachment = true;
            }
        } catch (\Throwable $e) {
            Log::error("Liquidation Attachment Error: " . $e->getMessage());
        }

        // 2) إرسال الإيميل
        try {
            $subject = "Lead: تصفية شركة - {$this->name}";
            $attachmentNote = $hasAttachment ? "يوجد ملف مرفق في هذا الإيميل." : "لا يوجد مرفقات.";
            
            $body = "طلب تصفية شركة جديد (من صفحة الهبوط)\n" .
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
            Log::error("Liquidation Mail Error: " . $e->getMessage());
        }

        // 3) تجهيز رسالة الواتساب
        $waMessage = urlencode(
            "*طلب تصفية شركة (من الموقع)*\n" .
            "الاسم: {$this->name}\n" .
            "الجوال: {$this->phone}\n" .
            "المدينة: {$this->city}\n" .
            "--------------\n" .
            "{$this->notes}"
        );
        
        $waLink = "https://wa.me/966505336956?text={$waMessage}";

        // 4) تفريغ الحقول وإعادة التوجيه (مع إضافة city للقائمة)
        $this->reset(['name', 'phone', 'city', 'notes', 'attachment']);
        
        // وضع رسالة النجاح قبل الـ Dispatch
        session()->flash('landing_success', 'تم استلام طلب التصفية بنجاح! سيتم تحويلك للواتساب الآن...');
        
        $this->dispatch('open-whatsapp', url: $waLink);
    }

    public function render()
    {
        // إعادة استخدام نفس واجهة الـ Form هو قرار معماري ممتاز (Component Reusability)
        return view('livewire.public.landing-company-formation-form'); 
    }
}