<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

if (!function_exists('get_setting')) {
    /**
     * جلب إعداد معين من قاعدة البيانات مع دعم الكاش والقيم الافتراضية
     *
     * @param string $key مفتاح الإعداد
     * @param mixed $default القيمة التي تعاد في حال عدم وجود الإعداد
     * @return mixed
     */
    function get_setting(string $key, $default = null)
    {
        try {
            // استخدام التخزين المؤقت للأبد لتخفيف الضغط على قاعدة البيانات
            return Cache::rememberForever("setting:{$key}", function () use ($key, $default) {
                // التحقق من وجود الجدول (مفيد جداً وقت الـ Migration أو التثبيت الأول)
                if (!Schema::hasTable('settings')) {
                    return $default;
                }

                $setting = Setting::where('key', $key)->first();
                
                // الموديل سيقوم بعمل JSON cast تلقائياً إذا كانت القيمة مصفوفة
                return $setting ? $setting->value : $default;
            });
        } catch (\Exception $e) {
            // في حال وجود خطأ تقني، نكتفي بإعادة القيمة الافتراضية بصمت
            return $default;
        }
    }
}