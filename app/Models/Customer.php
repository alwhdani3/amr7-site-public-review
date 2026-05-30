<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

/**
 * Customer = walk-in / legacy contact (see docs/data-model.md).
 *
 * This is NOT the primary client model for the portal. Real clients are
 * represented as User + Company linked via the `company_user` pivot.
 *
 * Customer exists only to hold a person's contact details (name, email,
 * phone, national_id) attached to a Company when there is no login
 * account — for example a walk-in customer who needs a support ticket
 * opened on their behalf. Today it is used by Ticket.customer_id only.
 *
 * Customer has no `user_id` column and is intentionally not connected to
 * the auth system. Promoting a Customer to a logged-in client today
 * means: create a User, attach it to the target Company via
 * `company_user` with role=admin, and (optionally) keep the Customer
 * row for historical tickets.
 */
class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'email',
        'phone',
        'national_id',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /* =============================
        العلاقات (Relationships)
    ============================== */

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(TicketReply::class);
    }

    /* =============================
        Mutators & Accessors (المعالجات)
    ============================== */

    /**
     * توحيد صيغة الإيميل (حروف صغيرة دائماً)
     */
    protected function email(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => strtolower(trim($value)),
        );
    }

    /**
     * تنظيف وتوحيد رقم الجوال تلقائياً عند الحفظ
     * يحول 05xxxxxxxx إلى 9665xxxxxxxx
     */
    protected function phone(): Attribute
    {
        return Attribute::make(
            set: function ($value) {
                // إزالة أي رموز غير رقمية
                $clean = preg_replace('/\D/', '', $value);

                // تحويل الصيغة المحلية للدولية
                if (str_starts_with($clean, '05')) {
                    return '966' . substr($clean, 1);
                }
                
                return $clean;
            }
        );
    }

    /**
     * جلب صورة افتراضية (Avatar) بناءً على الأحرف الأولى من الاسم
     * الاستخدام: <img src="{{ $customer->avatar_url }}">
     */
    public function getAvatarUrlAttribute(): string
    {
        $name = urlencode($this->name);
        return "https://ui-avatars.com/api/?name={$name}&color=ffffff&background=1FA7A2&bold=true";
    }

    /* =============================
        Scopes (نطاقات البحث والفلترة)
    ============================== */

    /**
     * جلب العملاء النشطين فقط
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * بحث شامل (بالاسم، الإيميل، الجوال، أو الهوية)
     * الاستخدام: Customer::search($keyword)->get();
     */
    public function scopeSearch(Builder $query, string $term): void
    {
        $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%")
              ->orWhere('phone', 'like', "%{$term}%")
              ->orWhere('national_id', 'like', "%{$term}%");
        });
    }
}