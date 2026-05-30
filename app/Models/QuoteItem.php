<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteItem extends Model
{
    protected $fillable = [
        'quote_id',
        'description',
        'qty',
        'unit_price',
        'total',
        'sort_order',
    ];

    protected $casts = [
        'qty'        => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total'      => 'decimal:2',
        'sort_order' => 'integer',
    ];

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }
}
