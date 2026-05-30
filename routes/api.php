<?php

use App\Http\Controllers\Api\Internal\Amr7InternalController;
use App\Http\Controllers\Api\Internal\CommandCenterSnapshotController;
use App\Http\Controllers\Api\Internal\DocumentReviewCallbackController;
use App\Http\Controllers\Api\Mobile\AuthController as MobileAuthController;
use App\Http\Controllers\Api\Mobile\CompanyController as MobileCompanyController;
use App\Http\Controllers\Api\Mobile\CompanyDocumentController as MobileCompanyDocumentController;
use App\Http\Controllers\Api\Mobile\DashboardController as MobileDashboardController;
use App\Http\Controllers\Api\Mobile\NotificationController as MobileNotificationController;
use App\Http\Controllers\Api\Mobile\ProfileController as MobileProfileController;
use App\Http\Controllers\Api\Mobile\ServiceRequestController as MobileServiceRequestController;
use App\Http\Controllers\Api\Mobile\TicketController as MobileTicketController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PostController;
use App\Http\Middleware\CheckN8nSecret; // <--- ضروري جداً تعمل Import للكلاس

/*
|--------------------------------------------------------------------------
| 2) مسارات n8n
|--------------------------------------------------------------------------
*/

// CheckN8nSecret enforces the shared secret. A per-IP throttle gives us a
// second layer so a leaked secret can't be used to flood the endpoint
// while we rotate it. Legitimate n8n traffic is low-volume.
Route::post('/n8n/posts', [PostController::class, 'store'])
    ->middleware([CheckN8nSecret::class, 'throttle:10,1']);

Route::prefix('internal/amr7')
    ->middleware('internal.amr7.api')
    ->group(function (): void {
        Route::get('/health', [Amr7InternalController::class, 'health']);
        Route::get('/summary', [Amr7InternalController::class, 'summary']);
        Route::get('/services', [Amr7InternalController::class, 'services']);
        Route::get('/posts', [Amr7InternalController::class, 'posts']);
        Route::get('/leads', [Amr7InternalController::class, 'leads']);

        // Command Center snapshots (read-only dashboard backing store)
        Route::post('/command-center/snapshots/{source}', [CommandCenterSnapshotController::class, 'store'])
            ->where('source', '[a-z0-9_-]+');
        Route::get('/command-center/snapshots/{source}', [CommandCenterSnapshotController::class, 'show'])
            ->where('source', '[a-z0-9_-]+');
    });

/*
|--------------------------------------------------------------------------
| Phase 8: n8n document-review callback
|--------------------------------------------------------------------------
| Separate prefix because this endpoint uses its own X-AMR7-N8N-Token
| header (verified inline inside the controller — different secret than
| the X-AMR7-SITE-TOKEN used by `internal.amr7.api` group above).
| Throttled to 30 req/min per IP since legitimate n8n traffic is low-volume.
*/
Route::prefix('internal/document-review')
    ->middleware('throttle:30,1')
    ->group(function (): void {
        Route::post('/callback', DocumentReviewCallbackController::class)
            ->name('internal.document-review.callback');
    });

/*
|--------------------------------------------------------------------------
| 3) Mobile API (Sanctum)
|--------------------------------------------------------------------------
| Phase: Mobile API.
| - Sanctum personal access tokens (stateless).
| - throttle:6,1 على login لمنع brute force.
| - throttle:60,1 على باقي الـ endpoints.
| - middleware mobile.company يضمن وجود active_company_id صحيح.
*/
Route::prefix('mobile')->group(function (): void {

    // عام (بدون auth) — throttle مُشدَّد
    Route::middleware('throttle:6,1')->group(function (): void {
        Route::post('/login', [MobileAuthController::class, 'login'])->name('mobile.login');
    });

    // محمي بـ sanctum — throttle عام
    Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function (): void {

        // Auth & me
        Route::post('/logout', [MobileAuthController::class, 'logout'])->name('mobile.logout');
        Route::get('/me',      [MobileAuthController::class, 'me'])->name('mobile.me');

        // Profile (Phase 2 — لا يحتاج منشأة نشطة)
        Route::get('/profile',    [MobileProfileController::class, 'show'])->name('mobile.profile.show');
        Route::patch('/profile',  [MobileProfileController::class, 'update'])->name('mobile.profile.update');

        // Companies (لا يحتاج اختيار منشأة)
        Route::get('/companies',         [MobileCompanyController::class, 'index'])->name('mobile.companies.index');
        Route::post('/companies/select', [MobileCompanyController::class, 'select'])->name('mobile.companies.select');

        // Notifications (Phase 2 — مرتبطة بالمستخدم، خارج mobile.company)
        Route::get('/notifications',              [MobileNotificationController::class, 'index'])->name('mobile.notifications.index');
        Route::post('/notifications/read-all',    [MobileNotificationController::class, 'readAll'])->name('mobile.notifications.read-all');
        Route::post('/notifications/{id}/read',   [MobileNotificationController::class, 'markRead'])->name('mobile.notifications.read');

        // المسارات التي تتطلب منشأة نشطة
        Route::middleware('mobile.company')->group(function (): void {

            // Dashboard (Phase 2)
            Route::get('/dashboard', [MobileDashboardController::class, 'index'])->name('mobile.dashboard');

            // Service Requests
            Route::get('/requests',                    [MobileServiceRequestController::class, 'index'])->name('mobile.requests.index');
            Route::post('/requests',                   [MobileServiceRequestController::class, 'store'])->name('mobile.requests.store');
            Route::get('/requests/{id}',               [MobileServiceRequestController::class, 'show'])
                ->whereNumber('id')
                ->name('mobile.requests.show');
            Route::post('/requests/{id}/messages',     [MobileServiceRequestController::class, 'storeMessage'])
                ->whereNumber('id')
                ->name('mobile.requests.messages.store');

            // Tickets
            Route::get('/tickets',                  [MobileTicketController::class, 'index'])->name('mobile.tickets.index');
            Route::post('/tickets',                 [MobileTicketController::class, 'store'])->name('mobile.tickets.store');
            Route::get('/tickets/{id}',             [MobileTicketController::class, 'show'])
                ->whereNumber('id')
                ->name('mobile.tickets.show');
            Route::post('/tickets/{id}/replies',    [MobileTicketController::class, 'storeReply'])
                ->whereNumber('id')
                ->name('mobile.tickets.replies.store');

            // Documents (metadata only — download_url = null)
            Route::get('/documents',      [MobileCompanyDocumentController::class, 'index'])->name('mobile.documents.index');
            Route::get('/documents/{id}', [MobileCompanyDocumentController::class, 'show'])
                ->whereNumber('id')
                ->name('mobile.documents.show');
        });
    });
});
