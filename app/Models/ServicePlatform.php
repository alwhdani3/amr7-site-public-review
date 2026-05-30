<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class ServicePlatform extends Model
{
   protected $fillable = [
    'service_category_id',
    'name_ar',
    'name_en',
    'slug',              // ✅ أضفها
    'is_active',
    'image',
    'hero_image',
    'description_ar',
];

    protected $casts = [
        'is_active' => 'boolean',
        'service_category_id' => 'integer',
    ];

    protected $appends = ['name', 'image_url', 'hero_url'];

    /* =========================
     | Accessors (خصائص إضافية)
     ========================= */

    /**
     * جلب الاسم بناءً على لغة النظام
     * - يدعم alias "name" إذا كان موجود من الاستعلام
     * - ويضمن عدم رجوع null لأن return type string
     */
    public function getNameAttribute(): string
    {
        // 1) لو الاستعلام جايب alias باسم "name" (مثلاً: select name_ar as name)
        $raw = $this->attributes['name'] ?? null;
        if (is_string($raw) && trim($raw) !== '') {
            return $raw;
        }

        // 2) fallback على الأعمدة الأصلية حتى لو كانت غير محمّلة بالكامل
        $ar = $this->attributes['name_ar'] ?? null;
        $en = $this->attributes['name_en'] ?? null;

        $value = app()->getLocale() === 'ar'
            ? ($ar ?: $en)
            : ($en ?: $ar);

        // 3) ضمان string دائمًا
        return is_string($value) ? $value : '';
    }

    /**
     * رابط الأيقونة/الصورة المصغرة
     */
    public function getImageUrlAttribute(): ?string
    {
        return $this->image
            ? Storage::disk('public')->url($this->image)
            : asset('images/platform-placeholder.png');
    }

    /**
     * رابط صورة الـ Hero (البانر العلوي)
     */
    public function getHeroUrlAttribute(): ?string
    {
        return $this->hero_image
            ? Storage::disk('public')->url($this->hero_image)
            : null;
    }

    /* =========================
     | Scopes (فلاتر الاستعلام)
     ========================= */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /* =========================
     | Relationships (العلاقات)
     ========================= */

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }
}
