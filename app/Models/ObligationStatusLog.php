<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Phase 9B — Audit trail for obligation_period status transitions.
 */
class ObligationStatusLog extends Model
{
    protected $fillable = [
        'obligation_period_id',
        'company_id',
        'from_status',
        'to_status',
        'changed_by_user_id',
        'reason',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(ObligationPeriod::class, 'obligation_period_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }
}
