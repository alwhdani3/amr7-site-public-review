<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocaleFromUrl
{
    public function handle(Request $request, Closure $next)
    {
        $seg = $request->segment(1);

        // /ar/... => redirect to no-prefix Arabic
        if ($seg === 'ar') {
            $path = preg_replace('#^ar/?#', '', $request->path());
            $path = ltrim($path, '/');
            $target = $path === '' ? '/' : '/' . $path;

            if ($request->getQueryString()) {
                $target .= '?' . $request->getQueryString();
            }

            return redirect($target, 301);
        }

        // /en/... => English
        if ($seg === 'en') {
            App::setLocale('en');
            session()->put('locale', 'en');

            if (auth()->check() && auth()->user()->locale !== 'en') {
                auth()->user()->forceFill(['locale' => 'en'])->save();
            }

            return $next($request);
        }

        // no prefix => decide locale
        $preferred = auth()->check()
            ? (auth()->user()->locale ?: 'ar')
            : (session('locale') ?: (request()->getPreferredLanguage(['ar', 'en']) ?: 'ar'));

        $preferred = $preferred === 'en' ? 'en' : 'ar';

        App::setLocale($preferred);
        session()->put('locale', $preferred);

        // لو اللغة المفضلة EN حوّل للرابط الإنجليزي
        if ($preferred === 'en') {
            $path = ltrim($request->path(), '/');
            $target = '/en' . ($path ? '/' . $path : '');

            if ($request->getQueryString()) {
                $target .= '?' . $request->getQueryString();
            }

            return redirect($target, 302);
        }

        return $next($request);
    }
}