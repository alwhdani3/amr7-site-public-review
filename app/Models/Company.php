<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\{HasMany, BelongsToMany};

/**
 * Company = the real client entity in AMR7 (see docs/data-model.md).
 *
 * A Company carries the establishment's identity (CR, tax number, unified
 * number, GOSI, medical insurance), its documents, files, subscriptions,
 * service requests, financial-statement requests, tickets, compliance
 * obligations, obligation periods, and tax-return requests.
 *
 * Membership / ownership model:
 *   - Users belong to Companies via the `company_user` pivot.
 *   - The pivot has a `role` column with two values: `admin` (the client
 *     who owns / manages this company) and `employee` (a worker inside
 *     the client's company who also uses the portal). This is a
 *     *company-internal* role and is unrelated to the system-level
 *     Spatie roles on the user.
 *
 * Customer (the separate model) is NOT the way clients are represented.
 * It exists for walk-in tickets where no login account exists.
 */
class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'commercial_name',
        'cr_number',
        'unified_number',
        'tax_number',
        'city',
        'address',
        'entity_size',
        'nitaq_color',
        'employees_count',
        'cr_issue_date',
        'cr_expiry_date',
        'status',              // active, inactive, suspended
        'logo_path',
        'consultation_balance', // ✅ هذا رصيد "يدوي" مخزن في الجدول
        // P1.5 — GOSI scaffold
        'gosi_establishment_id',
        // Phase B — حقول إتمام
        'activity',
        'entity_status',
        'internal_notes',
        'gosi_subscription_number',
        'gosi_link_status',
        'gosi_last_verified_at',
        'medical_insurance_status',
        'medical_insurance_company',
        'medical_insurance_policy_number',
        'medical_insurance_start_date',
        'medical_insurance_end_date',
        // Phase 9B — حقول الالتزام الضريبي (tax_number موجود مسبقاً، لا تكرار)
        'tax_filing_period',
    ];

    protected $casts = [
        'cr_issue_date' => 'date',
        'cr_expiry_date' => 'date',
        'employees_count' => 'integer',
        'consultation_balance' => 'integer', // ✅ اليدوي
        // Phase B — casts للحقول الجديدة
        'gosi_last_verified_at'        => 'datetime',
        'medical_insurance_start_date' => 'date',
        'medical_insurance_end_date'   => 'date',
    ];

    // ✅ لو تحتاجها في Filament / JSON
    protected $appends = [
        'logo_url',
        'consultation_balance_total',
        'compliance_score',
        // Phase B
        'profile_completion_percent',
    ];

    /* =============================
        العلاقات (Relationships)
    ============================== */

    public function documents(): HasMany
    {
        return $this->hasMany(CompanyDocument::class);
    }

    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class);
    }

    /**
     * Phase A — team invitations (additive, no schema rewrite of
     * company_user). Lifecycle and security live in CompanyInvitation;
     * this is the entry-point relation used by Dashboard + Filament.
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(CompanyInvitation::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /* Phase 9B: compliance + tax-return relations */

    public function complianceObligations(): HasMany
    {
        return $this->hasMany(ComplianceObligation::class);
    }

    public function obligationPeriods(): HasMany
    {
        return $this->hasMany(ObligationPeriod::class);
    }

    public function taxReturnRequests(): HasMany
    {
        return $this->hasMany(TaxReturnRequest::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'company_user')
            // Phase B: expose `permissions` (nullable JSON) on the
            // pivot. Existing rows read as null and fall back to the
            // role-based default matrix in App\Support\CompanyPermissions.
            ->withPivot('role', 'designation', 'is_active', 'permissions')
            ->withTimestamps();
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    /* =============================
        Accessors (المعالجات)
    ============================== */

    public function getLogoUrlAttribute(): string
    {
        return $this->logo_path
            ? asset('storage/' . $this->logo_path)
            : asset('images/default-company.png');
    }

    /**
     * ✅ رصيد الاستشارات النهائي:
     * = (رصيد الاشتراكات النشطة) + (الرصيد اليدوي consultation_balance)
     */
    public function getConsultationBalanceTotalAttribute(): int
    {
        $subscriptionBalance = $this->subscriptions()
            ->where('status', 'active')
            ->where('expires_at', '>=', now())
            ->sum('remaining_consultations');

        $manualBalance = (int) ($this->attributes['consultation_balance'] ?? 0);

        return (int) $subscriptionBalance + $manualBalance;
    }

    public function getComplianceScoreAttribute(): int
    {
        $totalDocs = $this->documents()->count();
        if ($totalDocs === 0) return 0;

        $validDocs = $this->documents()
            ->where('status', 'valid')
            ->whereDate('expiry_date', '>', now())
            ->count();

        return (int) round(($validDocs / $totalDocs) * 100);
    }

    /* =============================
        Phase B — Profile Completion
    ============================== */

    /**
     * يعيد قائمة الحقول التي تُحسب لاكتمال الملف.
     * كل عنصر [$key, $present:bool, $arabicLabel].
     */
    public function profileFieldsForCompletion(): array
    {
        $hasCommercialRegisterDocument = $this->hasCommercialRegisterUpload();

        $fields = [
            'name'                            => 'اسم المنشأة',
            'cr_number'                       => 'رقم السجل التجاري',
            'unified_number'                  => 'الرقم الموحد 700',
            'tax_number'                      => 'الرقم الضريبي',
            'city'                            => 'المدينة',
            'address'                         => 'العنوان الوطني',
            'cr_issue_date'                   => 'تاريخ إصدار السجل',
            'cr_expiry_date'                  => 'تاريخ انتهاء السجل',
            'activity'                        => 'نشاط المنشأة',
            'entity_size'                     => 'حجم المنشأة',
            'employees_count'                 => 'عدد الموظفين',
            'gosi_subscription_number'        => 'رقم اشتراك التأمينات',
            'medical_insurance_company'       => 'شركة التأمين الطبي',
            'medical_insurance_policy_number' => 'رقم وثيقة التأمين',
            'commercial_register_document'    => 'وثيقة السجل التجاري',
        ];

        $result = [];
        foreach ($fields as $col => $label) {
            $present = $col === 'commercial_register_document'
                ? $hasCommercialRegisterDocument
                : $this->completionValueIsPresent($this->getAttribute($col));

            $result[] = [
                'key'     => $col,
                'present' => $present,
                'label'   => $label,
            ];
        }

        return $result;
    }

    public function getProfileCompletionPercentAttribute(): int
    {
        $fields = $this->profileFieldsForCompletion();
        $total = count($fields);
        if ($total === 0) return 0;

        $filled = count(array_filter($fields, fn ($f) => $f['present']));
        return (int) round(($filled / $total) * 100);
    }

    public function profileMissingFields(): array
    {
        return array_values(array_filter(
            $this->profileFieldsForCompletion(),
            fn ($f) => ! $f['present']
        ));
    }

    protected function completionValueIsPresent(mixed $value): bool
    {
        return ! is_null($value) && $value !== '' && $value !== 0;
    }

    public static function commercialRegisterDocumentTypes(): array
    {
        return [
            'cr',
            'commercial_register',
            'commercial_register_certificate',
        ];
    }

    public static function commercialRegisterFileCategories(): array
    {
        return [
            'cr',
            'commercial_register',
            'commercial_register_certificate',
        ];
    }

    public function hasCommercialRegisterUpload(): bool
    {
        $documentTypes = static::commercialRegisterDocumentTypes();
        $fileCategories = static::commercialRegisterFileCategories();

        return $this->documents()
            ->whereIn('type', $documentTypes)
            ->whereNotNull('file_path')
            ->exists()
            || $this->files()
                ->whereIn('category', $fileCategories)
                ->whereNotNull('path')
                ->exists();
    }

    /* =============================
        Helpers
    ============================== */

    public function isCrExpired(): bool
    {
        return $this->cr_expiry_date && $this->cr_expiry_date->isPast();
    }

    public function daysUntilCrExpiry(): int
    {
        if (! $this->cr_expiry_date) return 0;
        return (int) now()->diffInDays($this->cr_expiry_date, false);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /* =============================
        Scopes
    ============================== */

    public function files(): HasMany
    {
        return $this->hasMany(CompanyFile::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('status', 'active');
    }

    public function scopeExpiringSoon(Builder $query, int $days = 30): void
    {
        $query->whereDate('cr_expiry_date', '>', now())
              ->whereDate('cr_expiry_date', '<=', now()->addDays($days));
    }
}
