<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Phase 9B — Operational instance of an obligation (e.g. "Q1-2026").
 *
 * status: upcoming | open | client_uploading | files_uploaded | under_review
 *       | client_approval | filed | late | missed | cancelled
 */
class ObligationPeriod extends Model
{
    protected $fillable = [
        'compliance_obligation_id',
        'company_id',
        'subscription_id',
        'period_label',
        'period_start',
        'period_end',
        'opens_at',
        'due_date',
        'status',
        'financial_statement_request_id',
        'tax_return_request_id',
        'closed_at',
        'closed_by_user_id',
        'metadata',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end'   => 'date',
        'opens_at'     => 'date',
        'due_date'     => 'date',
        'closed_at'    => 'datetime',
        'metadata'     => 'array',
    ];

    public function obligation(): BelongsTo
    {
        return $this->belongsTo(ComplianceObligation::class, 'compliance_obligation_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function financialStatementRequest(): BelongsTo
    {
        return $this->belongsTo(FinancialStatementRequest::class);
    }

    public function taxReturnRequest(): BelongsTo
    {
        return $this->belongsTo(TaxReturnRequest::class);
    }

    public function closedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by_user_id');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(ObligationStatusLog::class);
    }
}
