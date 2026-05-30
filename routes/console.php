<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| أوامر الكونسول (Console Commands)
|--------------------------------------------------------------------------
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| المهام المجدولة (Scheduled Tasks)
|--------------------------------------------------------------------------
*/

// 1. إغلاق التذاكر القديمة تلقائياً
Schedule::command('tickets:auto-close')
    ->dailyAt('03:00')        // يفضل تشغيله في وقت متأخر من الليل (مثلاً 3 فجراً)
    ->withoutOverlapping()    // يمنع تشغيل الأمر مرة أخرى إذا كانت المرة السابقة لم تنتهِ بعد
    ->runInBackground();      // يعمل في الخلفية لعدم تعطيل المهام الأخرى

// 2. تنظيف رموز المصادقة منتهية الصلاحية (Sanctum Tokens)
// هذا مهم جداً للحفاظ على سرعة قاعدة البيانات والأمان
Schedule::command('sanctum:prune-expired --hours=24')
    ->daily();

// 3. تنظيف رموز استعادة كلمة المرور القديمة
Schedule::command('auth:clear-resets')
    ->everyFifteenMinutes();

// 4. (اختياري) تنظيف الملفات المؤقتة أو المهام الفاشلة
Schedule::command('queue:prune-batches')->daily();

// 5. توليد sitemap.xml يومياً لمنع تقادمه (يومياً 02:30 قبل auto-close)
Schedule::command('sitemap:generate')
    ->dailyAt('02:30')
    ->withoutOverlapping()
    ->runInBackground();

// 6. P1.4 — تنبيهات وثائق المنشأة (CR وغيرها). database فقط الآن.
Schedule::command('docs:alert-expiring')
    ->dailyAt('07:00')
    ->withoutOverlapping()
    ->runInBackground();