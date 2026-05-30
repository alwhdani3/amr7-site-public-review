<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'created_by',
        'assigned_to',
        'department_id',
        'related_ticket_id',
        // Phase 9B — ربط اختياري بـsubscription/obligation/tax-return.
        // كل الأعمدة nullable في DB — الـTasks القديمة تبقى عاملة.
        'subscription_id',
        'compliance_obligation_id',
        'obligation_period_id',
        'tax_return_request_id',
        'status',
        'priority',
        'due_date',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];

    /* ─── STATUS / PRIORITY MAPS ─── */

    public static function statusOptions(): array
    {
        return [
            'pending' => 'قيد الانتظار',
            'in_progress' => 'قيد التنفيذ',
            'done' => 'مكتملة',
            'cancelled' => 'ملغاة',
        ];
    }

    public static function priorityOptions(): array
    {
        return [
            'low' => 'منخفضة',
            'normal' => 'عادية',
            'high' => 'عالية',
            'urgent' => 'عاجلة',
        ];
    }

    public static function statusColors(): array
    {
        return [
            'pending' => 'gray',
            'in_progress' => 'info',
            'done' => 'success',
            'cancelled' => 'danger',
        ];
    }

    public static function priorityColors(): array
    {
        return [
            'low' => 'gray',
            'normal' => 'primary',
            'high' => 'warning',
            'urgent' => 'danger',
        ];
    }

    /* ─── RELATIONS ─── */

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function relatedTicket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'related_ticket_id');
    }

    /* Phase 9B — nullable subscription / obligation / tax_return relations */

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function complianceObligation(): BelongsTo
    {
        return $this->belongsTo(ComplianceObligation::class);
    }

    public function obligationPeriod(): BelongsTo
    {
        return $this->belongsTo(ObligationPeriod::class);
    }

    public function taxReturnRequest(): BelongsTo
    {
        return $this->belongsTo(TaxReturnRequest::class);
    }

    /* ─── ACCESSORS ─── */

    public function getStatusLabelAttribute(): string
    {
        return static::statusOptions()[$this->status] ?? $this->status;
    }

    public function getPriorityLabelAttribute(): string
    {
        return static::priorityOptions()[$this->priority] ?? $this->priority;
    }

    public function getIsOverdueAttribute(): bool
    {
        return (bool) (
            $this->due_date
            && $this->due_date->isPast()
            && ! in_array($this->status, ['done', 'cancelled'], true)
        );
    }

    /* ─── SCOPES ─── */

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeDone(Builder $query): Builder
    {
        return $query->where('status', 'done');
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query
            ->whereNotIn('status', ['done', 'cancelled'])
            ->whereDate('due_date', '<', now());
    }

    public function scopeAssignedTo(Builder $query, int $userId): Builder
    {
        return $query->where('assigned_to', $userId);
    }
}