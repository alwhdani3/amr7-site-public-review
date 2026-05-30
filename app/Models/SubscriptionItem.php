<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Phase 9B — Per-subscription feature usage tracker.
 */
class SubscriptionItem extends Model
{
    protected $fillable = [
        'subscription_id',
        'package_feature_id',
        'feature_code',
        'quota_limit',
        'quota_used',
        'period_start',
        'period_end',
        'metadata',
    ];

    protected $casts = [
        'quota_limit'  => 'integer',
        'quota_used'   => 'integer',
        'period_start' => 'date',
        'period_end'   => 'date',
        'metadata'     => 'array',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function packageFeature(): BelongsTo
    {
        return $this->belongsTo(PackageFeature::class);
    }
}
