<?php

namespace App\Http\Middleware;

use App\Support\Api\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mobile API — يضمن وجود منشأة نشطة لكل request محمي.
 *
 * أولوية القراءة:
 *   1) header `X-Company-ID` (للتجاوز الفوري)
 *   2) users.active_company_id (المُخزَّن من POST /api/mobile/companies/select)
 *
 * يستخدم envelope ApiResponse الموحَّد (Phase 2):
 *   - 401 unauthenticated
 *   - 412 company_required
 *   - 403 company_forbidden
 */
class EnsureMobileActiveCompany
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return ApiResponse::error('unauthenticated', 'Unauthenticated.', 401);
        }

        $companyId = $this->resolveCompanyId($request, $user);

        if ($companyId <= 0) {
            return ApiResponse::error(
                'company_required',
                'No active company selected. Call POST /api/mobile/companies/select or send X-Company-ID header.',
                412
            );
        }

        $isActiveMember = $user->companies()
            ->whereKey($companyId)
            ->wherePivot('is_active', true)
            ->exists();

        if (! $isActiveMember) {
            return ApiResponse::error(
                'company_forbidden',
                'You are not an active member of this company.',
                403
            );
        }

        $request->attributes->set('active_company_id', $companyId);

        return $next($request);
    }

    protected function resolveCompanyId(Request $request, $user): int
    {
        $header = $request->header('X-Company-ID');

        if ($header !== null && $header !== '') {
            return (int) $header;
        }

        return (int) ($user->active_company_id ?? 0);
    }
}
