<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class Partner extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'url',
        'logo',
        'is_active',
        'sort_order', // إضافة حقل الترتيب يعطي تحكم أفضل في العرض
    ];

    protected $casts = [
        'logo' => 'array', 
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $appends = ['logo_url'];

    /**
     * الحصول على الرابط المباشر للشعار
     */
    public function getLogoUrlAttribute(): ?string
    {
        if (empty($this->logo) || !isset($this->logo['path'])) {
            return asset('images/partner-placeholder.png');
        }

        return Storage::disk($this->logo['disk'] ?? 'public')->url($this->logo['path']);
    }

    /**
     * سكوب لجلب الشركاء النشطين فقط
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * الترتيب الافتراضي (حسب الأولوية ثم الأحدث)
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order', 'asc')->latest();
    }
}