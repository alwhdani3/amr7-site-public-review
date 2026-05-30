<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class FsRequestMessage extends Model
{
    protected $fillable = [
        'request_id',
        'user_id',
        'body',
        'is_internal',
        'read_at'
    ];

    protected $casts = [
        'is_internal' => 'boolean',
        'read_at' => 'datetime',
        'request_id' => 'integer',
        'user_id' => 'integer',
    ];

    protected $appends = ['is_read', 'created_at_human'];

    public function request(): BelongsTo
    {
        return $this->belongsTo(FinancialStatementRequest::class, 'request_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getIsReadAttribute(): bool
    {
        return $this->read_at !== null;
    }

    public function getCreatedAtHumanAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    public function scopeInternal(Builder $query): void
    {
        $query->where('is_internal', true);
    }

    public function scopePublic(Builder $query): void
    {
        $query->where('is_internal', false);
    }

    public function scopeUnread(Builder $query): void
    {
        $query->whereNull('read_at');
    }

    public function markAsRead(): bool
    {
        return $this->update(['read_at' => now()]);
    }
}