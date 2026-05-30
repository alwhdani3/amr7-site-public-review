<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Quote extends Model
{
    public const STATUS_DRAFT     = 'draft';
    public const STATUS_SENT      = 'sent';
    public const STATUS_ACCEPTED  = 'accepted';
    public const STATUS_REJECTED  = 'rejected';
    public const STATUS_EXPIRED   = 'expired';

    protected $fillable = [
        'public_id',
        'quote_number',
        'company_id',
        'service_request_id',
        'created_by',
        'subtotal',
        'vat_amount',
        'total',
        'currency',
        'status',
        'valid_until',
        'notes',
    ];

    protected $casts = [
        'subtotal'    => 'decimal:2',
        'vat_amount'  => 'decimal:2',
        'total'       => 'decimal:2',
        'valid_until' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (Quote $quote) {
            if (! $quote->public_id) {
                $quote->public_id = (string) Str::uuid();
            }
            if (! $quote->quote_number) {
                $prefix = (string) config('billing.quote_number_prefix', 'QT');
                $quote->quote_number = $prefix . '-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));
            }
            if (! $quote->currency) {
                $quote->currency = (string) config('billing.currency', 'SAR');
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class)->orderBy('sort_order');
    }
}
