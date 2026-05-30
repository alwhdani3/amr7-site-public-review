<?php

namespace App\Models;

use App\Services\Agreements\AgreementRenderer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Master accounting agreement template with mustache-style placeholders.
 * Per-client agreements are stored in `generated_agreements` after rendering.
 */
class AgreementTemplate extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'kind',
        'body',
        'placeholders',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'placeholders' => 'array',
        'is_active'    => 'boolean',
        'metadata'     => 'array',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function packages(): HasMany
    {
        return $this->hasMany(Package::class, 'agreement_template_id');
    }

    public function generatedAgreements(): HasMany
    {
        return $this->hasMany(GeneratedAgreement::class, 'agreement_template_id');
    }

    /**
     * The complete list of placeholders the templating engine recognises.
     * Editors see this in the admin UI so they can copy/paste them into body.
     */
    public static function supportedPlaceholders(): array
    {
        return [
            'client_company_name',
            'client_cr_number',
            'client_representative_name',
            'client_representative_title',
            'client_email',
            'client_phone',
            'package_name',
            'start_date',
            'end_date',
            'included_services',
            'excluded_services',
            'invoice_sales_limit',
            'invoice_purchase_limit',
            'base_price',
            'vat_amount',
            'total_price',
            'payment_terms',
            'renewal_notice_days',
            'client_requirements',
            'agreement_duration_months',
        ];
    }

    /**
     * Canonical Arabic body editors can use as a safe starting point.
     * It intentionally contains placeholders only for client-specific data.
     */
    public static function defaultBodyTemplate(): string
    {
        return <<<'MARKDOWN'
# اتفاقية تقديم خدمات محاسبية

تم إبرام هذه الاتفاقية بين:

**الطرف الأول:** شركة آمر سبعة لحلول الأعمال، ويشار إليها لاحقاً بـ"آمر سبعة".

**الطرف الثاني:** {{client_company_name}}، سجل تجاري رقم {{client_cr_number}}، ويمثلها {{client_representative_name}} بصفته {{client_representative_title}}، ويشار إليه لاحقاً بـ"العميل".

## 1. بيانات التواصل

يكون التواصل الرسمي مع العميل عبر البريد {{client_email}} أو الهاتف {{client_phone}}، ما لم يتم الاتفاق كتابياً على وسيلة أخرى.

## 2. الباقة ونطاق الخدمات

اتفق الطرفان على باقة {{package_name}}، وتشمل الخدمات التالية:

{{included_services}}

ولا تشمل هذه الاتفاقية الخدمات التالية إلا بموجب عرض مستقل:

{{excluded_services}}

## 3. حدود التشغيل

يشمل نطاق العمل حد فواتير مبيعات قدره {{invoice_sales_limit}} وحد فواتير مشتريات قدره {{invoice_purchase_limit}} خلال مدة الاتفاقية، وما يزيد عن ذلك يخضع لتقدير إضافي أو عرض منفصل.

## 4. مدة الاتفاقية

تبدأ الاتفاقية بتاريخ {{start_date}} وتنتهي بتاريخ {{end_date}}، ولمدة تشغيل تقديرية قدرها {{agreement_duration_months}} شهر.

## 5. قيمة العرض والدفعات

قيمة الخدمات الأساسية {{base_price}} ريال، وضريبة القيمة المضافة {{vat_amount}} ريال، ويكون الإجمالي {{total_price}} ريال.

تكون شروط الدفع كالتالي:

{{payment_terms}}

## 6. التزامات العميل

يلتزم العميل بتوفير البيانات والمستندات المطلوبة في الوقت المناسب، وتشمل:

{{client_requirements}}

## 7. السرية

يلتزم الطرفان بالمحافظة على سرية المعلومات والوثائق والبيانات المتبادلة وعدم استخدامها خارج نطاق تنفيذ هذه الاتفاقية.

## 8. الإنهاء والتجديد

يجوز لأي طرف طلب عدم التجديد بإشعار كتابي قبل {{renewal_notice_days}} يوماً من تاريخ انتهاء الاتفاقية. ولا يخل الإنهاء بأي مبالغ مستحقة أو التزامات نشأت قبل تاريخ الإنهاء.

## 9. المراسلات

تكون الإشعارات والمراسلات الرسمية عبر البريد أو وسائل التواصل المعتمدة بين الطرفين، وتعد الرسائل المرسلة إلى بيانات التواصل أعلاه منتجة لآثارها ما لم يثبت خلاف ذلك.

## 10. الشرط الجزائي

في حال الإخلال الجوهري بالالتزامات أو التأخر غير المبرر في توفير المستندات أو السداد، يحق للطرف المتضرر المطالبة بالتعويض عن الضرر المباشر وفق الأنظمة والاتفاقات النافذة.

## 11. التوقيع

الطرف الأول: شركة آمر سبعة لحلول الأعمال

الطرف الثاني: {{client_company_name}}

اسم المفوض: {{client_representative_name}}

الصفة: {{client_representative_title}}
MARKDOWN;
    }

    public function renderWith(array $data): string
    {
        return app(AgreementRenderer::class)->render($this, $data);
    }

    /**
     * @return array<int, string>
     */
    public function unsupportedPlaceholders(): array
    {
        return app(AgreementRenderer::class)->missingPlaceholders($this->body ?? '');
    }
}
