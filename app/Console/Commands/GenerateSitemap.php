<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate {--clean-old : Delete legacy sitemap files before generating}';

    protected $description = 'Generate clean sitemap.xml for Amr 7 website';

    protected array $seen = [];

    protected array $staticPages = [
        ['/', '/en', 1.00, Url::CHANGE_FREQUENCY_DAILY],
        ['/about', '/en/about', 0.80, Url::CHANGE_FREQUENCY_MONTHLY],
        ['/vision', '/en/vision', 0.60, Url::CHANGE_FREQUENCY_YEARLY],
        ['/contact-us', '/en/contact-us', 0.80, Url::CHANGE_FREQUENCY_MONTHLY],
        ['/privacy-policy', '/en/privacy-policy', 0.40, Url::CHANGE_FREQUENCY_YEARLY],
        ['/using-policy', '/en/using-policy', 0.40, Url::CHANGE_FREQUENCY_YEARLY],
        ['/faq', '/en/faq', 0.70, Url::CHANGE_FREQUENCY_MONTHLY],
        ['/banks', '/en/banks', 0.60, Url::CHANGE_FREQUENCY_MONTHLY],
        ['/services', '/en/services', 0.90, Url::CHANGE_FREQUENCY_WEEKLY],
        ['/packages', '/en/packages', 0.80, Url::CHANGE_FREQUENCY_WEEKLY],
        ['/blog', '/en/blog', 0.80, Url::CHANGE_FREQUENCY_WEEKLY],
        ['/company-formation/riyadh', '/en/company-formation/riyadh', 0.90, Url::CHANGE_FREQUENCY_WEEKLY],
        ['/foreign-investment', '/en/foreign-investment', 0.90, Url::CHANGE_FREQUENCY_WEEKLY],
        ['/company-liquidation', '/en/company-liquidation', 0.90, Url::CHANGE_FREQUENCY_WEEKLY],
    ];

    protected array $excludedExactPaths = [
        '/login',
        '/register',
        '/forgot-password',
        '/reset-password',
        '/email/verify',
        '/dashboard',
        '/company/select',
        '/financial-statements/portal',
        '/en/financial-statements/portal',
        '/search',
        '/en/search',
        '/services/request',
        '/en/services/request',
        '/lang/ar',
        '/lang/en',
        '/wp-login.php',
        '/xmlrpc.php',
    ];

    protected array $excludedPrefixes = [
        '/services/request/',
        '/en/services/request/',
        '/dashboard/',
        '/company/',
        '/files/',
        '/lang/',
        '/wp-admin',
        '/wp-content',
        '/wp-includes',
        '/product-category',
        '/portfolio',
        '/project-cat',
        '/feed',
    ];

    public function handle(): int
    {
        $publicPath = public_path();

        if (! File::isDirectory($publicPath)) {
            File::makeDirectory($publicPath, 0755, true);
        }

        $this->cleanupLegacySitemaps();

        $sitemapPath = $publicPath . '/sitemap.xml';
        $sitemap = Sitemap::create();

        $this->addStaticPages($sitemap);
        $this->addServices($sitemap);
        $this->addPackages($sitemap);
        $this->addPosts($sitemap);

        $sitemap->writeToFile($sitemapPath);

        $this->info('Sitemap generated successfully:');
        $this->line($sitemapPath);
        $this->line('URLs count: ' . count($this->seen));

        return self::SUCCESS;
    }

    protected function cleanupLegacySitemaps(): void
    {
        $legacyFiles = [
            base_path('sitemap.xml'),
            public_path('sitemap-index.xml'),
            public_path('sitemap-0.xml'),
            public_path('sitemap.xml.gz'),
        ];

        foreach ($legacyFiles as $file) {
            if (File::exists($file)) {
                File::delete($file);
                $this->line('Deleted legacy sitemap: ' . $file);
            }
        }
    }

    protected function addStaticPages(Sitemap $sitemap): void
    {
        foreach ($this->staticPages as [$arPath, $enPath, $priority, $changeFrequency]) {
            $this->addPathPair(
                sitemap: $sitemap,
                arPath: $arPath,
                enPath: $enPath,
                priority: $priority,
                changeFrequency: $changeFrequency,
            );
        }
    }

    protected function addServices(Sitemap $sitemap): void
    {
        if (! class_exists(\App\Models\Service::class)) {
            return;
        }

        $query = \App\Models\Service::query();

        if ($this->hasScope(\App\Models\Service::class, 'active')) {
            $query->active();
        } elseif ($this->columnExists($query, 'is_active')) {
            $query->where('is_active', true);
        }

        $services = $query->orderBy('id')->get();

        foreach ($services as $service) {
            if (! $this->modelIsIndexable($service)) {
                continue;
            }

            $slug = $this->resolveSlug($service);
            if (! $slug) {
                continue;
            }

            $arUrl = $this->localizedUrl('ar', '/services/' . $slug);
            $enUrl = $this->localizedUrl('en', '/services/' . $slug);

            $this->addUrlWithAlternates(
                sitemap: $sitemap,
                url: $arUrl,
                priority: 0.80,
                changeFrequency: Url::CHANGE_FREQUENCY_WEEKLY,
                lastModificationDate: $this->resolveLastModificationDate($service),
                alternateAr: $arUrl,
                alternateEn: $enUrl,
            );
        }
    }

    protected function addPackages(Sitemap $sitemap): void
    {
        if (! class_exists(\App\Models\Package::class)) {
            return;
        }

        $query = \App\Models\Package::query();

        if ($this->hasScope(\App\Models\Package::class, 'active')) {
            $query->active();
        } elseif ($this->columnExists($query, 'is_active')) {
            $query->where('is_active', true);
        }

        $packages = $query->orderBy('id')->get();

        foreach ($packages as $package) {
            if (! $this->modelIsIndexable($package)) {
                continue;
            }

            $slug = $this->resolveSlug($package);
            if (! $slug) {
                continue;
            }

            $arUrl = $this->localizedUrl('ar', '/packages/' . $slug);
            $enUrl = $this->localizedUrl('en', '/packages/' . $slug);

            $this->addUrlWithAlternates(
                sitemap: $sitemap,
                url: $arUrl,
                priority: 0.70,
                changeFrequency: Url::CHANGE_FREQUENCY_WEEKLY,
                lastModificationDate: $this->resolveLastModificationDate($package),
                alternateAr: $arUrl,
                alternateEn: $enUrl,
            );
        }
    }

    protected function addPosts(Sitemap $sitemap): void
    {
        if (! class_exists(\App\Models\Post::class)) {
            return;
        }

        $query = \App\Models\Post::query();

        if ($this->hasScope(\App\Models\Post::class, 'published')) {
            $query->published();
        } elseif ($this->columnExists($query, 'is_published')) {
            $query->where('is_published', true);
        } elseif ($this->columnExists($query, 'status')) {
            $query->where('status', 'published');
        }

        $posts = $query->orderByDesc('id')->get();

        foreach ($posts as $post) {
            if (! $this->modelIsIndexable($post)) {
                continue;
            }

            $slug = $this->resolveSlug($post);
            if (! $slug) {
                continue;
            }

            $arUrl = $this->localizedUrl('ar', '/blog/' . $slug);
            $enUrl = $this->localizedUrl('en', '/blog/' . $slug);

            $this->addUrlWithAlternates(
                sitemap: $sitemap,
                url: $arUrl,
                priority: 0.70,
                changeFrequency: Url::CHANGE_FREQUENCY_MONTHLY,
                lastModificationDate: $this->resolveLastModificationDate($post),
                alternateAr: $arUrl,
                alternateEn: $enUrl,
            );
        }
    }

    protected function addPathPair(
        Sitemap $sitemap,
        string $arPath,
        string $enPath,
        float $priority,
        string $changeFrequency,
        Carbon|string|null $lastModificationDate = null,
    ): void {
        if (! $this->isIndexablePublicPath($arPath) || ! $this->isIndexablePublicPath($enPath)) {
            return;
        }

        $arUrl = $this->localizedUrl('ar', $arPath);
        $enUrl = $this->localizedUrl('en', $enPath);

        $this->addUrlWithAlternates(
            sitemap: $sitemap,
            url: $arUrl,
            priority: $priority,
            changeFrequency: $changeFrequency,
            lastModificationDate: $lastModificationDate,
            alternateAr: $arUrl,
            alternateEn: $enUrl,
        );
    }

    protected function addUrlWithAlternates(
        Sitemap $sitemap,
        string $url,
        float $priority,
        string $changeFrequency,
        Carbon|string|null $lastModificationDate,
        string $alternateAr,
        string $alternateEn,
    ): void {
        $url = $this->normalizeUrl($url);
        $alternateAr = $this->normalizeUrl($alternateAr);
        $alternateEn = $this->normalizeUrl($alternateEn);

        if (! $this->isIndexableUrl($url) || ! $this->isIndexableUrl($alternateAr) || ! $this->isIndexableUrl($alternateEn)) {
            return;
        }

        foreach (array_unique([$alternateAr, $alternateEn]) as $loc) {
            if (isset($this->seen[$loc])) {
                continue;
            }

            $this->seen[$loc] = true;

            $tag = Url::create($loc)
                ->setPriority($priority)
                ->setChangeFrequency($changeFrequency)
                ->addAlternate($alternateAr, 'ar')
                ->addAlternate($alternateEn, 'en')
                ->addAlternate($alternateAr, 'x-default');

            if ($lastModificationDate !== null) {
                $tag->setLastModificationDate($lastModificationDate);
            }

            $sitemap->add($tag);
        }
    }

    protected function modelIsIndexable(Model $model): bool
    {
        foreach (['is_active', 'active', 'published', 'is_published'] as $boolColumn) {
            if (isset($model->{$boolColumn}) && $model->{$boolColumn} === false) {
                return false;
            }
        }

        if (isset($model->noindex) && (bool) $model->noindex === true) {
            return false;
        }

        if (isset($model->robots) && is_string($model->robots) && str_contains(strtolower($model->robots), 'noindex')) {
            return false;
        }

        return true;
    }

    protected function resolveSlug(Model $model): ?string
    {
        foreach (['slug', 'slug_en', 'uri'] as $attribute) {
            $value = $model->{$attribute} ?? null;

            if (is_string($value) && trim($value) !== '') {
                return ltrim(trim($value), '/');
            }
        }

        return null;
    }

    protected function resolveLastModificationDate(Model $model): Carbon|string|null
    {
        foreach (['updated_at', 'published_at', 'created_at'] as $attribute) {
            $value = $model->{$attribute} ?? null;

            if ($value instanceof Carbon) {
                return $value;
            }

            if (is_string($value) && trim($value) !== '') {
                return $value;
            }
        }

        return null;
    }

    protected function isIndexableUrl(string $url): bool
    {
        if (parse_url($url, PHP_URL_QUERY)) {
            return false;
        }

        $path = parse_url($url, PHP_URL_PATH) ?: '/';

        return $this->isIndexablePublicPath($path);
    }

    protected function isIndexablePublicPath(string $path): bool
    {
        $path = '/' . ltrim($path, '/');
        $path = preg_replace('#/+#', '/', $path) ?: '/';
        $path = $path !== '/' ? rtrim($path, '/') : '/';

        if ($path === '/ar') {
            $path = '/';
        }

        if ($path === '/en/en') {
            return false;
        }

        if (in_array($path, $this->excludedExactPaths, true)) {
            return false;
        }

        if (preg_match('#^/(?:en/)?services/[0-9]+$#', $path)) {
            return false;
        }

        foreach ($this->excludedPrefixes as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return false;
            }
        }

        if (str_contains($path, '/feed')) {
            return false;
        }

        return true;
    }

    protected function hasScope(string $modelClass, string $scope): bool
    {
        return method_exists($modelClass, 'scope' . ucfirst($scope));
    }

    protected function columnExists(Builder $query, string $column): bool
    {
        try {
            return in_array($column, $query->getModel()->getFillable(), true)
                || array_key_exists($column, $query->getModel()->getAttributes())
                || Schema::hasColumn($query->getModel()->getTable(), $column);
        } catch (\Throwable) {
            return false;
        }
    }

    protected function normalizeUrl(string $url): string
    {
        $parts = parse_url($url);

        $scheme = $parts['scheme'] ?? 'https';
        $host = $parts['host'] ?? parse_url(config('app.url', 'https://amr-7.sa'), PHP_URL_HOST);
        $path = $parts['path'] ?? '/';
        $path = preg_replace('#/+#', '/', $path) ?: '/';
        $path = $path !== '/' ? rtrim($path, '/') : '/';

        if ($path === '/ar') {
            $path = '/';
        }

        if (str_starts_with($path, '/en/en/')) {
            $path = '/en/' . ltrim(substr($path, 7), '/');
        }

        if ($path === '/en/en') {
            $path = '/en';
        }

        return $scheme . '://' . $host . $path;
    }

    protected function localizedUrl(string $locale, string $path): string
    {
        $path = '/' . ltrim($path, '/');
        $path = preg_replace('#/+#', '/', $path) ?: '/';
        $path = $path !== '/' ? rtrim($path, '/') : '/';

        if ($locale === 'en') {
            if ($path === '/en') {
                return $this->normalizeUrl(url('/en'));
            }

            if (! str_starts_with($path, '/en/')) {
                $path = '/en' . ($path === '/' ? '' : $path);
            }

            return $this->normalizeUrl(url($path));
        }

        if ($path === '/en') {
            $path = '/';
        }

        if (str_starts_with($path, '/en/')) {
            $path = substr($path, 3);
            $path = $path === '' ? '/' : $path;
        }

        return $this->normalizeUrl(url($path));
    }
}
