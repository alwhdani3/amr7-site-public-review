<?php

namespace App\Http\Controllers\Api\Internal;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Amr7InternalController extends Controller
{
    public function health(): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'service' => 'amr7-internal-api',
        ]);
    }

    public function summary(): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'services' => $this->countTable('services'),
            'posts' => $this->countTable('posts', 'is_published', 1),
            'leads' => $this->countTable('service_requests'),
            'tickets' => $this->countTable('tickets'),
        ]);
    }

    public function services(): JsonResponse
    {
        if (! Schema::hasTable('services')) {
            return $this->missingTableResponse('services');
        }

        $columns = $this->availableColumns('services', [
            'id',
            'slug',
            'title_ar',
            'title_en',
            'price',
            'is_active',
            'created_at',
            'updated_at',
        ]);

        $query = DB::table('services')->select($columns);

        if (Schema::hasColumn('services', 'is_active')) {
            $query->orderByDesc('is_active');
        }

        $services = $query
            ->orderByDesc('id')
            ->limit($this->defaultLimit())
            ->get();

        return response()->json([
            'ok' => true,
            'table' => 'services',
            'count' => DB::table('services')->count(),
            'items' => $services,
        ]);
    }

    public function posts(): JsonResponse
    {
        if (! Schema::hasTable('posts')) {
            return $this->missingTableResponse('posts');
        }

        $columns = $this->availableColumns('posts', [
            'id',
            'title',
            'slug',
            'excerpt',
            'is_published',
            'published_at',
            'created_at',
            'updated_at',
        ]);

        $query = DB::table('posts')->select($columns);

        if (Schema::hasColumn('posts', 'is_published')) {
            $query->where('is_published', 1)->orderByDesc('published_at');
        }

        $posts = $query
            ->orderByDesc('id')
            ->limit($this->defaultLimit())
            ->get();

        return response()->json([
            'ok' => true,
            'table' => 'posts',
            'count' => $this->countTable('posts', 'is_published', 1),
            'items' => $posts,
        ]);
    }

    public function leads(): JsonResponse
    {
        if (! Schema::hasTable('service_requests')) {
            return $this->missingTableResponse('service_requests');
        }

        $columns = $this->availableColumns('service_requests', [
            'id',
            'service_id',
            'establishment_name',
            'cr_number',
            'phone',
            'status',
            'created_at',
            'updated_at',
        ]);

        $query = DB::table('service_requests')->select($columns);

        if (Schema::hasColumn('service_requests', 'description')) {
            $query->addSelect('description');
        }

        $items = $query
            ->orderByDesc('id')
            ->limit($this->defaultLimit())
            ->get()
            ->map(function (object $lead): object {
                if (property_exists($lead, 'description')) {
                    $lead->message_preview = mb_substr(
                        (string) $lead->description,
                        0,
                        (int) config('amr7-site-api.lead_message_preview_limit', 160)
                    );
                    unset($lead->description);
                }

                return $lead;
            })
            ->values();

        return response()->json([
            'ok' => true,
            'table' => 'service_requests',
            'count' => DB::table('service_requests')->count(),
            'items' => $items,
        ]);
    }

    private function countTable(string $table, ?string $column = null, mixed $value = null): array
    {
        if (! Schema::hasTable($table)) {
            return [
                'ok' => false,
                'table' => $table,
                'count' => null,
            ];
        }

        $query = DB::table($table);

        if ($column !== null && Schema::hasColumn($table, $column)) {
            $query->where($column, $value);
        }

        return [
            'ok' => true,
            'table' => $table,
            'count' => $query->count(),
        ];
    }

    private function availableColumns(string $table, array $columns): array
    {
        return array_values(array_filter(
            $columns,
            static fn (string $column): bool => Schema::hasColumn($table, $column)
        ));
    }

    private function missingTableResponse(string $table): JsonResponse
    {
        return response()->json([
            'ok' => false,
            'table' => $table,
            'message' => 'Table is missing.',
        ]);
    }

    private function defaultLimit(): int
    {
        return (int) config('amr7-site-api.default_limit', 10);
    }
}
