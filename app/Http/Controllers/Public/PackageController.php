<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Support\Str;
use Artesaos\SEOTools\Facades\SEOTools;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\TwitterCard;
use Artesaos\SEOTools\Facades\JsonLd;

class PackageController extends Controller
{
    public function index()
    {
        $isAr = app()->getLocale() === 'ar';

        $title = __('packages.seo_title');
        $description = __('packages.seo_description');

        $url = url()->current();

        SEOTools::setTitle($title);
        SEOTools::setDescription($description);
        SEOTools::setCanonical($url);

        SEOTools::metatags()->setRobots('index,follow');

        OpenGraph::setTitle($title);
        OpenGraph::setDescription($description);
        OpenGraph::setUrl($url);
        OpenGraph::addProperty('type', 'website');
        OpenGraph::addProperty('locale', $isAr ? 'ar_SA' : 'en_US');

        TwitterCard::setTitle($title);
        TwitterCard::setDescription($description);
        TwitterCard::setUrl($url);
        TwitterCard::setType('summary_large_image');

        JsonLd::setTitle($title);
        JsonLd::setDescription($description);
        JsonLd::setType('CollectionPage');
        JsonLd::addValue('@url', $url);

        $packages = Package::query()
            ->active()
            ->latest('id')
            ->get();

        return view('public.packages.index', compact('packages'));
    }

    public function show(Package $package)
    {
        abort_unless($package->is_active, 404);

        $isAr = app()->getLocale() === 'ar';

        $packageName = $this->localizedPackageName($package, $isAr);
        $title = $this->formatSeoTitle(__('packages.detail_seo_title', ['name' => $packageName]));
        $description = $this->localizedPackageDescription($package, $packageName, $isAr);

        $url = url()->current();

        SEOTools::setTitle($title);
        SEOTools::setDescription($description);
        SEOTools::setCanonical($url);
        SEOTools::metatags()->setRobots('index,follow');

        OpenGraph::setTitle($title);
        OpenGraph::setDescription($description);
        OpenGraph::setUrl($url);
        OpenGraph::addProperty('type', 'product');
        OpenGraph::addProperty('locale', $isAr ? 'ar_SA' : 'en_US');

        if (!is_null($package->price)) {
            OpenGraph::addProperty('product:price:amount', number_format((float) $package->price, 2, '.', ''));
            OpenGraph::addProperty('product:price:currency', 'SAR');
        }

        TwitterCard::setTitle($title);
        TwitterCard::setDescription($description);
        TwitterCard::setUrl($url);
        TwitterCard::setType('summary_large_image');

        JsonLd::setTitle($title);
        JsonLd::setDescription($description);
        JsonLd::setType('Product');
        JsonLd::addValue('@url', $url);
        JsonLd::addValue('name', $packageName);
        JsonLd::addValue('description', $description);

        if (!is_null($package->price)) {
            JsonLd::addValue('offers', [
                '@type' => 'Offer',
                'priceCurrency' => 'SAR',
                'price' => number_format((float) $package->price, 2, '.', ''),
                'availability' => 'https://schema.org/InStock',
                'url' => $url,
            ]);
        }

        return view('public.packages.show', compact('package'));
    }

    private function localizedPackageName(Package $package, bool $isAr): string
    {
        $preferred = $isAr
            ? ($package->getAttribute('name_ar') ?: $package->name)
            : ($package->getAttribute('name_en') ?: $package->name);

        $name = trim(strip_tags((string) $preferred));

        if (! $isAr && ($name === '' || $this->containsArabic($name))) {
            return __('packages.detail_fallback_name');
        }

        return $name !== '' ? $name : __('packages.detail_fallback_name');
    }

    private function localizedPackageDescription(Package $package, string $packageName, bool $isAr): string
    {
        $preferred = $isAr
            ? ($package->getAttribute('description_ar') ?: $package->description)
            : ($package->getAttribute('description_en') ?: null);

        $description = trim(strip_tags((string) $preferred));

        if (! $isAr && $this->containsArabic($description)) {
            $description = '';
        }

        if ($description === '' || Str::length($description) < 110) {
            return __('packages.detail_seo_description', ['name' => $packageName]);
        }

        return Str::limit($description, 155);
    }

    private function formatSeoTitle(string $title): string
    {
        return Str::limit(trim($title), 60, '');
    }

    private function containsArabic(string $value): bool
    {
        return preg_match('/\p{Arabic}/u', $value) === 1;
    }
}
