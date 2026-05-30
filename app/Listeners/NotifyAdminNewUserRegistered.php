<?php

namespace App\Listeners;

use App\Mail\Admin\NewUserRegisteredMail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class NotifyAdminNewUserRegistered implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(Registered $event): void
    {
        // جلب إيميل الإدارة من ملف .env أو استخدام الافتراضي
        $adminEmail = config('mail.admin_address', env('ADMIN_EMAIL', 'info@amr-7.sa'));

        if ($adminEmail) {
            Mail::to($adminEmail)->send(new NewUserRegisteredMail($event->user));
        }
    }
}
