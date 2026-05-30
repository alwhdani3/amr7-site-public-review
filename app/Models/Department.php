<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /* =============================
        BOOT
    ============================== */

    protected static function booted(): void
    {
        // توليد slug تلقائي عند الإنشاء
        static::creating(function (Department $department) {
            if (empty($department->slug)) {
                $department->slug = static::generateUniqueSlug($department->name);
            }
        });

        // تحديث slug عند تعديل الاسم فقط إذا كان slug فارغاً
        static::updating(function (Department $department) {
            if ($department->isDirty('name') && empty($department->slug)) {
                $department->slug = static::generateUniqueSlug($department->name, $department->id);
            }
        });
    }

    /**
     * توليد slug فريد — يضيف رقم تسلسلي إذا كان موجوداً مسبقاً
     */
    protected static function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i    = 1;

        while (
            static::query()
                ->where('slug', $slug)
                ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    /* =============================
        ROUTE KEY
    ============================== */

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /* =============================
        RELATIONS
    ============================== */

    /** الموظفون التابعون لهذا القسم */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /** التذاكر الموجهة لهذا القسم */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    /** المهام المرتبطة بهذا القسم */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /** تصنيفات الخدمات */
    public function serviceCategories(): HasMany
    {
        return $this->hasMany(ServiceCategory::class);
    }

    /* =============================
        SCOPES
    ============================== */

    /** الأقسام النشطة فقط */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
