<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Phase 9B — Master record for any recurring obligation on a company.
 *
 * obligation_type examples:
 *   quarterly_vat | monthly_vat | annual_financial_statement
 *   monthly_payroll | gosi_certificate | cr_renewal | document_expiry
 */
class ComplianceObligation extends Model
{
    protected $fillable = [
        'company_id',
        'subscription_id',
        'obligation_type',
        'recurrence',
        'title_ar',
        'title_en',
        'next_due_at',
        'starts_at',
        'ends_at',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'next_due_at' => 'date',
        'starts_at'   => 'date',
        'ends_at'     => 'date',
        'is_active'   => 'boolean',
        'metadata'    => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function periods(): HasMany
    {
        return $this->hasMany(ObligationPeriod::class);
    }
}
