<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Subscription extends Model
{
    // Phase 9B: عُمدت أعمدة الـoperational nullable على مستوى الجدول.
    // إذا الـmigration لم تُشغَّل بعد، نملأها فقط لو موجودة فعلاً.
    protected $fillable = [
        'company_id',
        'package_id',
        'remaining_consultations',
        'starts_at',
        'expires_at',
        'status',
        // Phase 9B operational fields (all nullable in DB)
        'plan_code',
        'billing_period',
        'auto_renew',
        'cancelled_at',
        'cancellation_reason',
        'billing_cycle_anchor_at',
        'last_renewed_at',
        'next_renewal_at',
        'grace_ends_at',
        'metadata',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'remaining_consultations' => 'integer',
        // Phase 9B casts (safe even if columns are null)
        'auto_renew'              => 'boolean',
        'cancelled_at'            => 'datetime',
        'billing_cycle_anchor_at' => 'datetime',
        'last_renewed_at'         => 'datetime',
        'next_renewal_at'         => 'datetime',
        'grace_ends_at'           => 'datetime',
        'metadata'                => 'array',
    ];

    protected $appends = ['status_label', 'status_color', 'is_active'];

    /* =========================
     | العلاقات (Relationships)
     ========================= */

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    /* Phase 9B relations — usage / obligations / notifications / audit */

    public function items(): HasMany
    {
        return $this->hasMany(SubscriptionItem::class);
    }

    public function obligations(): HasMany
    {
        return $this->hasMany(ComplianceObligation::class);
    }

    public function obligationPeriods(): HasMany
    {
        return $this->hasMany(ObligationPeriod::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(SubscriptionStatusLog::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(SubscriptionNotification::class);
    }

    public function taxReturnRequests(): HasMany
    {
        return $this->hasMany(TaxReturnRequest::class);
    }

    /* =========================
     | الخصائص الذكية (Accessors)
     ========================= */

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active'   => 'نشط',
            'expired'  => 'منتهي',
            'canceled' => 'ملغي',
            'pending'  => 'في انتظار الدفع',
            default    => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'active'   => 'emerald',
            'expired'  => 'red',
            'canceled' => 'zinc',
            'pending'  => 'orange',
            default    => 'zinc',
        };
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active' && $this->expires_at->isFuture();
    }

    /* =========================
     | العمليات البرمجية (Methods)
     ========================= */

    /**
     * استهلاك استشارة من الرصيد
     */
    public function consumeConsultation(int $amount = 1): bool
    {
        if ($this->remaining_consultations >= $amount) {
            return $this->decrement('remaining_consultations', $amount);
        }
        
        return false;
    }

    /* =========================
     | الفلاتر (Scopes)
     ========================= */

    public function scopeActive(Builder $query): void
    {
        $query->where('status', 'active')
              ->where('expires_at', '>', now());
    }

    public function scopeExpired(Builder $query): void
    {
        $query->where('expires_at', '<=', now())
              ->orWhere('status', 'expired');
    }
}