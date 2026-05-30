<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, MorphMany};
use Illuminate\Database\Eloquent\Builder;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'ticket_number', 'subject', 'description', 'department_id',
        'priority', 'status', 'user_id', 'customer_id', 'service_request_id',
        'assigned_to', 'assigned_by', 'assigned_at', 'source_type',
        'source_reference', 'sla_deadline', 'last_reply_at',
    ];

    protected $casts = [
        'sla_deadline'  => 'datetime',
        'last_reply_at' => 'datetime',
        'assigned_at'   => 'datetime',
        'company_id'    => 'integer',
        'user_id'       => 'integer',
    ];

    protected $appends = ['status_label', 'status_color', 'priority_label', 'priority_color', 'is_overdue'];

    /* =============================
        العلاقات (RELATIONSHIPS)
    ============================== */

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function serviceRequest(): BelongsTo { return $this->belongsTo(ServiceRequest::class); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function assignedUser(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to'); }
    public function replies(): HasMany { return $this->hasMany(TicketReply::class, 'ticket_id'); }

    /**
     * 1. المرفقات المربوطة بالتذكرة مباشرة (Polymorphic)
     * تستخدم هذه العلاقة للوصول للمرفقات التي رُفعت أثناء إنشاء التذكرة فقط
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * 2. كافة المرفقات التابعة لهذه التذكرة (بما فيها مرفقات الردود)
     * تعتمد على عمود ticket_id الموجود في جدول attachments
     * هذه العلاقة هي التي ستستخدمها في صفحة عرض التذكرة لجلب كل الملفات
     */
    public function allAttachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'ticket_id');
    }

    /* =============================
        الخصائص الذكية (ACCESSORS)
    ============================== */

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'open'             => 'مفتوحة',
            'pending_agent'    => 'بانتظار الموظف',
            'pending_customer' => 'بانتظار العميل',
            'closed'           => 'مغلقة',
            default            => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'open'             => 'emerald',
            'pending_agent'    => 'orange',
            'pending_customer' => 'blue',
            'closed'           => 'zinc',
            default            => 'zinc',
        };
    }

    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            'high'   => 'عالية الأهمية',
            'medium' => 'متوسطة',
            'low'    => 'منخفضة',
            default  => $this->priority,
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'high'   => 'red',
            'medium' => 'orange',
            'low'    => 'blue',
            default  => 'zinc',
        };
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->sla_deadline && now()->greaterThan($this->sla_deadline) && $this->status !== 'closed';
    }

    /* =============================
        الفلاتر (SCOPES)
    ============================== */

    public function scopeOpen(Builder $query) { $query->where('status', '!=', 'closed'); }
    public function scopeOverdue(Builder $query) { $query->where('sla_deadline', '<', now())->where('status', '!=', 'closed'); }

    /* =============================
        المنطق التلقائي (BOOTED)
    ============================== */

    protected static function booted(): void
    {
        static::creating(function (Ticket $ticket) {
            // 🔢 توليد رقم التذكرة الاحترافي
            if (blank($ticket->ticket_number)) {
                $ticket->ticket_number = 'T-' . now()->year . '-' . str_pad((static::max('id') ?? 0) + 1, 6, '0', STR_PAD_LEFT);
            }

            // ⏱ حساب الموعد النهائي (SLA) بناءً على الأولوية
            if (blank($ticket->sla_deadline)) {
                $ticket->sla_deadline = now()->addHours(match ($ticket->priority) {
                    'high'   => 4,
                    'medium' => 12,
                    'low'    => 24,
                    default  => 48,
                });
            }
        });
    }
}