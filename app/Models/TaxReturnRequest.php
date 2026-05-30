<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Phase 9B — Independent tax return request (VAT/Zakat/Withholding).
 * Mirrors the shape of FinancialStatementRequest but for tax workflows.
 *
 * status: draft | files_pending | under_review | client_approval
 *       | filed | rejected | cancelled
 */
class TaxReturnRequest extends Model
{
    protected static function booted(): void
    {
        // Auto-generate public_id on create so route key binding never fails.
        // Mirrors FinancialStatementRequest's pattern (REQ-XXX) with TR- prefix.
        static::creating(function (self $record) {
            if (blank($record->public_id)) {
                $record->public_id = 'TR-' . Str::upper(Str::random(12));
            }
        });
    }

    protected $fillable = [
        'public_id',
        'company_id',
        'subscription_id',
        'kind',
        'fiscal_period_start',
        'fiscal_period_end',
        'status',
        'total_sales',
        'total_purchases',
        'computed_tax_due',
        'submitted_to_zatca_at',
        'zatca_reference',
        'client_notes',
        'admin_notes',
        'client_approved_at',
        'client_approved_by_user_id',
        'reviewed_at',
        'reviewed_by_user_id',
        'metadata',
    ];

    protected $casts = [
        'fiscal_period_start'    => 'date',
        'fiscal_period_end'      => 'date',
        'submitted_to_zatca_at'  => 'datetime',
        'client_approved_at'     => 'datetime',
        'reviewed_at'            => 'datetime',
        'total_sales'            => 'decimal:2',
        'total_purchases'        => 'decimal:2',
        'computed_tax_due'       => 'decimal:2',
        'metadata'               => 'array',
    ];

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    /**
     * Defensive fallback: if a legacy record has a null public_id (the column is
     * nullable in DB), use the primary key so URL generation does not throw
     * "Missing required parameter for [Route: ...edit]".
     */
    public function getRouteKey()
    {
        return $this->public_id ?: $this->getKey();
    }

    /**
     * Resolve route bindings by either public_id (preferred) or id (fallback),
     * so legacy rows without public_id remain reachable.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->newQuery()
            ->where('public_id', $value)
            ->orWhere($this->getKeyName(), $value)
            ->first();
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function clientApprovedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_approved_by_user_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(TaxReturnFile::class);
    }

    public function obligationPeriods(): HasMany
    {
        return $this->hasMany(ObligationPeriod::class);
    }
}
