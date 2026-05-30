<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CleanQueryParameters
{
    public function handle(Request $request, Closure $next): Response
    {
        $query = $request->query();
        $changed = false;

        if (array_key_exists('page', $query) && (string) $query['page'] === '1') {
            unset($query['page']);
            $changed = true;
        }

        if (array_key_exists('s', $query)) {
            unset($query['s']);
            $changed = true;
        }

        if ($changed) {
            $target = $request->url();

            if (! empty($query)) {
                $target .= '?' . http_build_query($query);
            }

            return redirect()
                ->to($target, 301)
                ->withHeaders(['X-Robots-Tag' => 'noindex, follow']);
        }

        return $next($request);
    }
}
