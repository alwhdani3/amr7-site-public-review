<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckN8nSecret
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. جلب المفتاح باستخدام config (أكثر أماناً ولا يتأثر بالكاش)
        // إذا لم يكن موجوداً في الـ config، نأخذه من الـ env كحل بديل
        $secret = config('services.n8n.secret', env('N8N_ACCESS_TOKEN'));
        
        // 2. جلب المفتاح من الهيدر (ونحوله إلى نص String لتجنب الأخطاء)
        $header = (string) $request->header('x-n8n-secret', '');

        // 3. التحقق الآمن باستخدام hash_equals
        if (empty($secret) || !hash_equals($secret, $header)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 401);
        }

        return $next($request);
    }
}