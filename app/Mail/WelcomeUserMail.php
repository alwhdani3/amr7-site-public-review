<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeUserMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    // تعريف المتغير كـ Public يجعله متاحاً تلقائياً في ملف العرض، لكن سنمرره يدوياً للتأكيد
    public function __construct(public User $user)
    {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            // ✅ جعلنا العنوان يقبل الترجمة بدلاً من النص الثابت
            subject: __('Welcome to :app_name', ['app_name' => config('app.name')]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.auth.welcome',
            with: [
                'user' => $this->user, // تمرير بيانات المستخدم لملف التصميم
            ],
        );
    }
}