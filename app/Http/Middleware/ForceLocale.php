<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceLocale
{
    public function handle(Request $request, Closure $next, string $locale = 'ar')
    {
        app()->setLocale($locale);

        // session helper ok
        session(['locale' => $locale]);

        // ✅ لا تحدث إلا إذا فعلاً تغيّر
        if (auth()->check() && auth()->user()->locale !== $locale) {
            auth()->user()->forceFill(['locale' => $locale])->save();
        }

        return $next($request);
    }
}
