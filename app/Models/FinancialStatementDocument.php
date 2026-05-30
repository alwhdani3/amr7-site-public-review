<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class FinancialStatementDocument extends Model
{
    protected $fillable = [
        'financial_statement_request_id',
        'category',
        'label',
        'path',
        'mime',
        'size',
        'is_required',
        'is_final_output',
        'uploaded_by',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_final_output' => 'boolean',
        'size' => 'integer',
    ];

    protected $appends = ['formatted_size', 'url'];

    public function request(): BelongsTo
    {
        return $this->belongsTo(FinancialStatementRequest::class, 'financial_statement_request_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    public function getFormattedSizeAttribute(): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($this->size, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime, 'image/');
    }

    public function isPdf(): bool
    {
        return $this->mime === 'application/pdf';
    }
}