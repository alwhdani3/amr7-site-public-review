<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'user_id',
        'company_id', // ضروري لربط الطلب بالشركة إذا وجد
        'status',

        // ✅ بيانات مقدم الطلب (Guest أو Auth)
        'name',
        'email',
        'applicant_type', // person | company

        // بيانات التواصل
        'phone',

        // تفاصيل الطلب
        'establishment_name',
        'cr_number',
        'description',

        // ملف مرفق (قديم - إذا كنت لا تزال تستخدمه بجانب جدول المرفقات)
        'attachment',

        // دفعات/فواتير
        'payment_method',

        // مصدر الطلب وطريقة التواصل المفضلة
        'source',
        'preferred_contact_method',
    ];

    protected $casts = [
        'service_id' => 'integer',
        'user_id'    => 'integer',
        'company_id' => 'integer',
    ];

    // =============================
    //       Relationships
    // =============================

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ServiceRequestMessage::class);
    }

    /**
     * Polymorphic attachments uploaded with the wizard. Reuses the existing
     * `attachments` table (Ticket also uses it) via the `attachable_*`
     * columns so no new migration is needed.
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    // =============================
    //      Accessors (Status)
    // =============================

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending'    => 'قيد المراجعة',
            'new'        => 'جديد',
            'processing' => 'قيد التنفيذ',
            'completed'  => 'مكتمل',
            'rejected'   => 'مرفوض',
            'canceled'   => 'ملغي',
            default      => (string) $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending'    => 'amber',
            'new'        => 'blue',
            'processing' => 'cyan',
            'completed'  => 'emerald',
            'rejected', 'canceled' => 'rose',
            default      => 'zinc',
        };
    }
}