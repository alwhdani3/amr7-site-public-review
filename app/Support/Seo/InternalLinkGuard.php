<?php

namespace App\Support\Seo;

use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Throwable;

class InternalLinkGuard
{
    private const FORBIDDEN_PREFIXES = [
        '/lang/',
        '/services/request/',
        '/en/services/request/',
        '/ar/services/request/',
        '/wp-content/',
        '/wp-admin/',
        '/product-category/',
        '/product/',
        '/portfolio/',
        '/portfolio__trashed/',
        '/project-cat/',
        '/tag/',
        '/wishlist/',
    ];

    private const FORBIDDEN_CONTAINS = [
        '/elementor-',
        '/wp-',
    ];

    public static function cleanRelatedLinks(array $links): array
    {
        $clean = [];

        foreach ($links as $link) {
            $prepared = self::routeLink($link);

            if ($prepared === null) {
                continue;
            }

            $clean[] = $prepared;

            if (count($clean) >= 6) {
                break;
            }
        }

        return $clean;
    }

    public static function routeLink(array $link): ?array
    {
        if (empty($link['route']) || empty($link['label'])) {
            return null;
        }

        try {
            $url = route($link['route'], $link['params'] ?? []);

            if (class_exists(LaravelLocalization::class)) {
                $url = LaravelLocalization::getLocalizedURL(app()->getLocale() === 'en' ? 'en' : 'ar', $url);
            }
        } catch (Throwable) {
            return null;
        }

        if (! self::isAllowedInternalUrl($url)) {
            return null;
        }

        return [
            'label' => $link['label'],
            'description' => $link['description'] ?? null,
            'url' => $url,
        ];
    }

    public static function isAllowedInternalUrl(string $url): bool
    {
        $parts = parse_url($url);
        $path = '/' . ltrim((string) ($parts['path'] ?? '/'), '/');

        if (! empty($parts['query'])) {
            return false;
        }

        foreach (self::FORBIDDEN_PREFIXES as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return false;
            }
        }

        foreach (self::FORBIDDEN_CONTAINS as $needle) {
            if (str_contains($path, $needle)) {
                return false;
            }
        }

        return true;
    }
}
