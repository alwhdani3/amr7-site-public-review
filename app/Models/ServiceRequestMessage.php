<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceRequestMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_request_id',
        'sender_id',
        'sender_type',
        'body',
    ];

    protected $casts = [
        'service_request_id' => 'integer',
        'sender_id'          => 'integer',
        'created_at'         => 'datetime',
        'updated_at'         => 'datetime',
    ];

    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
