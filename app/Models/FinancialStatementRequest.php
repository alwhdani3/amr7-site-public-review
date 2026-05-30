<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class FinancialStatementRequest extends Model
{
    protected $fillable = [
        'public_id',
        'user_id',
        'company_id',
        'status',
        'company_name',
        'cr_number',
        'fiscal_year',
        'client_notes',
        'admin_notes',
    ];

    protected $casts = [
        'fiscal_year' => 'integer',
        'created_at' => 'datetime',
    ];

    protected $appends = ['status_label', 'status_color'];

    protected static function booted(): void
    {
        static::creating(function (self $m) {
            if (!$m->public_id) {
                $m->public_id = 'REQ-' . Str::upper(Str::random(12));
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(FinancialStatementFile::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(FinancialStatementMessage::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(FinancialStatementStatusLog::class);
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    /**
     * Phase 5 audit fix: قيمة MOC في DB قد تُكتب moc_approval (Enum)
     * أو moci_approval (legacy)؛ نُغطّي الاثنتين كي لا يظهر raw key.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'new'                              => 'جديد',
            'waiting_docs'                     => 'بانتظار مستندات',
            'in_review'                        => 'قيد المراجعة',
            'client_approval'                  => 'بانتظار اعتماد العميل',
            'moc_approval', 'moci_approval'    => 'بانتظار اعتماد وزارة التجارة',
            'completed'                        => 'مكتمل',
            'closed'                           => 'مغلق',
            'cancelled'                        => 'ملغي',
            default                            => (string) $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'new'                              => 'zinc',
            'waiting_docs'                     => 'orange',
            'in_review'                        => 'blue',
            'client_approval'                  => 'purple',
            'moc_approval', 'moci_approval'    => 'indigo',
            'completed'                        => 'emerald',
            'closed', 'cancelled'              => 'red',
            default                            => 'zinc',
        };
    }
}