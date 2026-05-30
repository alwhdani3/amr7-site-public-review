<?php

namespace App\Mail\Admin;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewUserRegisteredMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public User $user)
    {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            // ✅ جعلنا العنوان يدعم الترجمة (يأخذ الاسم من ملفات اللغة)
            subject: __('New User Registered: :name', ['name' => $this->user->name]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin.new-user-registered',
            with: [
                'user' => $this->user, // تمرير بيانات المستخدم لملف العرض
            ],
        );
    }
}