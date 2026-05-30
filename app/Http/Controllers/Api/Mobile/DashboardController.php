<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\CompanyDocument;
use App\Models\ServiceRequest;
use App\Models\Ticket;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Mobile API Phase 2 — Dashboard aggregate للمنشأة النشطة.
 * يجمع stats سريعة بدون كشف تفاصيل حسّاسة.
 */
class DashboardController extends Controller
{
    /**
     * GET /api/mobile/dashboard
     */
    public function index(Request $request): JsonResponse
    {
        $companyId = (int) $request->attributes->get('active_company_id');
        $user      = $request->user();

        // Tickets scoping — يحترم الصلاحيات
        $ticketsQuery = Ticket::query()->where('company_id', $companyId);
        if (! $user->hasPermissionTo('tickets.view_all')) {
            $ticketsQuery->where('user_id', $user->id);
        }

        $requestsQuery = ServiceRequest::query()->where('company_id', $companyId);
        if (! $user->hasPermissionTo('service_requests.view_all')) {
            $requestsQuery->where('user_id', $user->id);
        }

        $stats = [
            'tickets' => [
                'open'    => (clone $ticketsQuery)->where('status', '!=', 'closed')->count(),
                'closed'  => (clone $ticketsQuery)->where('status', 'closed')->count(),
                'overdue' => (clone $ticketsQuery)->where('status', '!=', 'closed')
                    ->whereNotNull('sla_deadline')
                    ->where('sla_deadline', '<', now())
                    ->count(),
            ],
            'service_requests' => [
                'pending' => (clone $requestsQuery)->whereIn('status', ['pending', 'new'])->count(),
                'in_progress' => (clone $requestsQuery)->where('status', 'processing')->count(),
                'completed'   => (clone $requestsQuery)->where('status', 'completed')->count(),
            ],
            'documents' => [
                'expired'      => CompanyDocument::where('company_id', $companyId)->where('alert_stage', 'expired')->count(),
                'expiring_7d'  => CompanyDocument::where('company_id', $companyId)->where('alert_stage', '7d')->count(),
                'expiring_30d' => CompanyDocument::where('company_id', $companyId)->where('alert_stage', '30d')->count(),
                'expiring_60d' => CompanyDocument::where('company_id', $companyId)->where('alert_stage', '60d')->count(),
                'total'        => CompanyDocument::where('company_id', $companyId)->count(),
            ],
            'notifications' => [
                'unread' => $user->unreadNotifications()->count(),
            ],
        ];

        return ApiResponse::success(
            $stats,
            null,
            ['active_company_id' => $companyId],
        );
    }
}
