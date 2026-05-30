<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiDocumentExtraction extends Model
{
    use HasFactory;

    public const STATUS_PENDING          = 'pending';
    public const STATUS_PROCESSING       = 'processing';
    public const STATUS_READY_FOR_REVIEW = 'ready_for_review';
    public const STATUS_APPROVED         = 'approved';
    public const STATUS_REJECTED         = 'rejected';
    public const STATUS_FAILED           = 'failed';

    protected $fillable = [
        'company_id',
        'document_id',
        'correlation_id',
        'uploaded_by',
        'document_type',
        'status',
        'extracted_json',
        'confidence_score',
        'error_message',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'extracted_json'   => 'array',
        'confidence_score' => 'decimal:3',
        'approved_at'      => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(CompanyDocument::class, 'document_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isReadyForReview(): bool
    {
        return $this->status === self::STATUS_READY_FOR_REVIEW;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }
}
