<?php

namespace App\Traits;

use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\SEOTools;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\TwitterCard;
use Illuminate\Support\Str;

trait HasSEO
{
    /**
     * إعداد بيانات الـ SEO للصفحة الحالية
     */
    public function setSeo($title, $description = null, $image = null)
    {
        // 1. ضبط العنوان بشكل نظيف
        SEOTools::setTitle($title);

        // 2. ضبط الوصف (معالجة النص ليكون متوافقاً مع جوجل - أقصى حد 160 حرف)
        if ($description) {
            // إزالة أي وسوم HTML (تجنب طباعة الأكواد في الميتا) وقص النص
            $cleanDescription = Str::limit(strip_tags($description), 160);
            SEOTools::setDescription($cleanDescription);
        } else {
            // وصف افتراضي قوي في حال عدم تمرير وصف من الكنترولر
            $defaultDesc = app()->getLocale() === 'ar' 
                ? 'اكتشف خدماتنا المهنية وحلول الأعمال المتكاملة لتأسيس وتطوير شركتك مع شركة آمر سبعة لحلول الأعمال.'
                : 'Discover our professional services and integrated business solutions with Amr 7 Business Solutions.';
            SEOTools::setDescription($defaultDesc);
        }

        // 3. ضبط الصورة (OG & Twitter)
        if ($image) {
            OpenGraph::addImage($image);
            TwitterCard::setImage($image);
        } else {
            // صورة افتراضية للموقع لضمان عدم ظهور الرابط بشكل مشوه عند المشاركة
            OpenGraph::addImage(asset('brand/amr7/amr7-og-image-1200x630.png'));
            TwitterCard::setImage(asset('brand/amr7/amr7-og-image-1200x630.png'));
        }
        
        // 4. ضبط الرابط الحالي والـ Canonical (🔥 الأهم للـ SEO)
        // دالة current() تجلب الرابط النظيف بدون فلاتر (بدون ?page=2 وغيرها)
        $cleanUrl = url()->current(); 
        
        SEOTools::opengraph()->setUrl($cleanUrl);
        SEOMeta::setCanonical($cleanUrl); // يخبر جوجل أن هذا هو الرابط الأصلي ولا تلتفت للروابط الفرعية
        
        // 5. إعدادات الشبكات الاجتماعية (OpenGraph & Twitter Cards)
        OpenGraph::addProperty('type', 'website');
        OpenGraph::addProperty('site_name', 'شركة آمر سبعة لحلول الأعمال | Amr 7 Business Solutions');
        
        // يعرض الصورة بشكل كبير وجذاب في تويتر
        TwitterCard::setType('summary_large_image'); 
    }
}
