<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

class ContactAutoReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $data;
    public string $locale;

    // نستقبل البيانات كمصفوفة لتوافق الكود مع التعديلات السابقة
    public function __construct(array $data, string $locale = 'ar')
    {
        $this->data = $data;
        $this->locale = $locale;
    }

    public function build()
    {
        // ضبط اللغة حسب لغة المتصفح وقت الطلب
        App::setLocale($this->locale);

        return $this->subject(__('شكراً لتواصلك مع آمر سبعة - تم استلام طلبك'))
            ->view('emails.contact_autoreply')
            ->with(['data' => $this->data]);
    }
}