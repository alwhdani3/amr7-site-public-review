<?php

namespace App\Providers;

use App\Listeners\NotifyAdminNewUserRegistered;
use App\Listeners\SendWelcomeEmail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * خريطة الأحداث والمستمعين (Event-to-Listener mapping)
     */
    protected $listen = [
        // عند تسجيل مستخدم جديد (سواء عبر Fortify أو يدوياً)
        Registered::class => [
            SendWelcomeEmail::class,             // إرسال بريد ترحيبي للعميل
            NotifyAdminNewUserRegistered::class, // تنبيه إدارة "آمر سبعة" بوجود عضو جديد
        ],

        // يمكنك إضافة أحداث التذاكر هنا مستقبلاً، مثل:
        // 'App\Events\TicketCreated' => [
        //     'App\Listeners\AssignAgentToTicket',
        // ],
    ];

    /**
     * تسجيل الأحداث عند بدء التطبيق
     */
    public function boot(): void
    {
        parent::boot();
    }

    /**
     * هل يجب على لارافيل اكتشاف الأحداث تلقائياً؟
     * نفضل تركها false طالما نقوم بتعريفها يدوياً بدقة هنا.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}