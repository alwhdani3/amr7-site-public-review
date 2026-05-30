<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Invoice extends Model
{
    public const STATUS_DRAFT     = 'draft';
    public const STATUS_ISSUED    = 'issued';
    public const STATUS_PAID      = 'paid';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_OVERDUE   = 'overdue';

    protected $fillable = [
        'public_id',
        'invoice_number',
        'company_id',
        'service_request_id',
        'subscription_id',
        'quote_id',
        'issued_by',
        'subtotal',
        'vat_amount',
        'total',
        'currency',
        'status',
        'issue_date',
        'due_date',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'subtotal'   => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'total'      => 'decimal:2',
        'issue_date' => 'date',
        'due_date'   => 'date',
        'paid_at'    => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Invoice $invoice) {
            if (! $invoice->public_id) {
                $invoice->public_id = (string) Str::uuid();
            }
            if (! $invoice->invoice_number) {
                $prefix = (string) config('billing.invoice_number_prefix', 'INV');
                $invoice->invoice_number = $prefix . '-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));
            }
            if (! $invoice->currency) {
                $invoice->currency = (string) config('billing.currency', 'SAR');
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

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function totalPaid(): float
    {
        return (float) $this->payments()->where('status', Payment::STATUS_PAID)->sum('amount');
    }

    public function isFullyPaid(): bool
    {
        return $this->totalPaid() >= (float) $this->total;
    }
}
