<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InternalAmr7ApiTokenMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $expectedToken = (string) config('amr7-site-api.token', '');
        $providedToken = (string) $request->header('X-AMR7-SITE-TOKEN', '');

        if ($expectedToken === '' || $providedToken === '' || ! hash_equals($expectedToken, $providedToken)) {
            return new JsonResponse([
                'ok' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        return $next($request);
    }
}
