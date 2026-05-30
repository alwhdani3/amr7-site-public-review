<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * TODO (P1.6 cleanup): هذه الـ middleware غير مسجّلة في bootstrap/app.php
 * ولا تستخدمها أي route حاليًا. تعتمد على users.role legacy.
 *
 * في Phase 2: إما حذفها أو تسجيلها كـ alias وربطها بصلاحية Spatie
 * بدلاً من قراءة users.role.
 */
class AdminOrAgent
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_unless($user, 403);

        if (method_exists($user, 'hasBackofficeAccess') && $user->hasBackofficeAccess()) {
            return $next($request);
        }

        $legacyRole = strtolower((string) ($user->role ?? ''));

        if (in_array($legacyRole, ['admin', 'superadmin', 'manager', 'agent', 'staff', 'employee', 'support'], true)) {
            return $next($request);
        }

        abort(403, 'غير مصرح لك بالوصول إلى هذه الصفحة.');
    }
}