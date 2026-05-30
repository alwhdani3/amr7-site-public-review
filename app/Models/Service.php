<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Service extends Model
{
    protected $fillable = [
        'service_platform_id',
        'title_ar',
        'title_en',
        'slug',
        'price',
        'excerpt_ar',
        'excerpt_en',
        'content_ar',
        'content_en',
        'is_active',
        'icon',
        'duration',
        'govt_fees',
        'features',
        'steps',
        'requirements',
        'conditions',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'price'      => 'decimal:2',
        'govt_fees'  => 'decimal:2',
        'features'   => 'array',
        'steps'      => 'array',
        'requirements' => 'array',
        'conditions' => 'array',
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    // ─── Relations ───────────────────────────────────────────────

    public function platform()
    {
        return $this->belongsTo(ServicePlatform::class, 'service_platform_id');
    }

    public function category()
    {
        return $this->hasOneThrough(
            ServiceCategory::class,
            ServicePlatform::class,
            'id',
            'id',
            'service_platform_id',
            'service_category_id'
        );
    }

    // ─── Locale Helpers ──────────────────────────────────────────

    protected function localeKey(): string
    {
        return app()->getLocale() === 'en' ? 'en' : 'ar';
    }

    protected function pick(?string $ar, ?string $en): string
    {
        $locale = $this->localeKey();
        if ($locale === 'en') {
            return trim((string)($en ?: $ar ?: ''));
        }
        return trim((string)($ar ?: $en ?: ''));
    }

    // ─── Localized Accessors ─────────────────────────────────────

    public function getTitleAttribute(): string
    {
        return $this->pick($this->attributes['title_ar'] ?? null, $this->attributes['title_en'] ?? null);
    }

    public function getExcerptAttribute(): string
    {
        return $this->pick($this->attributes['excerpt_ar'] ?? null, $this->attributes['excerpt_en'] ?? null);
    }

    public function getContentAttribute(): string
    {
        return $this->pick($this->attributes['content_ar'] ?? null, $this->attributes['content_en'] ?? null);
    }

    public function getFeaturesLocalizedAttribute()
    {
        return $this->localizedFlexible($this->features ?? null);
    }

    public function getStepsLocalizedAttribute()
    {
        return $this->localizedFlexible($this->steps ?? null);
    }

    public function getRequirementsLocalizedAttribute()
    {
        return $this->localizedFlexible($this->requirements ?? null);
    }

    public function getConditionsLocalizedAttribute()
    {
        return $this->localizedFlexible($this->conditions ?? null);
    }

    protected function localizedFlexible($value)
    {
        if (is_null($value)) {
            return null;
        }

        if (is_array($value)) {
            $locale = $this->localeKey();
            $ar = $value['ar'] ?? null;
            $en = $value['en'] ?? null;

            if ($ar === null && $en === null) {
                return $value;
            }

            return $locale === 'en' ? ($en ?: $ar) : ($ar ?: $en);
        }

        return $value;
    }

    // ─── Icon Accessor ───────────────────────────────────────────

    public function getIconUrlAttribute(): string
    {
        // لو عندها أيقونة خاصة — استخدمها
        if (!empty($this->attributes['icon'])) {
            return Storage::disk('public')->url($this->attributes['icon']);
        }

        // فول باك حسب المنصة
        $platformSlug = $this->platform?->slug ?? '';

        if (str_starts_with($platformSlug, 'ministry-of-commerce')) {
            return 'https://amr-7.sa/storage/services/icons/01KK2M9MZ2G8857D1KHX7CXZQ9.png';
        }

        // الافتراضي لكل الخدمات
        return 'https://amr-7.sa/storage/services/icons/01KFQRG7X23W70VAQD137KWQTC.png';
    }
}