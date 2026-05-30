<?php

namespace App\Console\Commands;

use App\Models\Package;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

/**
 * AMR7 — accounting package installer.
 *
 *  - Idempotent via Package.slug + updateOrCreate.
 *  - `--dry-run` writes nothing; prints a diff-style preview.
 *  - `--apply`   actually persists.
 *  - One of the two flags must be supplied (no silent default).
 *  - Refuses to run if the accounting columns are not yet migrated.
 */
class InstallAccountingPackages extends Command
{
    protected $signature = 'amr7:install-accounting-packages
                            {--dry-run : Show what would change without writing.}
                            {--apply : Persist the presets (idempotent updateOrCreate).}';

    protected $description = 'Install the 6 AMR7 accounting package presets (idempotent).';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $apply  = (bool) $this->option('apply');

        if ($dryRun === $apply) {
            $this->error('اختر --dry-run أو --apply (وليس الاثنين).');
            return self::INVALID;
        }

        // Hard-stop only on missing table or the lookup key.
        if (! Schema::hasTable('packages')) {
            $this->error('❌ جدول packages غير موجود.');
            return self::FAILURE;
        }
        if (! Schema::hasColumn('packages', 'slug')) {
            $this->error('❌ عمود packages.slug غير موجود. شغّل migration 2026_05_24_120000 أولاً.');
            return self::FAILURE;
        }

        // Cache column existence once — used to build a tolerant payload that
        // never references a column the production schema does not have.
        // Required for full functionality but not blocking: a missing column
        // just means that piece of data won't be saved on this environment.
        $columns = [
            'name'               => Schema::hasColumn('packages', 'name'),
            'description'        => Schema::hasColumn('packages', 'description'),
            'price'              => Schema::hasColumn('packages', 'price'),
            'consultation_limit' => Schema::hasColumn('packages', 'consultation_limit'),
            'kind'               => Schema::hasColumn('packages', 'kind'),
            'accounting_config'  => Schema::hasColumn('packages', 'accounting_config'),
            'is_active'          => Schema::hasColumn('packages', 'is_active'),
            'is_featured'        => Schema::hasColumn('packages', 'is_featured'),
        ];

        // Warn (but do not fail) on missing-but-useful columns. The actual save
        // logic below silently skips any column not present in the table.
        foreach ([
            'kind'              => 'النوع (monthly/quarterly/...) لن يُحفظ',
            'accounting_config' => 'إعدادات الباقة المحاسبية لن تُحفظ',
            'is_featured'       => 'علامة "مميزة" لن تُحفظ',
            'is_active'         => 'حالة التفعيل لن تُحفظ',
        ] as $col => $impact) {
            if (! $columns[$col]) {
                $this->warn("⚠️  عمود packages.{$col} غير موجود — {$impact}.");
            }
        }
        $this->newLine();

        $presets = static::presets();

        $this->line('عدد الباقات المُجهَّزة: ' . count($presets));
        $this->newLine();

        foreach ($presets as $slug => $preset) {
            $existing = Package::where('slug', $slug)->first();
            $action   = $existing ? 'سيُحدَّث' : 'سيُنشأ';

            $this->line("─── {$preset['name']}  ({$slug})  →  {$action}");
            $this->line('   kind: ' . ($preset['kind'] ?? '—'));
            $this->line('   price: ' . number_format((float) $preset['price'], 2) . ' SAR');

            if (! $dryRun) {
                // Build payload from the candidate map, dropping any key whose
                // underlying column is not present in this environment.
                $candidates = [
                    'name'              => $preset['name'],
                    'description'       => $preset['description'] ?? null,
                    'price'             => $preset['price'],
                    'consultation_limit'=> $preset['consultation_limit'] ?? 0,
                    'kind'              => $preset['kind'] ?? null,
                    'accounting_config' => $preset['accounting_config'] ?? null,
                    'is_active'         => $preset['is_active'] ?? true,
                    'is_featured'       => $preset['is_featured'] ?? false,
                ];

                $payload = [];
                foreach ($candidates as $col => $val) {
                    if ($columns[$col] ?? false) {
                        $payload[$col] = $val;
                    }
                }

                Package::updateOrCreate(['slug' => $slug], $payload);
            }
        }

        $this->newLine();

        if ($dryRun) {
            $this->info('وضع --dry-run: لم يُكتب شيء على قاعدة البيانات. لاعتماد التغييرات، أعد التشغيل بـ--apply.');
        } else {
            $this->info('✅ تم تطبيق الباقات (' . count($presets) . ' باقة) عبر updateOrCreate.');
        }

        return self::SUCCESS;
    }

    /**
     * The 6 canonical accounting package presets.
     * Pricing is base-price (before VAT). VAT/tax applied at presentation time.
     */
    protected static function presets(): array
    {
        return [
            'accounting-basic' => [
                'name'              => 'باقة المحاسبة الأساسية',
                'description'       => 'محاسبة شهرية أساسية للمنشآت الصغيرة.',
                'kind'              => 'monthly',
                'price'             => 500,
                'consultation_limit'=> 2,
                'is_active'         => true,
                'is_featured'       => false,
                'accounting_config' => [
                    'base_price'                     => 500,
                    'vat_rate'                       => 15,
                    'invoice_sales_limit'            => 50,
                    'invoice_purchase_limit'         => 50,
                    'includes_vat'                   => false,
                    'includes_zakat'                 => false,
                    'includes_financial_statements'  => false,
                    'includes_monthly_reports'       => true,
                    'includes_quarterly_reports'     => false,
                    'included_services'              => "تسجيل القيود اليومية\nمطابقة الحسابات البنكية\nتقرير شهري مبسّط",
                    'excluded_services'              => "إعداد الإقرارات الضريبية\nالقوائم المالية السنوية\nالزكاة",
                    'client_requirements'            => "توفير الفواتير والمستندات شهرياً\nتزويدنا بكشوفات الحسابات البنكية",
                    'agreement_duration_months'      => 12,
                    'renewal_notice_days'            => 30,
                    'payment_terms'                  => 'شهرياً مقدّماً.',
                ],
            ],
            'accounting-quarterly' => [
                'name'              => 'باقة المحاسبة الربع سنوية',
                'description'       => 'محاسبة ربع سنوية تشمل إعداد إقرار VAT الربعي.',
                'kind'              => 'quarterly',
                'price'             => 1800,
                'consultation_limit'=> 6,
                'is_active'         => true,
                'is_featured'       => true,
                'accounting_config' => [
                    'base_price'                     => 1800,
                    'vat_rate'                       => 15,
                    'invoice_sales_limit'            => 200,
                    'invoice_purchase_limit'         => 200,
                    'includes_vat'                   => true,
                    'includes_zakat'                 => false,
                    'includes_financial_statements'  => false,
                    'includes_monthly_reports'       => true,
                    'includes_quarterly_reports'     => true,
                    'included_services'              => "تسجيل القيود اليومية\nإعداد وتقديم إقرار ضريبة القيمة المضافة الربعي\nتقرير ربع سنوي مفصّل",
                    'excluded_services'              => "القوائم المالية السنوية المعتمدة\nالزكاة السنوية",
                    'client_requirements'            => "توفير الفواتير والمستندات\nتزويدنا ببيانات المخزون",
                    'agreement_duration_months'      => 12,
                    'renewal_notice_days'            => 30,
                    'payment_terms'                  => 'كل 3 أشهر مقدّماً.',
                ],
            ],
            'financial-statements' => [
                'name'              => 'باقة القوائم المالية السنوية',
                'description'       => 'إعداد القوائم المالية السنوية المعتمدة.',
                'kind'              => 'yearly',
                'price'             => 3500,
                'consultation_limit'=> 4,
                'is_active'         => true,
                'is_featured'       => false,
                'accounting_config' => [
                    'base_price'                     => 3500,
                    'vat_rate'                       => 15,
                    'invoice_sales_limit'            => 0,
                    'invoice_purchase_limit'         => 0,
                    'includes_vat'                   => false,
                    'includes_zakat'                 => false,
                    'includes_financial_statements'  => true,
                    'includes_monthly_reports'       => false,
                    'includes_quarterly_reports'     => false,
                    'included_services'              => "إعداد القوائم المالية السنوية\nمراجعة القيود السنوية",
                    'excluded_services'              => "المحاسبة اليومية\nإعداد إقرار الضريبة\nالزكاة",
                    'client_requirements'            => "توفير دفتر اليومية والميزان للسنة المالية كاملة",
                    'agreement_duration_months'      => 12,
                    'renewal_notice_days'            => 45,
                    'payment_terms'                  => '50٪ مقدّماً والباقي عند التسليم.',
                ],
            ],
            'zakat-tax-annual' => [
                'name'              => 'باقة الزكاة والضريبة السنوية',
                'description'       => 'إعداد وتقديم إقرار الزكاة والضريبة السنوي.',
                'kind'              => 'yearly',
                'price'             => 2500,
                'consultation_limit'=> 3,
                'is_active'         => true,
                'is_featured'       => false,
                'accounting_config' => [
                    'base_price'                     => 2500,
                    'vat_rate'                       => 15,
                    'invoice_sales_limit'            => 0,
                    'invoice_purchase_limit'         => 0,
                    'includes_vat'                   => false,
                    'includes_zakat'                 => true,
                    'includes_financial_statements'  => false,
                    'includes_monthly_reports'       => false,
                    'includes_quarterly_reports'     => false,
                    'included_services'              => "إعداد إقرار الزكاة\nإعداد الإقرار الضريبي السنوي\nالتواصل مع زاتكا",
                    'excluded_services'              => "المحاسبة الشهرية\nالقوائم المالية",
                    'client_requirements'            => "تزويدنا بالقوائم المالية المعتمدة للسنة المالية",
                    'agreement_duration_months'      => 12,
                    'renewal_notice_days'            => 30,
                    'payment_terms'                  => 'كاملاً عند التوقيع.',
                ],
            ],
            'accounting-compliance-full' => [
                'name'              => 'باقة الامتثال المحاسبي الشامل',
                'description'       => 'باقة شاملة: محاسبة شهرية + VAT ربعي + قوائم مالية + زكاة سنوية.',
                'kind'              => 'yearly',
                'price'             => 9500,
                'consultation_limit'=> 12,
                'is_active'         => true,
                'is_featured'       => true,
                'accounting_config' => [
                    'base_price'                     => 9500,
                    'vat_rate'                       => 15,
                    'invoice_sales_limit'            => 500,
                    'invoice_purchase_limit'         => 500,
                    'includes_vat'                   => true,
                    'includes_zakat'                 => true,
                    'includes_financial_statements'  => true,
                    'includes_monthly_reports'       => true,
                    'includes_quarterly_reports'     => true,
                    'included_services'              => "محاسبة شهرية كاملة\nإعداد إقرار VAT الربعي\nالقوائم المالية السنوية\nإعداد وتقديم إقرار الزكاة\nتقارير دورية مفصّلة\nاستشارات محاسبية شهرية",
                    'excluded_services'              => "تدقيق خارجي\nخدمات قانونية",
                    'client_requirements'            => "تزويدنا بكل الفواتير والمستندات\nالوصول للأنظمة المحاسبية إن وُجدت",
                    'agreement_duration_months'      => 12,
                    'renewal_notice_days'            => 45,
                    'payment_terms'                  => 'أقساط ربع سنوية مقدّماً.',
                ],
            ],
            'custom-accounting' => [
                'name'              => 'باقة مخصصة',
                'description'       => 'تُفصَّل بنوداً وأسعاراً حسب احتياجات العميل.',
                'kind'              => 'custom',
                'price'             => 0,
                'consultation_limit'=> 0,
                'is_active'         => true,
                'is_featured'       => false,
                'accounting_config' => [
                    'base_price'                     => 0,
                    'vat_rate'                       => 15,
                    'invoice_sales_limit'            => null,
                    'invoice_purchase_limit'         => null,
                    'includes_vat'                   => false,
                    'includes_zakat'                 => false,
                    'includes_financial_statements'  => false,
                    'includes_monthly_reports'       => false,
                    'includes_quarterly_reports'     => false,
                    'included_services'              => 'يُتفق عليه مع العميل.',
                    'excluded_services'              => 'حسب الاتفاقية.',
                    'client_requirements'            => 'يُحدَّد لكل عميل.',
                    'agreement_duration_months'      => null,
                    'renewal_notice_days'            => 30,
                    'payment_terms'                  => 'حسب الاتفاقية.',
                ],
            ],
        ];
    }
}
