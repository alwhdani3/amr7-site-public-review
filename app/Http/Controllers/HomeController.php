<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Artesaos\SEOTools\Facades\SEOTools;
use App\Models\Setting;
use App\Models\Service;
use App\Models\Partner;
use App\Models\ServicePlatform;
use App\Models\Post;
use App\Models\Article;
use Throwable;

class HomeController extends Controller
{
    public function index()
    {
        $locale = app()->getLocale();

        $this->setupSEO($locale);

        // تم إصلاح مشكلة العشوائية مع الكاش
        $services = Cache::remember("home_services_{$locale}", 1440, function () {
            if (!class_exists(Service::class)) return collect([]);

            $allowedPlatformSlugs = ['ministry-of-commerce', 'misa'];

            return Service::where('is_active', true)
                ->select([
                    'id',
                    'service_platform_id',
                    'title_ar',
                    'title_en',
                    'slug',
                    'icon',
                ])
                ->whereHas('platform', function ($q) use ($allowedPlatformSlugs) {
                    $q->whereIn('slug', $allowedPlatformSlugs)->where('is_active', true);
                })
                ->with(['platform:id,slug,is_active'])
                ->get(); // نجلب جميع الخدمات المطابقة ليتم حفظها في الكاش
        })->shuffle()->take(6); // نقوم بخلط النتائج وأخذ 6 بعد استخراجها من الكاش ليراها كل مستخدم بشكل مختلف

        $partners = Cache::remember('home_partners', 1440, function () {
            if (! class_exists(Partner::class)) {
                return collect([]);
            }

            try {
                return Partner::query()
                    ->select(['id', 'name', 'url', 'logo', 'is_active', 'sort_order'])
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->get();
            } catch (Throwable $e) {
                Log::warning('Home Partners Fetch Fallback: ' . $e->getMessage());

                return Partner::query()
                    ->select(['id', 'name', 'url', 'logo', 'is_active'])
                    ->where('is_active', true)
                    ->latest('id')
                    ->get();
            }
        });

        $platforms = Cache::remember('home_platforms', 1440, function () {
            if (! class_exists(ServicePlatform::class)) {
                return collect([]);
            }

            try {
                return ServicePlatform::query()
                    ->select(['id', 'slug', 'name_ar', 'name_en', 'is_active', 'sort_order'])
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->get();
            } catch (Throwable $e) {
                Log::warning('Home Platforms Fetch Fallback: ' . $e->getMessage());

                return ServicePlatform::query()
                    ->select(['id', 'slug', 'name_ar', 'name_en', 'is_active'])
                    ->where('is_active', true)
                    ->latest('id')
                    ->get();
            }
        });

        $settings = Cache::remember('site_settings_first', 1440, function () {
            try {
                if (! class_exists(Setting::class) || ! Schema::hasTable('settings')) {
                    return null;
                }

                return (object) [
                    'map_embed_url' => Setting::query()
                        ->where('key', 'map_embed_url')
                        ->value('value'),
                ];
            } catch (Throwable $e) {
                Log::warning('Home Settings Fetch Fallback: ' . $e->getMessage());

                return null;
            }
        });

        $posts = Cache::remember("home_posts_{$locale}", 60, function () {
            try {
                if (class_exists(Post::class)) {
                    return Post::query()
                        ->select([
                            'id',
                            'title',
                            'slug',
                            'excerpt',
                            'image',
                            'published_at',
                            'is_published',
                        ])
                        ->where('is_published', true)
                        ->latest('published_at')
                        ->take(3)
                        ->get();
                }
                if (class_exists(Article::class)) {
                    return Article::query()
                        ->select([
                            'id',
                            'title',
                            'slug',
                            'excerpt',
                            'image',
                            'published_at',
                            'is_published',
                        ])
                        ->where('is_published', true)
                        ->latest('published_at')
                        ->take(3)
                        ->get();
                }
            } catch (Throwable $e) {
                // تسجيل الخطأ بدلاً من تجاهله بصمت
                Log::warning('Home Posts Fetch Error: ' . $e->getMessage());
            }
            return collect([]);
        });

        // يفضل مستقبلاً نقل هذه الأرقام إلى جدول SiteSettings ليتمكن الأدمن من تعديلها
        $stats = [
            'clients'      => 11000,
            'companies'    => 500,
            'transactions' => 15000,
            'satisfaction' => 99,
        ];

        return view('home', compact(
            'services', 'partners', 'platforms', 'settings', 'stats', 'posts'
        ))->with('page', 'home');
    }

    private function setupSEO(string $locale): void
    {
        if (!class_exists(\Artesaos\SEOTools\Facades\SEOTools::class)) return;

        $isEn = $locale === 'en';

        $title = $isEn
            ? 'Company Formation in Riyadh | Amr 7 Business Solutions'
            : 'تأسيس شركات في الرياض | شركة آمر سبعة لحلول الأعمال';

        $description = $isEn
            ? 'Amr 7 Business Solutions: Professional company formation in Saudi Arabia, CR issuance, government licensing, compliance, and governance services aligned with Vision 2030.'
            : 'شركة آمر سبعة لحلول الأعمال: خدمات تأسيس الشركات في السعودية، إصدار السجل التجاري، التراخيص الحكومية، الامتثال والحوكمة، وخدمات عقود التأسيس باحترافية.';

        $canonicalUrl = url($isEn ? '/en' : '/');
        $image        = asset('brand/amr7/amr7-og-image-1200x630.png');

        SEOTools::metatags()->reset();
        SEOTools::setTitle($title);
        SEOTools::setDescription($description);
        SEOTools::setCanonical($canonicalUrl);

        SEOTools::opengraph()->setTitle($title);
        SEOTools::opengraph()->setDescription($description);
        SEOTools::opengraph()->setUrl($canonicalUrl);
        SEOTools::opengraph()->addProperty('type', 'website');
        SEOTools::opengraph()->addProperty('locale', $isEn ? 'en_US' : 'ar_SA');
        SEOTools::opengraph()->addImage($image);

        SEOTools::twitter()->setTitle($title);
        SEOTools::twitter()->setDescription($description);
        SEOTools::twitter()->setImage($image);
    }
}
