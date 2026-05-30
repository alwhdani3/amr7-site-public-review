<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialStatementStatusLog extends Model
{
    protected $fillable = [
        'financial_statement_request_id',
        'from_status',
        'to_status',
        'changed_by',
        'note',
    ];

    protected $casts = [
        'financial_statement_request_id' => 'integer',
        'changed_by' => 'integer',
        'created_at' => 'datetime',
    ];

    protected $appends = ['from_label', 'to_label', 'changed_at_human'];

    public function request(): BelongsTo
    {
        return $this->belongsTo(FinancialStatementRequest::class, 'financial_statement_request_id');
    }

    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function getFromLabelAttribute(): string
    {
        return $this->getStatusLabel($this->from_status);
    }

    public function getToLabelAttribute(): string
    {
        return $this->getStatusLabel($this->to_status);
    }

    public function getChangedAtHumanAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    private function getStatusLabel(?string $status): string
    {
        if (!$status) return '—';
        
        return match ($status) {
            'new'             => 'جديد',
            'waiting_docs'    => 'بانتظار مستندات',
            'in_review'       => 'قيد المراجعة',
            'client_approval' => 'بانتظار اعتماد العميل',
            'moci_approval'   => 'بانتظار اعتماد وزارة التجارة',
            'completed'       => 'مكتمل',
            'closed'          => 'مغلق',
            'cancelled'       => 'ملغي',
            default           => $status,
        };
    }
}