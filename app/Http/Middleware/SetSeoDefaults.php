<?php

namespace App\Http\Middleware;

use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\SEOTools;
use Artesaos\SEOTools\Facades\TwitterCard;
use Closure;
use Illuminate\Http\Request;

class SetSeoDefaults
{
    public function handle(Request $request, Closure $next)
    {
        if (class_exists(SEOTools::class)) {
            try {
                $locale = app()->getLocale() === 'en' ? 'en' : 'ar';
                $isAr = $locale === 'ar';

                $fallbackUrl = url()->current();

                $companyName = $isAr
                    ? 'شركة آمر سبعة لحلول الأعمال'
                    : 'Amr 7 Business Solutions Company';

                $defaultTitle = $isAr
                    ? 'شركة آمر سبعة لحلول الأعمال | خدمات تأسيس الشركات والامتثال والحوكمة'
                    : 'Amr 7 Business Solutions Company | Company Formation, Compliance & Governance';

                $defaultDescription = $isAr
                    ? 'شركة آمر سبعة لحلول الأعمال تقدم خدمات تأسيس الشركات، السجل التجاري، التراخيص، الامتثال والحوكمة، وخدمات الأعمال في السعودية.'
                    : 'Amr 7 Business Solutions Company provides company formation, commercial registration, licensing, compliance, governance, and business services in Saudi Arabia.';

                if (! SEOMeta::getTitle()) {
                    SEOMeta::setTitle($defaultTitle);
                }

                if (! SEOMeta::getDescription()) {
                    SEOMeta::setDescription($defaultDescription);
                }

                if (! SEOMeta::getCanonical()) {
                    SEOMeta::setCanonical($fallbackUrl);
                }

                OpenGraph::setUrl(SEOMeta::getCanonical() ?: $fallbackUrl);
                OpenGraph::setTitle(SEOMeta::getTitle() ?: $defaultTitle);
                OpenGraph::setDescription(SEOMeta::getDescription() ?: $defaultDescription);
                OpenGraph::addProperty('site_name', $companyName);
                OpenGraph::addProperty('locale', $isAr ? 'ar_AR' : 'en_US');

                TwitterCard::setType('summary_large_image');
                TwitterCard::setTitle(SEOMeta::getTitle() ?: $defaultTitle);
                TwitterCard::setDescription(SEOMeta::getDescription() ?: $defaultDescription);

                SEOTools::metatags()->setKeywords(
                    $isAr
                        ? ['شركة آمر سبعة لحلول الأعمال', 'تأسيس شركات', 'السجل التجاري', 'ترخيص استثماري', 'امتثال', 'حوكمة', 'السعودية', 'الرياض']
                        : ['Amr 7 Business Solutions Company', 'company formation', 'commercial registration', 'licenses', 'contracts', 'compliance', 'governance', 'Saudi Arabia', 'Riyadh']
                );
            } catch (\Throwable $e) {
                //
            }
        }

        return $next($request);
    }
}