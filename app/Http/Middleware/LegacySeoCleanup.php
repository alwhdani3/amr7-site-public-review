<?php

namespace App\Http\Middleware;

use App\Models\Service;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LegacySeoCleanup
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
            return $next($request);
        }

        $path = $this->normalizedPath($request);
        $withoutTrailingSlash = rtrim($path, '/') ?: '/';

        if ($target = $this->indexFileTarget($withoutTrailingSlash)) {
            return $this->redirectResponse($target);
        }

        if ($withoutTrailingSlash === '/' && ($request->query->has('p') || $request->query->has('page_id'))) {
            return $this->goneResponse();
        }

        if ($target = $this->duplicateEnglishTarget($withoutTrailingSlash, $request)) {
            return $this->redirectResponse($target);
        }

        if ($target = $this->duplicateArabicTarget($withoutTrailingSlash, $request)) {
            return $this->redirectResponse($target);
        }

        if ($target = $this->numericServiceTarget($withoutTrailingSlash)) {
            return $this->redirectResponse($target);
        }

        [$locale, $pathWithoutLocale] = $this->stripLocalePrefix($withoutTrailingSlash);

        if ($target = $this->staticRedirectTarget($pathWithoutLocale, $locale)) {
            return $this->redirectResponse($target);
        }

        if ($this->isGoneLegacyPath($pathWithoutLocale)) {
            return $this->goneResponse();
        }

        if ($target = $this->arabicDuplicateTarget($withoutTrailingSlash, $pathWithoutLocale, $request)) {
            return $this->redirectResponse($target);
        }

        return $next($request);
    }

    private function indexFileTarget(string $path): ?string
    {
        $map = [
            '/index.html'    => '/',
            '/index.php'     => '/',
            '/en/index.html' => '/en',
            '/en/index.php'  => '/en',
            '/ar/index.html' => '/',
            '/ar/index.php'  => '/',
        ];

        return $map[$path] ?? null;
    }

    private function normalizedPath(Request $request): string
    {
        $path = '/' . ltrim($request->decodedPath(), '/');

        return $path === '//' ? '/' : $path;
    }

    private function duplicateEnglishTarget(string $path, Request $request): ?string
    {
        if (! preg_match('#^/en/en(?:/(.*))?$#', $path, $matches)) {
            return null;
        }

        $target = '/en/' . ltrim($matches[1] ?? '', '/');
        $target = rtrim($target, '/') ?: '/en';

        return $this->appendQueryString($target, $request);
    }

    private function duplicateArabicTarget(string $path, Request $request): ?string
    {
        if (! preg_match('#^/ar/ar(?:/(.*))?$#', $path, $matches)) {
            return null;
        }

        $target = '/' . ltrim($matches[1] ?? '', '/');
        $target = rtrim($target, '/') ?: '/';

        return $this->appendQueryString($target, $request);
    }

    private function numericServiceTarget(string $path): ?string
    {
        if (! preg_match('#^/(?:(en|ar)/)?services/([0-9]+)$#', $path, $matches)) {
            return null;
        }

        $locale = $matches[1] ?? null;
        $slug = $this->serviceSlug((int) $matches[2]);
        $base = $locale === 'en' ? '/en/services' : '/services';

        return $slug ? $base . '/' . ltrim($slug, '/') : $base;
    }

    private function serviceSlug(int $id): ?string
    {
        try {
            $slug = Service::query()->whereKey($id)->value('slug');
        } catch (\Throwable) {
            return null;
        }

        $slug = trim((string) $slug);

        return $slug !== '' ? $slug : null;
    }

    private function stripLocalePrefix(string $path): array
    {
        if (preg_match('#^/(en|ar)(/.*)?$#', $path, $matches)) {
            return [$matches[1], $matches[2] ?? '/'];
        }

        return [null, $path];
    }

    private function staticRedirectTarget(string $path, ?string $locale): ?string
    {
        $targets = [
            '/contact' => '/contact-us',
            '/terms' => '/using-policy',
            '/about-us' => '/about',
            '/company-formation-saudi-arabia' => '/company-formation/riyadh',
            '/من-نحن-شركة-امر-سبعة-لحلول-الأعمال' => '/about',
        ];

        if (! isset($targets[$path])) {
            return null;
        }

        $target = $targets[$path];

        return $locale === 'en' && $target !== '/' ? '/en' . $target : $target;
    }

    private function isGoneLegacyPath(string $path): bool
    {
        if (preg_match('#^/(?:wp-[^/]*|xmlrpc)\.php$#', $path) || preg_match('#^/elementor-.+#', $path)) {
            return true;
        }

        foreach ($this->gonePrefixes() as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                return true;
            }
        }

        return false;
    }

    private function gonePrefixes(): array
    {
        return [
            '/wp-content',
            '/wp-admin',
            '/wp-includes',
            '/product-category',
            '/product-tag',
            '/product',
            '/portfolio',
            '/portfolio__trashed',
            '/project-cat',
            '/tag',
            '/category',
            '/author',
            '/page',
            '/feed',
            '/comments/feed',
            '/shop',
            '/cart',
            '/checkout',
            '/my-account',
            '/wishlist',
        ];
    }

    private function arabicDuplicateTarget(string $path, string $pathWithoutLocale, Request $request): ?string
    {
        if ($path !== '/ar' && ! str_starts_with($path, '/ar/')) {
            return null;
        }

        if ($path === '/ar') {
            return '/';
        }

        if (! $this->isKnownPublicPath($pathWithoutLocale)) {
            return null;
        }

        return $this->appendQueryString($pathWithoutLocale, $request);
    }

    private function isKnownPublicPath(string $path): bool
    {
        $exactPaths = [
            '/',
            '/about',
            '/contact-us',
            '/faq',
            '/privacy-policy',
            '/using-policy',
            '/services',
            '/packages',
            '/blog',
            '/banks',
            '/company-formation/riyadh',
            '/foreign-investment',
            '/company-liquidation',
        ];

        if (in_array($path, $exactPaths, true)) {
            return true;
        }

        return preg_match('#^/services/(?:platform/[A-Za-z0-9_-]+|[A-Za-z0-9_-]+)$#', $path) === 1
            || preg_match('#^/blog/[A-Za-z0-9_-]+$#', $path) === 1
            || preg_match('#^/packages/[A-Za-z0-9_-]+$#', $path) === 1;
    }

    private function appendQueryString(string $target, Request $request): string
    {
        $query = $request->query();

        foreach (config('seo.duplicate_query_keys', []) as $key) {
            unset($query[$key]);
        }

        return $query ? $target . '?' . http_build_query($query) : $target;
    }

    private function redirectResponse(string $target): Response
    {
        return redirect()
            ->to($target, 301)
            ->withHeaders(['X-Robots-Tag' => 'noindex, follow']);
    }

    private function goneResponse(): Response
    {
        return response('', 410)
            ->withHeaders(['X-Robots-Tag' => 'noindex, nofollow, noarchive']);
    }
}
