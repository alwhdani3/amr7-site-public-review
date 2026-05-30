<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class N8nWorkflowRun extends Model
{
    protected $fillable = [
        'workflow_key',
        'workflow_name',
        'category',
        'status',
        'correlation_id',
        'request_payload',
        'response_payload',
        'error_message',
        'triggered_by',
        'duration_ms',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
        'duration_ms' => 'integer',
        'triggered_by' => 'integer',
    ];

    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }
}
