<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Payment extends Model
{
    public const STATUS_PENDING  = 'pending';
    public const STATUS_PAID     = 'paid';
    public const STATUS_FAILED   = 'failed';
    public const STATUS_REFUNDED = 'refunded';
    public const STATUS_MANUAL   = 'manual';

    protected $fillable = [
        'public_id',
        'invoice_id',
        'recorded_by',
        'provider',
        'provider_reference',
        'amount',
        'currency',
        'status',
        'paid_at',
        'raw_response',
        'notes',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'paid_at'      => 'datetime',
        'raw_response' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (Payment $payment) {
            if (! $payment->public_id) {
                $payment->public_id = (string) Str::uuid();
            }
            if (! $payment->currency) {
                $payment->currency = (string) config('billing.currency', 'SAR');
            }
            if (! $payment->provider) {
                $payment->provider = (string) config('billing.payment_provider', 'manual');
            }
        });
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
