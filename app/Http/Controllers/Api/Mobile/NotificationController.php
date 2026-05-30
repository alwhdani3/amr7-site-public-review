<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Mobile\NotificationResource;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * GET /api/mobile/notifications
     * يعيد فقط إشعارات المستخدم الحالي. paginated.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $perPage = (int) $request->query('per_page', 20);
        $perPage = max(5, min($perPage, 50));

        $paginator = $user->notifications()
            ->latest()
            ->paginate($perPage);

        return ApiResponse::paginated(
            $paginator,
            NotificationResource::class,
            null,
            ['unread_count' => $user->unreadNotifications()->count()],
        );
    }

    /**
     * POST /api/mobile/notifications/{id}/read
     */
    public function markRead(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        $notification = $user->notifications()->whereKey($id)->first();

        if (! $notification) {
            return ApiResponse::error('not_found', 'الإشعار غير موجود.', 404);
        }

        if (! $notification->read_at) {
            $notification->markAsRead();
        }

        return ApiResponse::success(
            new NotificationResource($notification->fresh()),
            'تم تحديد الإشعار كمقروء.',
        );
    }

    /**
     * POST /api/mobile/notifications/read-all
     */
    public function readAll(Request $request): JsonResponse
    {
        $user = $request->user();

        $count = $user->unreadNotifications()->count();
        $user->unreadNotifications()->update(['read_at' => now()]);

        return ApiResponse::success(
            ['marked_count' => $count],
            'تم تحديد كل الإشعارات كمقروءة.',
        );
    }
}
