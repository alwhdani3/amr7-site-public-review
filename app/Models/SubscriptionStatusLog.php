<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Phase 9B — Audit trail for subscription status transitions.
 */
class SubscriptionStatusLog extends Model
{
    protected $fillable = [
        'subscription_id',
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

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
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
