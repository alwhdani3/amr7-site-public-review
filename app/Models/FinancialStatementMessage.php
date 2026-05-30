<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialStatementMessage extends Model
{
    protected $fillable = [
        'financial_statement_request_id',
        'sender_id',
        'sender_type', // client | staff
        'body',
        'attachments',
    ];

    protected $casts = [
        'attachments' => 'array',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(FinancialStatementRequest::class, 'financial_statement_request_id');
    }

    public function senderUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function getSenderLabelAttribute(): string
    {
        if ($this->sender_type === 'staff') {
            return $this->senderUser?->name ?: 'موظف';
        }

        return 'العميل';
    }
}
