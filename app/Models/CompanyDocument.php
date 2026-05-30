<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CompanyDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'type',             // نوع الوثيقة: سجل تجاري، شهادة زكاة، الخ
        'document_number',
        'issuer',           // الجهة المصدِرة (typed by the client)
        'issue_date',
        'expiry_date',
        'notes',            // ملاحظات حرة من العميل
        'file_path',
        'status',           // valid, expired, warning
        // P1.4 — حالة التنبيه
        'alert_stage',          // none | 60d | 30d | 7d | expired
        'alert_last_sent_at',
    ];

    protected $casts = [
        'issue_date'         => 'date',
        'expiry_date'        => 'date',
        'alert_last_sent_at' => 'datetime',
    ];

    /**
     * الـ Appends تجعل هذه القيم تظهر تلقائياً عند تحويل الموديل لـ JSON أو استخدامه في Livewire
     */
    protected $appends = [
        'url',              // الرابط الموحد والآمن
        'days_remaining',   // الأيام المتبقية للصلاحية
        'status_color',     // لون الحالة للواجهات
    ];

    /* ==========================================================================
       العلاقات (Relationships)
    ========================================================================== */

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /* ==========================================================================
       التحسينات والعرض (Accessors)
    ========================================================================== */

    /**
     * الاسم المستعار (Alias) لتوحيد الوصول للملفات في كامل المشروع
     */
    public function getUrlAttribute(): string
    {
        return $this->getFileUrlAttribute();
    }

    /**
     * رابط التنزيل الآمن - يمر عبر SecureFileController لضمان الخصوصية
     */
    public function getFileUrlAttribute(): string
    {
        if (! $this->file_path) {
            return '#';
        }

        // يوجه إلى المسار المعرف في web.php الذي يتحقق من الصلاحيات
        return route('company.docs.download', $this);
    }

    /**
     * حساب الأيام المتبقية (يعيد رقم سالب إذا كانت الوثيقة منتهية)
     */
    public function getDaysRemainingAttribute(): int
    {
        if (! $this->expiry_date) {
            return 0;
        }

        return (int) now()->diffInDays($this->expiry_date, false);
    }

    /**
     * تعيين لون الحالة (CSS Classes) لاستخدامه مباشرة في Blade
     */
    public function getStatusColorAttribute(): string
    {
        if ($this->isExpired()) {
            return 'bg-red-100 text-red-800';
        }

        if ($this->isExpiringSoon()) {
            return 'bg-yellow-100 text-yellow-800';
        }

        return 'bg-green-100 text-green-800';
    }

    /**
     * نص الحالة المحسوب لعرضه للمستخدم
     */
    public function getComputedStatusAttribute(): string
    {
        if ($this->isExpired()) {
            return 'منتهي';
        }

        if ($this->isExpiringSoon()) {
            return 'ينتهي قريباً';
        }

        return 'ساري';
    }

    /* ==========================================================================
       الدوال المساعدة (Helpers)
    ========================================================================== */

    public function isExpired(): bool
    {
        return (bool) ($this->expiry_date && $this->expiry_date->isPast());
    }

    public function isExpiringSoon(int $days = 30): bool
    {
        return ! $this->isExpired() && $this->days_remaining <= $days;
    }

    /* ==========================================================================
       نطاقات الاستعلام (Scopes)
    ========================================================================== */

    public function scopeValid(Builder $query): void
    {
        $query->whereDate('expiry_date', '>', now());
    }

    public function scopeExpired(Builder $query): void
    {
        $query->whereDate('expiry_date', '<=', now());
    }

    public function scopeExpiringWithin(Builder $query, int $days = 30): void
    {
        $query->whereDate('expiry_date', '>', now())
              ->whereDate('expiry_date', '<=', now()->addDays($days));
    }

    /* ==========================================================================
       P1.4 — تنبيهات الانتهاء (Stage helpers)
    ========================================================================== */

    /**
     * يحدد المرحلة الحالية لوثيقة حسب expiry_date:
     *   none → 60d → 30d → 7d → expired
     */
    public static function stageFor($expiryDate): string
    {
        if (! $expiryDate) {
            return 'none';
        }

        $expiry = $expiryDate instanceof \Carbon\CarbonInterface
            ? $expiryDate
            : \Carbon\Carbon::parse($expiryDate);

        if ($expiry->isPast()) {
            return 'expired';
        }

        $days = (int) now()->diffInDays($expiry, false);

        return match (true) {
            $days <= 7  => '7d',
            $days <= 30 => '30d',
            $days <= 60 => '60d',
            default     => 'none',
        };
    }

    public function currentStage(): string
    {
        return static::stageFor($this->expiry_date);
    }

    /**
     * وثائق تستحق تنبيهًا الآن: المرحلة الحالية ≠ alert_stage المُسجَّل.
     */
    public function scopeNeedingAlert(Builder $query): void
    {
        $query->whereNotNull('expiry_date');
    }

    /* ==========================================================================
       الأحداث التلقائية (Boot Logic)
    ========================================================================== */

    protected static function booted(): void
    {
        /**
         * عند الحفظ أو التعديل، نقوم بتحديث عمود status في قاعدة البيانات تلقائياً
         * لضمان توافق البيانات مع تواريخ الانتهاء دائماً.
         */
        static::saving(function (CompanyDocument $doc) {
            if ($doc->expiry_date) {
                if ($doc->expiry_date->isPast()) {
                    $doc->status = 'expired';
                } elseif (now()->diffInDays($doc->expiry_date) < 30) {
                    $doc->status = 'warning';
                } else {
                    $doc->status = 'valid';
                }
            }
        });

        /**
         * حذف الملف الفيزيائي من الهاردسك تلقائياً عند حذف السجل
         */
        static::deleted(function (CompanyDocument $doc) {
            if ($doc->file_path) {
                // نبحث في الـ private أولاً كونه المعيار الجديد
                if (Storage::disk('private')->exists($doc->file_path)) {
                    Storage::disk('private')->delete($doc->file_path);
                } elseif (Storage::disk('public')->exists($doc->file_path)) {
                    Storage::disk('public')->delete($doc->file_path);
                }
            }
        });
    }
}