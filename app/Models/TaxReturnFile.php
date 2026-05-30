<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Phase 9B — File attached to a tax return request.
 * file_key: purchases | sales | bank | other
 */
class TaxReturnFile extends Model
{
    protected $fillable = [
        'tax_return_request_id',
        'company_id',
        'uploaded_by_user_id',
        'file_key',
        'disk',
        'path',
        'original_name',
        'mime',
        'size',
        'visibility',
        'metadata',
    ];

    protected $casts = [
        'size'     => 'integer',
        'metadata' => 'array',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(TaxReturnRequest::class, 'tax_return_request_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
