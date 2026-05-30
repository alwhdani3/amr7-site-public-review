<?php

namespace App\Http\Controllers\Public\Landing;

use App\Support\Seo\OfficialServiceContent;
use Illuminate\Routing\Controller;

class CompanyFormationRiyadhController extends Controller
{
    public function show()
    {
        $seoTitle = __('meta_title_formation');
        $seoDescription = __('meta_description_formation');
        $locale = app()->getLocale() === 'en' ? 'en' : 'ar';
        $canonicalUrl = url($locale === 'en' ? '/en/company-formation/riyadh' : '/company-formation/riyadh');
        $officialContent = OfficialServiceContent::forLanding('company-formation-riyadh', $locale);
        $officialFaqSchema = OfficialServiceContent::faqSchema($officialContent, $canonicalUrl);
        $officialPageSchema = OfficialServiceContent::webpageSchema($officialContent, $canonicalUrl, 'WebPage');

        $highlights = [
            ['icon' => 'fa-building', 'title' => 'تأسيس الشركات', 'desc' => 'نحدد الكيان المناسب ونجهز التأسيس باحتراف وفق الأنظمة.'],
            ['icon' => 'fa-scale-balanced', 'title' => 'الالتزام والحوكمة', 'desc' => 'نماذج عقود + تعديلات + حوكمة داخلية تقلل المخاطر.'],
            ['icon' => 'fa-id-card', 'title' => 'الملفات الحكومية', 'desc' => 'تجهيز ملفات المنشأة وربط المتطلبات حسب النشاط.'],
            ['icon' => 'fa-receipt', 'title' => 'زكاة/ضريبة/محاسبة', 'desc' => 'نجهزك للامتثال من البداية لتفادي الغرامات.'],
        ];

        $steps = [
            ['1', 'استشارة سريعة', 'نحدد نوع الشركة والنشاط والمتطلبات.'],
            ['2', 'تجهيز الأوراق', 'نراجع الهويات/التفويض/العنوان ونرتب الملفات.'],
            ['3', 'إنهاء التأسيس', 'إنجاز إجراءات التأسيس حسب المسار النظامي.'],
            ['4', 'إطلاق شركتك', 'تسليم “الهوية القانونية” وما يلزم للتشغيل.'],
        ];

        $docsNationalGcc = [
            'هويات الملاك/الشركاء والمدراء + بيانات التواصل',
            'تحديد النشاط ورأس المال (حسب الحالة)',
            'عنوان وطني أو بيانات مقر الشركة',
            'تفويض/وكالة شرعية عند الحاجة',
        ];

        $docsForeignMixed = [
            'بيانات المالك/الشركة الأم (إن وجدت)',
            'مستندات تأسيس الشركة الأم مصدقة (حسب الحالة)',
            'تفويض ممثل داخل المملكة',
            'متطلبات إضافية بحسب النشاط والجهة المختصة',
        ];

        $faqs = [
            ['q' => 'هل تأسيس الشركة في الرياض يختلف عن باقي المناطق؟', 'a' => 'الإجراءات الأساسية واحدة، لكن قد تختلف بعض التفاصيل حسب النشاط والجهات المرتبطة.'],
            ['q' => 'كم مدة التأسيس؟', 'a' => 'يعتمد على نوع الكيان واكتمال المستندات، وغالباً يتم خلال فترة قصيرة عند اكتمال البيانات.'],
            ['q' => 'هل تقدمون خدمة عن بُعد؟', 'a' => 'نعم، ننجز أغلب الخدمات عن بُعد، ونستقبلكم في الرياض عند الحاجة بموعد.'],
            ['q' => 'هل تشمل الخدمة عقود ونماذج تشغيل؟', 'a' => 'نعم حسب الباقة: نوفر نماذج وعقود أساسية وتعديلات عند الحاجة.'],
        ];

        // SEO Tools (Safe)
        if (class_exists('\Artesaos\SEOTools\Facades\SEOTools')) {
            try {
                \Artesaos\SEOTools\Facades\SEOTools::setTitle($seoTitle);
                \Artesaos\SEOTools\Facades\SEOTools::setDescription($seoDescription);
                \Artesaos\SEOTools\Facades\SEOTools::setCanonical($canonicalUrl);
                \Artesaos\SEOTools\Facades\SEOTools::opengraph()->setUrl($canonicalUrl);
                \Artesaos\SEOTools\Facades\SEOTools::twitter()->setTitle($seoTitle);
            } catch (\Throwable $e) {}
        }

    
return view('public.landing.company-formation-riyadh', compact(
    'seoTitle','seoDescription','highlights','steps','docsNationalGcc','docsForeignMixed','faqs',
    'officialContent','officialFaqSchema','officialPageSchema'
));
    }
}
