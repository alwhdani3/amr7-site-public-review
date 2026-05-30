<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NoIndexSensitivePages
{
    public function handle(Request $request, Closure $next): Response
    {
        [$robots, $canonicalUrl] = $this->robotsFor($request);

        if ($robots) {
            $request->attributes->set('seo_robots', $robots);
            $request->attributes->set('seo_canonical_url', $canonicalUrl);
        }

        $response = $next($request);

        if ($robots) {
            $response->headers->set('X-Robots-Tag', str_replace(',', ', ', $robots));
        }

        return $response;
    }

    private function robotsFor(Request $request): array
    {
        $path = '/' . ltrim($request->path(), '/');
        if ($path === '//') {
            $path = '/';
        }

        $normalized = preg_replace('#^/en(?=/|$)#', '', $path) ?: '/';
        $cleanCanonical = $this->cleanCanonicalUrl($request);

        if ($path === '/lang' || str_starts_with($path, '/lang/')) {
            return ['noindex,follow', $cleanCanonical];
        }

        if ($this->hasDuplicateQuery($request)) {
            return ['noindex,follow,max-image-preview:large', $cleanCanonical];
        }

        $privatePrefixes = [
            '/amr7',
            '/admin',
            '/nova',
            '/login',
            '/register',
            '/forgot-password',
            '/reset-password',
            '/email/verify',
            '/confirm-password',
            '/two-factor-challenge',
            '/settings',
            '/staff',
            '/dashboard',
            '/company',
            '/companies',
            '/files',
            '/fs',
            '/financial-statements/dashboard',
        ];

        $publicNoIndexPrefixes = [
            '/services/request',
        ];

        if ($this->matchesAnyPrefix($path, $normalized, $privatePrefixes)) {
            return ['noindex,nofollow,noarchive', $cleanCanonical];
        }

        if ($this->matchesAnyPrefix($path, $normalized, $publicNoIndexPrefixes)) {
            return ['noindex,follow,max-image-preview:large', $cleanCanonical];
        }

        return [null, $cleanCanonical];
    }

    private function matchesAnyPrefix(string $path, string $normalized, array $prefixes): bool
    {
        foreach ($prefixes as $prefix) {
            if (
                $path === $prefix ||
                $normalized === $prefix ||
                str_starts_with($path, $prefix . '/') ||
                str_starts_with($path, '/en' . $prefix . '/') ||
                str_starts_with($normalized, $prefix . '/')
            ) {
                return true;
            }
        }

        return false;
    }

    private function hasDuplicateQuery(Request $request): bool
    {
        $query = $request->query();

        if ($query === []) {
            return false;
        }

        return array_intersect(config('seo.duplicate_query_keys', []), array_keys($query)) !== [];
    }

    private function cleanCanonicalUrl(Request $request): string
    {
        return $request->url();
    }
}
