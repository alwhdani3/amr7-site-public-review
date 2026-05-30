<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Support\Seo\OfficialServiceContent;
use Artesaos\SEOTools\Facades\SEOTools;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    public function show(Service $service)
    {
        abort_if(! $service->is_active, 404);

        $service->loadMissing('platform');

        $locale = app()->getLocale() === 'en' ? 'en' : 'ar';

        $title = $this->localizedServiceTitle($service, $locale);
        $description = $this->localizedServiceDescription($service, $title, $locale);
        $fullTitle = $this->formatServiceSeoTitle($title);

        $canonicalUrl = url()->current();

        $image = ! empty($service->icon)
            ? asset('storage/' . ltrim((string) $service->icon, '/'))
            : asset('brand/amr7/amr7-og-image-1200x630.png');

        SEOTools::setTitle($fullTitle, false);
        SEOTools::setDescription($description);
        SEOTools::setCanonical($canonicalUrl);

        SEOTools::opengraph()->setUrl($canonicalUrl);
        SEOTools::opengraph()->setTitle($fullTitle);
        SEOTools::opengraph()->setDescription($description);
        SEOTools::opengraph()->addProperty('type', 'service');
        SEOTools::opengraph()->addImage($image);

        SEOTools::twitter()->setTitle($fullTitle);
        SEOTools::twitter()->setDescription($description);
        SEOTools::twitter()->setImage($image);

        $officialContent = OfficialServiceContent::forService($service->slug, $locale);
        $officialFaqSchema = OfficialServiceContent::faqSchema($officialContent, $canonicalUrl);

        $relatedServices = Service::query()
            ->with('platform')
            ->where('service_platform_id', $service->service_platform_id)
            ->where('id', '!=', $service->id)
            ->where('is_active', true)
            ->latest()
            ->take(3)
            ->get();

        return view('services.show', compact('service', 'relatedServices', 'officialContent', 'officialFaqSchema'));
    }

    public function requestService($service_id)
    {
        $locale = app()->getLocale() === 'en' ? 'en' : 'ar';

        $service = Service::query()
            ->whereKey($service_id)
            ->where('is_active', true)
            ->firstOrFail();

        $serviceTitle = $locale === 'ar'
            ? ($service->title_ar ?? $service->title_en ?? $service->slug ?? '')
            : ($service->title_en ?? $service->title_ar ?? $service->slug ?? '');

        $title = $locale === 'ar'
            ? 'طلب خدمة: ' . trim($serviceTitle)
            : 'Request Service: ' . trim($serviceTitle);

        SEOTools::setTitle($title, false);
        SEOTools::setDescription($title);
        SEOTools::metatags()->setRobots('noindex,follow');
        SEOTools::setCanonical(request()->url());
        SEOTools::opengraph()->setUrl(request()->url());

        return view('services.request', compact('service'));
    }

    private function localizedServiceTitle(Service $service, string $locale): string
    {
        $preferred = $locale === 'en'
            ? $service->title_en
            : $service->title_ar;

        $title = trim(strip_tags((string) $preferred));

        if ($locale === 'en' && ($title === '' || $this->containsArabic($title))) {
            return __('services.detail_fallback_title');
        }

        return $title !== '' ? $title : __('services.detail_fallback_title');
    }

    private function localizedServiceDescription(Service $service, string $title, string $locale): string
    {
        $rawExcerpt = $locale === 'en'
            ? $service->excerpt_en
            : $service->excerpt_ar;

        $rawContent = $locale === 'en'
            ? $service->content_en
            : $service->content_ar;

        $description = trim(strip_tags((string) ($rawExcerpt ?: $rawContent)));

        if ($locale === 'en' && $this->containsArabic($description)) {
            $description = '';
        }

        if ($description === '' || Str::length($description) < 110) {
            $description = __('services.detail_seo_description', ['service' => $title]);
        }

        return Str::limit($description, 155);
    }

    private function formatServiceSeoTitle(string $title): string
    {
        $fullTitle = __('services.detail_seo_title', ['service' => $title]);

        return Str::limit(trim($fullTitle), 60, '');
    }

    private function containsArabic(string $value): bool
    {
        return preg_match('/\p{Arabic}/u', $value) === 1;
    }
}
