<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ServiceCategory extends Model
{
    protected $fillable = [
        'name_ar', 
        'name_en', 
        'is_active', 
        'department_id', 
        'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $appends = ['slug', 'name'];

    /**
     * جلب الاسم بناءً على لغة النظام الحالية تلقائياً
     */
    public function getNameAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->name_ar : ($this->name_en ?: $this->name_ar);
    }

    public function getSlugAttribute(): string
    {
        return Str::slug($this->name_en ?: $this->name_ar) ?: (string) $this->id;
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function platforms(): HasMany
    {
        return $this->hasMany(ServicePlatform::class, 'service_category_id');
    }

    /**
     * سكوب لجلب التصنيفات النشطة فقط
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * سكوب للترتيب الافتراضي
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order', 'asc');
    }
}