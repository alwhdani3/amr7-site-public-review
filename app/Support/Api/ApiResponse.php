<?php

namespace App\Support\Api;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * Mobile API Phase 2 — Envelope موحَّد لكل ردود /api/mobile/*.
 *
 * Schema:
 *   {
 *     "success": true|false,
 *     "data": mixed|null,
 *     "message": string|null,
 *     "meta": object (defaults to {})
 *   }
 *
 * أخطاء: meta.code إلزامي، meta.errors اختياري (validation).
 */
class ApiResponse
{
    public static function success(mixed $data = null, ?string $message = null, array $meta = [], int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => static::resolveData($data),
            'message' => $message,
            'meta'    => (object) $meta,
        ], $status);
    }

    public static function error(string $code, ?string $message = null, int $status = 400, array $extra = []): JsonResponse
    {
        $meta = array_merge(['code' => $code], $extra);

        return response()->json([
            'success' => false,
            'data'    => null,
            'message' => $message,
            'meta'    => (object) $meta,
        ], $status);
    }

    /**
     * يحوّل LengthAwarePaginator إلى envelope موحَّد مع meta.pagination.
     * يقبل اسم Resource class اختياريًا لتحويل العناصر.
     */
    public static function paginated(LengthAwarePaginator $paginator, ?string $resourceClass = null, ?string $message = null, array $extraMeta = []): JsonResponse
    {
        $items = $resourceClass
            ? $resourceClass::collection($paginator->getCollection())->resolve()
            : $paginator->getCollection()->all();

        $meta = array_merge([
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'last_page'    => $paginator->lastPage(),
                'has_more'     => $paginator->hasMorePages(),
            ],
        ], $extraMeta);

        return response()->json([
            'success' => true,
            'data'    => $items,
            'message' => $message,
            'meta'    => (object) $meta,
        ]);
    }

    /**
     * يحوّل Exception إلى envelope خطأ موحَّد. يُستدعى من bootstrap/app.php
     * لكل request يبدأ بـ api/mobile/*.
     */
    public static function renderException(Throwable $e): JsonResponse
    {
        if ($e instanceof \Illuminate\Validation\ValidationException) {
            return static::error('validation_failed', 'Validation failed.', 422, [
                'errors' => $e->errors(),
            ]);
        }

        if ($e instanceof \Illuminate\Auth\AuthenticationException) {
            return static::error('unauthenticated', 'Unauthenticated.', 401);
        }

        if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
            return static::error('forbidden', $e->getMessage() ?: 'Forbidden.', 403);
        }

        if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            return static::error('not_found', 'Resource not found.', 404);
        }

        if ($e instanceof NotFoundHttpException) {
            return static::error('not_found', 'Endpoint not found.', 404);
        }

        if ($e instanceof HttpException) {
            $status = $e->getStatusCode();
            $code = match ($status) {
                401 => 'unauthenticated',
                403 => 'forbidden',
                404 => 'not_found',
                405 => 'method_not_allowed',
                412 => 'precondition_required',
                422 => 'validation_failed',
                429 => 'too_many_requests',
                default => 'http_error',
            };

            return static::error($code, $e->getMessage() ?: 'HTTP error.', $status);
        }

        // غير معروف — نعرض تفاصيل في وضع debug فقط.
        if (config('app.debug')) {
            return static::error('server_error', $e->getMessage(), 500, [
                'exception' => get_class($e),
            ]);
        }

        return static::error('server_error', 'Internal server error.', 500);
    }

    protected static function resolveData(mixed $data): mixed
    {
        if ($data === null) {
            return null;
        }

        if ($data instanceof JsonResource) {
            return $data->resolve();
        }

        if ($data instanceof Arrayable) {
            return $data->toArray();
        }

        return $data;
    }
}
