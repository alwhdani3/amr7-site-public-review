<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',          // المالك (صاحب الهوية/الجواز)
        'title',            // اسم المستند (مثلاً: هوية وطنية)
        'type',             // passport, national_id, contract, other
        'document_number',  // رقم الهوية/الجواز
        'file_path',        // مسار الملف
        'issue_date',
        'expiry_date',
        'status',           // pending, approved, rejected, expired
        'notes',            // ملاحظات الإدارة (مثلاً سبب الرفض)
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
    ];

    /* =============================
        RELATIONSHIPS
    ============================== */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /* =============================
        ACCESSORS (تحسينات العرض)
    ============================== */

    /**
     * جلب رابط الملف (يدعم الملفات الخاصة والآمنة)
     */
    public function getFileUrlAttribute(): string
    {
        if (!$this->file_path) return '#';

        // إذا كان الملف public
        // return Storage::url($this->file_path);

        // إذا كان الملف محمي (Private) - نستخدم راوت التحميل الآمن
        // تأكد من وجود راوت باسم 'documents.download'
        return route('files.download', ['path' => $this->file_path]); 
    }

    /**
     * هل المستند منتهي الصلاحية؟
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    /**
     * الأيام المتبقية للانتهاء
     */
    public function getDaysRemainingAttribute(): int
    {
        if (!$this->expiry_date) return 0;
        return (int) now()->diffInDays($this->expiry_date, false);
    }

    /**
     * لون الحالة (UI Badge)
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'approved' => 'success', // أخضر
            'pending' => 'warning',  // أصفر
            'rejected' => 'danger',  // أحمر
            'expired' => 'secondary', // رمادي
            default => 'primary',
        };
    }

    /* =============================
        SCOPES (نطاقات البحث)
    ============================== */

    public function scopeExpired(Builder $query): void
    {
        $query->whereDate('expiry_date', '<=', now());
    }

    public function scopeValid(Builder $query): void
    {
        $query->where('status', 'approved')
              ->where(function($q) {
                  $q->whereNull('expiry_date')
                    ->orWhereDate('expiry_date', '>', now());
              });
    }

    /* =============================
        BOOTED (تحديث الحالة تلقائياً)
    ============================== */
    
    protected static function booted(): void
    {
        static::saving(function ($doc) {
            // تحويل الحالة إلى "منتهي" تلقائياً إذا انتهى التاريخ
            if ($doc->expiry_date && $doc->expiry_date->isPast() && $doc->status === 'approved') {
                $doc->status = 'expired';
            }
        });
    }
}