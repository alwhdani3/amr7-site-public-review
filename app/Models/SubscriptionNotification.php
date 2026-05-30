<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Phase 9B — Sent-notifications log for subscriptions + obligations.
 * Phase 9F will write/dedup against this table.
 *
 * kind: expiring_30d | expiring_14d | expiring_7d | expiring_3d | expiring_1d
 *     | expired | renewed | obligation_opened | obligation_late
 */
class SubscriptionNotification extends Model
{
    protected $fillable = [
        'subscription_id',
        'company_id',
        'user_id',
        'kind',
        'channel',
        'sent_at',
        'payload',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'payload' => 'array',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
