<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Phase 9B — Package feature row (relational + countable).
 * No business logic, no observers — only fillable/casts/relations.
 */
class PackageFeature extends Model
{
    protected $fillable = [
        'package_id',
        'feature_code',
        'label_ar',
        'label_en',
        'description_ar',
        'description_en',
        'quota',
        'unit',
        'is_highlighted',
        'sort_order',
        'metadata',
    ];

    protected $casts = [
        'quota'          => 'integer',
        'is_highlighted' => 'boolean',
        'sort_order'     => 'integer',
        'metadata'       => 'array',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function subscriptionItems(): HasMany
    {
        return $this->hasMany(SubscriptionItem::class);
    }
}
