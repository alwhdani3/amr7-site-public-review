<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * جلب قيمة إعداد معين مع دعم التخزين المؤقت (Cache)
     */
    public static function get(string $key, $default = null)
    {
        $value = Cache::rememberForever("setting:{$key}", function () use ($key) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : null;
        });

        return $value ?? $default;
    }

    /**
     * تحديث أو إنشاء إعداد جديد مع مسح التخزين المؤقت تلقائياً
     */
    public static function set(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /**
     * تحقق من وجود إعداد معين
     */
    public static function has(string $key): bool
    {
        return static::get($key) !== null;
    }

    /**
     * التعامل مع البيانات المعقدة (مثل المصفوفات) تلقائياً
     */
    protected function casts(): array
    {
        return [
            'value' => 'json', // يسمح لك بتخزين مصفوفات أو كائنات في عمود القيمة
        ];
    }

    /**
     * منطق مسح الكاش عند أي تغيير
     */
    protected static function booted(): void
    {
        static::saved(fn ($m) => Cache::forget("setting:{$m->key}"));
        static::deleted(fn ($m) => Cache::forget("setting:{$m->key}"));
    }
}