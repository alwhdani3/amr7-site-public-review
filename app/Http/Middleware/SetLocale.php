<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $allowed = ['ar', 'en'];

        // 1) اللغة من أول جزء في الرابط
        $firstSegment = $request->segment(1);
        if (in_array($firstSegment, $allowed, true)) {
            $locale = $firstSegment;

            App::setLocale($locale);
            session(['locale' => $locale]);
            cookie()->queue(cookie('locale', $locale, 60 * 24 * 30));

            return $next($request);
        }

        // 2) اللغة من السيشن أو الكوكي
        $locale = session('locale') ?: $request->cookie('locale');

        // 3) fallback
        if (! in_array($locale, $allowed, true)) {
            $locale = config('app.locale', 'ar');
        }

        App::setLocale($locale);

        return $next($request);
    }
}