<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Frozen rendering of an AgreementTemplate for a specific company / subscription.
 * Placeholder substitution is handled by App\Services\Agreements\AgreementRenderer.
 */
class GeneratedAgreement extends Model
{
    protected $fillable = [
        'company_id',
        'subscription_id',
        'agreement_template_id',
        'status',
        'rendered_body',
        'signed_at',
        'signed_by_user_id',
        'metadata',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
        'metadata'  => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(AgreementTemplate::class, 'agreement_template_id');
    }

    public function signedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signed_by_user_id');
    }

    public static function statusOptions(): array
    {
        return [
            'draft'     => 'مسودة',
            'sent'      => 'مُرسلة للعميل',
            'signed'    => 'موقَّعة',
            'active'    => 'سارية',
            'expired'   => 'منتهية',
            'cancelled' => 'ملغاة',
        ];
    }
}
