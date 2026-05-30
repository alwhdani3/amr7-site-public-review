<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'consultation_limit',
        'features',
        'is_active',
        'is_featured',
        // Accounting preset extensions (nullable in DB):
        'slug',
        'kind',
        'agreement_template_id',
        'accounting_config',
    ];

    protected $casts = [
        'features'              => 'array',
        'accounting_config'     => 'array',
        'is_active'             => 'boolean',
        'is_featured'           => 'boolean',
        'price'                 => 'decimal:2',
        'consultation_limit'    => 'integer',
        'agreement_template_id' => 'integer',
    ];

    protected $appends = [
        'formatted_price',
        'is_free',
        'normalized_features',
        'grouped_features',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Phase 9B: relational features (countable + quota'd) alongside the legacy
     * `features` JSON column which stays untouched.
     */
    public function packageFeatures(): HasMany
    {
        return $this->hasMany(PackageFeature::class)->orderBy('sort_order');
    }

    /**
     * Accounting agreement template attached to this package (optional).
     * Resolves only when the new column + agreement_templates table exist.
     */
    public function agreementTemplate(): BelongsTo
    {
        return $this->belongsTo(AgreementTemplate::class, 'agreement_template_id');
    }

    public function getFormattedPriceAttribute(): string
    {
        if ((float) $this->price <= 0) {
            return __('free');
        }

        return number_format((float) $this->price, 2) . ' ' . __('currency_sar');
    }

    public function getIsFreeAttribute(): bool
    {
        return (float) $this->price <= 0;
    }

    /**
     * توحيد قراءة features سواء كانت:
     * - array بسيطة
     * - JSON string
     * - object grouped sections => items
     */
    protected function parseFeaturesValue(): array
    {
        $raw = $this->getRawOriginal('features');

        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        if (is_array($this->features)) {
            return $this->features;
        }

        return [];
    }

    /**
     * مميزات مجمعة بالأقسام
     * مثال:
     * [
     *   ['title' => 'منصة قوى', 'items' => ['...', '...']],
     *   ['title' => 'وزارة التجارة', 'items' => ['...', '...']],
     * ]
     */
    public function getGroupedFeaturesAttribute(): array
    {
        $features = $this->parseFeaturesValue();

        if (! is_array($features) || empty($features)) {
            return [];
        }

        $grouped = [];

        foreach ($features as $key => $value) {
            // لو القيمة قائمة عناصر والقسم هو المفتاح
            if (is_string($key) && is_array($value)) {
                $items = collect($value)
                    ->map(fn ($item) => is_string($item) ? trim($item) : '')
                    ->filter(fn ($item) => $item !== '')
                    ->values()
                    ->all();

                if (! empty($items)) {
                    $grouped[] = [
                        'title' => trim($key),
                        'items' => $items,
                    ];
                }

                continue;
            }

            // لو العنصر string مباشر
            if (is_string($value)) {
                $value = trim($value);

                if ($value !== '') {
                    $grouped[] = [
                        'title' => null,
                        'items' => [$value],
                    ];
                }

                continue;
            }

            // لو العنصر array داخلي بصيغة custom
            if (is_array($value)) {
                $title = trim(
                    $value['title']
                    ?? $value['name']
                    ?? $value['label']
                    ?? ''
                );

                $items = $value['items'] ?? $value['features'] ?? null;

                if (is_array($items)) {
                    $items = collect($items)
                        ->map(fn ($item) => is_string($item) ? trim($item) : '')
                        ->filter(fn ($item) => $item !== '')
                        ->values()
                        ->all();

                    if (! empty($items)) {
                        $grouped[] = [
                            'title' => $title !== '' ? $title : null,
                            'items' => $items,
                        ];
                    }
                }
            }
        }

        return $grouped;
    }

    /**
     * قائمة مسطحة من جميع المميزات
     */
    public function getNormalizedFeaturesAttribute(): array
    {
        return collect($this->grouped_features)
            ->flatMap(fn ($group) => $group['items'] ?? [])
            ->filter(fn ($item) => is_string($item) && trim($item) !== '')
            ->values()
            ->all();
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeFeatured(Builder $query): void
    {
        $query->where('is_featured', true);
    }

    public function hasFeature(string $feature): bool
    {
        return in_array(trim($feature), $this->normalized_features, true);
    }
}