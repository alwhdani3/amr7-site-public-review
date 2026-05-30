<?php

namespace App\Http\Controllers\Public\Landing;

use App\Support\Seo\OfficialServiceContent;
use Illuminate\Routing\Controller;

class ForeignInvestmentController extends Controller
{
    public function show()
    {
        $seoTitle = __('seo_misa_title');
        $seoDescription = __('seo_misa_desc');
        $locale = app()->getLocale() === 'en' ? 'en' : 'ar';
        $canonicalUrl = url($locale === 'en' ? '/en/foreign-investment' : '/foreign-investment');
        $officialContent = OfficialServiceContent::forLanding('foreign-investment', $locale);
        $officialFaqSchema = OfficialServiceContent::faqSchema($officialContent, $canonicalUrl);
        $officialPageSchema = OfficialServiceContent::webpageSchema($officialContent, $canonicalUrl, 'WebPage');

        $painPoints = [
            ['icon' => 'fa-lock', 'title' => __('pain_misa_1_title'), 'desc' => __('pain_misa_1_desc')],
            ['icon' => 'fa-map-location-dot', 'title' => __('pain_misa_2_title'), 'desc' => __('pain_misa_2_desc')],
            ['icon' => 'fa-file-circle-xmark', 'title' => __('pain_misa_3_title'), 'desc' => __('pain_misa_3_desc')],
            ['icon' => 'fa-circle-question', 'title' => __('pain_misa_4_title'), 'desc' => __('pain_misa_4_desc')],
        ];

        $services = [
            ['icon' => 'fa-passport', 'title' => __('srv_misa_1_title'), 'desc' => __('srv_misa_1_desc')],
            ['icon' => 'fa-file-signature', 'title' => __('srv_misa_2_title'), 'desc' => __('srv_misa_2_desc')],
            ['icon' => 'fa-building-columns', 'title' => __('srv_misa_3_title'), 'desc' => __('srv_misa_3_desc')],
            ['icon' => 'fa-sitemap', 'title' => __('srv_misa_4_title'), 'desc' => __('srv_misa_4_desc')],
            ['icon' => 'fa-id-card-clip', 'title' => __('srv_misa_5_title'), 'desc' => __('srv_misa_5_desc')],
            ['icon' => 'fa-folder-tree', 'title' => __('srv_misa_6_title'), 'desc' => __('srv_misa_6_desc')],
        ];

        $requirements = [
            __('req_misa_1'),
            __('req_misa_2'),
            __('req_misa_3'),
        ];

        $faqs = [
            ['q' => __('faq_misa_1_q'), 'a' => __('faq_misa_1_a')],
            ['q' => __('faq_misa_2_q'), 'a' => __('faq_misa_2_a')],
            ['q' => __('faq_misa_3_q'), 'a' => __('faq_misa_3_a')],
            ['q' => __('faq_misa_4_q'), 'a' => __('faq_misa_4_a')],
            ['q' => __('faq_misa_5_q'), 'a' => __('faq_misa_5_a')],
        ];

        if (class_exists('\Artesaos\SEOTools\Facades\SEOTools')) {
            try {
                \Artesaos\SEOTools\Facades\SEOTools::setTitle($seoTitle);
                \Artesaos\SEOTools\Facades\SEOTools::setDescription($seoDescription);
                \Artesaos\SEOTools\Facades\SEOTools::setCanonical($canonicalUrl);
                \Artesaos\SEOTools\Facades\SEOTools::opengraph()->setUrl($canonicalUrl);
                \Artesaos\SEOTools\Facades\SEOTools::twitter()->setTitle($seoTitle);
            } catch (\Throwable $e) {}
        }

        return view('public.landing.foreign-investment', compact(
            'seoTitle', 'seoDescription', 'painPoints', 'services', 'requirements', 'faqs',
            'officialContent', 'officialFaqSchema', 'officialPageSchema'
        ));
    }
}
