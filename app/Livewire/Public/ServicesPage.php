<?php

namespace App\Livewire\Public;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServicePlatform;
use App\Support\Seo\OfficialServiceContent;
use App\Traits\HasSEO;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class ServicesPage extends Component
{
    use WithPagination;
    use HasSEO;

    private const CACHE_FIRST_ACTIVE_CATEGORY_ID = 'services_page.first_active_category_id';
    private const CACHE_ACTIVE_SERVICE_CATEGORIES = 'services_page.active_service_categories';
    private const CACHE_PLATFORMS_FOR_CATEGORY_PREFIX = 'services_page.platforms_for_category_';

    #[Url(except: null)]
    public ?int $activeCategoryId = null;

    #[Url(except: null)]
    public ?int $activePlatformId = null;

    #[Url(except: '')]
    public string $search = '';

    public ?string $platformSlug = null;

    protected $paginationTheme = 'tailwind';

    public function mount(?ServicePlatform $platform = null): void
    {
        if ($platform && $platform->is_active) {
            $this->platformSlug = $platform->slug;
            $this->activePlatformId = $platform->id;
            $this->activeCategoryId = $platform->service_category_id;
        }

        if ($this->activeCategoryId === null) {
            $this->activeCategoryId = Cache::remember(
                self::CACHE_FIRST_ACTIVE_CATEGORY_ID,
                now()->addDay(),
                fn () => ServiceCategory::query()
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->value('id')
            );
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function setCategory(int $id): void
    {
        if ($this->activeCategoryId === $id) {
            return;
        }

        $this->activeCategoryId = $id;
        $this->activePlatformId = null;
        $this->platformSlug = null;
        $this->resetPage();
    }

    public function setPlatform(int $id): void
    {
        if ($this->activePlatformId === $id) {
            $this->activePlatformId = null;
            $this->platformSlug = null;
        } else {
            $platform = ServicePlatform::query()
                ->select('id', 'slug', 'service_category_id')
                ->whereKey($id)
                ->where('is_active', true)
                ->first();

            if (! $platform) {
                return;
            }

            $this->activePlatformId = $platform->id;
            $this->activeCategoryId = $platform->service_category_id;
            $this->platformSlug = $platform->slug;
        }

        $this->resetPage();
    }

    public function clearPlatform(): void
    {
        if ($this->activePlatformId === null) {
            return;
        }

        $this->activePlatformId = null;
        $this->platformSlug = null;
        $this->resetPage();
    }

    #[Computed]
    public function categories()
    {
        return Cache::remember(
            self::CACHE_ACTIVE_SERVICE_CATEGORIES,
            now()->addDay(),
            fn () => ServiceCategory::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(['id', 'name_ar', 'name_en', 'sort_order'])
        );
    }

    #[Computed]
    public function platforms()
    {
        if (! $this->activeCategoryId) {
            return collect();
        }

        $cacheKey = self::CACHE_PLATFORMS_FOR_CATEGORY_PREFIX . $this->activeCategoryId;

        return Cache::remember(
            $cacheKey,
            now()->addDay(),
            fn () => ServicePlatform::query()
                ->where('service_category_id', $this->activeCategoryId)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(['id', 'slug', 'service_category_id', 'name_ar', 'name_en', 'sort_order'])
        );
    }

    protected function applySeo(): void
    {
        $locale = app()->getLocale();

        if ($this->activePlatformId) {
            $platform = ServicePlatform::query()
                ->select('id', 'slug', 'name_ar', 'name_en')
                ->whereKey($this->activePlatformId)
                ->first();

            if ($platform) {
                $name = $locale === 'ar'
                    ? ($platform->name_ar ?? $platform->name_en ?? '')
                    : ($platform->name_en ?? $platform->name_ar ?? '');

                $name = $this->localizedPlatformName(trim((string) $name), $locale);
                $title = Str::limit(__('services.platform_seo_title', ['platform' => $name]), 60, '');
                $description = __('services.platform_seo_description', ['platform' => $name]);

                $this->setSeo(
                    $title,
                    $description,
                    asset('brand/amr7/amr7-og-image-1200x630.png')
                );

                return;
            }
        }

        $title = __('services.seo_title');
        $description = __('services.seo_description');

        $this->setSeo($title, $description, asset('brand/amr7/amr7-og-image-1200x630.png'));
    }

    public function render()
    {
        $this->applySeo();
        $locale = app()->getLocale() === 'en' ? 'en' : 'ar';
        $isPlatformRoute = request()->routeIs('services.platform');
        $officialContent = ($isPlatformRoute && $this->platformSlug)
            ? OfficialServiceContent::forPlatform($this->platformSlug, $locale)
            : null;

        $officialPageUrl = ($isPlatformRoute && $this->platformSlug)
            ? route('services.platform', ['platform' => $this->platformSlug])
            : route('services.index');

        if (class_exists(LaravelLocalization::class)) {
            $officialPageUrl = LaravelLocalization::getLocalizedURL($locale, $officialPageUrl);
        }

        $officialFaqSchema = OfficialServiceContent::faqSchema($officialContent, $officialPageUrl);
        $officialPageSchema = OfficialServiceContent::webpageSchema($officialContent, $officialPageUrl, 'CollectionPage');

        $activePlatform = null;
        $activePlatformName = null;

        if ($isPlatformRoute && $this->activePlatformId) {
            $activePlatform = ServicePlatform::query()
                ->select('id', 'slug', 'name_ar', 'name_en')
                ->whereKey($this->activePlatformId)
                ->first();

            if ($activePlatform) {
                $rawName = $locale === 'en'
                    ? ($activePlatform->name_en ?? $activePlatform->name_ar ?? '')
                    : ($activePlatform->name_ar ?? $activePlatform->name_en ?? '');

                $activePlatformName = $this->localizedPlatformName(trim((string) $rawName), $locale);
            }
        }

        $term = trim($this->search);

        $services = Service::query()
            ->select([
                'id',
                'service_platform_id',
                'title_ar',
                'title_en',
                'excerpt_ar',
                'excerpt_en',
                'slug',
                'icon',
                'is_active',
            ])
            ->where('is_active', true)
            ->with(['platform:id,slug,service_category_id,name_ar,name_en'])
            ->when($term !== '', function ($q) use ($term) {
                $like = '%' . $term . '%';

                $q->where(function ($sub) use ($like) {
                    $sub->where('title_ar', 'like', $like)
                        ->orWhere('title_en', 'like', $like)
                        ->orWhere('excerpt_ar', 'like', $like)
                        ->orWhere('excerpt_en', 'like', $like);
                });
            })
            ->when($this->activePlatformId, function ($q) {
                $q->where('service_platform_id', $this->activePlatformId);
            })
            ->when(! $this->activePlatformId && $this->activeCategoryId, function ($q) {
                $q->whereHas('platform', function ($p) {
                    $p->where('service_category_id', $this->activeCategoryId);
                });
            })
            ->latest('id')
            ->paginate(12);

        if ($services->lastPage() > 0 && $services->currentPage() > $services->lastPage()) {
            abort(404);
        }

        return view('livewire.public.services-page', [
            'services' => $services,
            'officialContent' => $officialContent,
            'officialFaqSchema' => $officialFaqSchema,
            'officialPageSchema' => $officialPageSchema,
            'isPlatformRoute' => $isPlatformRoute,
            'activePlatformName' => $activePlatformName,
        ]);
    }

    private function localizedPlatformName(string $name, string $locale): string
    {
        if ($locale === 'en' && ($name === '' || $this->containsArabic($name))) {
            return __('services.platform_fallback_name');
        }

        return $name !== '' ? $name : __('services.platform_fallback_name');
    }

    private function containsArabic(string $value): bool
    {
        return preg_match('/\p{Arabic}/u', $value) === 1;
    }
}
