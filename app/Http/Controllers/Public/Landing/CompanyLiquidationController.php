<?php

namespace App\Http\Controllers\Public\Landing;

use Illuminate\Routing\Controller;

class CompanyLiquidationController extends Controller
{
    public function show()
    {
        $seoTitle = __('company_liquidation.seo_title');
        $seoDescription = __('company_liquidation.seo_description');

        $highlights = [
            ['icon' => 'fa-building-circle-xmark', 'title' => 'إلغاء السجل التجاري', 'desc' => 'نساعدك تقفل صفحة الشركة بشكل رسمي وصحيح، وإنهاء كل التزاماتك.'],
            ['icon' => 'fa-scale-balanced', 'title' => 'استشارات ووثائق قانونية', 'desc' => 'نجهز لك كل المستندات ومحاضر التصفية التي تضمن سير العملية بدون ثغرات.'],
            ['icon' => 'fa-file-invoice-dollar', 'title' => 'تسوية الزكاة والضريبة', 'desc' => 'نراجع سجلك الضريبي ونتواصل مع الهيئة لتسوية موقفك وتفادي الغرامات.'],
            ['icon' => 'fa-handshake', 'title' => 'إنهاء خلافات الشركاء', 'desc' => 'ندير التصفية بشكل حيادي يضمن توزيع الأصول وتسديد الديون برضا الجميع.'],
        ];

        $steps = [
            ['1', 'دراسة الحالة', 'نراجع وضع الشركة المالي والقانوني وحجم الالتزامات والديون.'],
            ['2', 'إصدار قرار التصفية', 'نجهز قرار الشركاء بحل الشركة وتعيين المصفي قانونياً.'],
            ['3', 'تسوية الجهات', 'ننهي الملفات في (الزكاة والدخل، الموارد البشرية، التأمينات).'],
            ['4', 'الشطب النهائي', 'نصدر شهادة الشطب النهائية للسجل التجاري لتصبح حر الالتزامات.'],
        ];

        $faqs = [
            ['q' => 'ما الفرق بين "حل الشركة" و"تصفية الشركة"؟', 'a' => 'حل الشركة هو قرار إيقاف النشاط، أما التصفية فهي الإجراءات العملية لتسديد الديون وتوزيع الأصول وشطب السجل التجاري نهائياً.'],
            ['q' => 'هل يمكنني تصفية الشركة إذا كان عليها ديون؟', 'a' => 'نعم، نساعدك في جدولة وتمرير الدفعات للدائنين أو إعلان الإفلاس (حسب الحالة) لضمان التصفية بشكل سليم.'],
            ['q' => 'كم تستغرق عملية تصفية الشركة؟', 'a' => 'تختلف حسب نوع الشركة وحجم التزاماتها الضريبية، لكننا نضمن لك أسرع مسار نظامي لإنهاء الإجراءات.'],
            ['q' => 'هل أستطيع فتح مشروع جديد بعد التصفية؟', 'a' => 'بالتأكيد! بمجرد حصولك على شهادة الشطب النهائية، تصبح ذمتك المالية خالية ويمكنك البدء من جديد.'],
        ];

        // إعدادات الـ SEO
        if (class_exists('\Artesaos\SEOTools\Facades\SEOTools')) {
            try {
                $canonicalUrl = url(app()->getLocale() === 'en' ? '/en/company-liquidation' : '/company-liquidation');

                \Artesaos\SEOTools\Facades\SEOTools::setTitle($seoTitle);
                \Artesaos\SEOTools\Facades\SEOTools::setDescription($seoDescription);
                \Artesaos\SEOTools\Facades\SEOTools::setCanonical($canonicalUrl);
                \Artesaos\SEOTools\Facades\SEOTools::opengraph()->setUrl($canonicalUrl);
                \Artesaos\SEOTools\Facades\SEOTools::twitter()->setTitle($seoTitle);
            } catch (\Throwable $e) {}
        }

        return view('public.landing.company-liquidation', compact(
            'seoTitle', 'seoDescription', 'highlights', 'steps', 'faqs'
        ));
    }
}
