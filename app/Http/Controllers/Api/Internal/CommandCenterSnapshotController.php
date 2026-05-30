<?php

namespace App\Http\Controllers\Api\Internal;

use App\Http\Controllers\Controller;
use App\Services\CommandCenterSnapshotStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Internal-only endpoints for AMR7 Command Center snapshots.
 *
 * Protected by the existing 'internal.amr7.api' middleware (token header
 * X-AMR7-SITE-TOKEN). Trusted callers (n8n workflow, monitoring scripts,
 * server probes) POST a JSON payload per source; the Filament Command
 * Center page reads back the latest snapshot per source.
 */
class CommandCenterSnapshotController extends Controller
{
    public function __construct(private CommandCenterSnapshotStore $store)
    {
    }

    public function store(Request $request, string $source): JsonResponse
    {
        if (! $this->store->isAllowedSource($source)) {
            return response()->json([
                'ok' => false,
                'message' => 'Unknown snapshot source.',
                'allowed' => CommandCenterSnapshotStore::SOURCES,
            ], 404);
        }

        $payload = $request->json()->all();

        if (! is_array($payload)) {
            return response()->json([
                'ok' => false,
                'message' => 'Payload must be a JSON object.',
            ], 422);
        }

        if (($forbidden = $this->store->findForbiddenKey($payload)) !== null) {
            return response()->json([
                'ok' => false,
                'message' => 'Payload contains a forbidden key.',
                'forbidden_key' => $forbidden,
            ], 422);
        }

        $envelope = $this->store->writeSnapshot($source, $payload);

        return response()->json([
            'ok' => true,
            'source' => $envelope['source'],
            'received_at' => $envelope['received_at'],
        ]);
    }

    public function show(string $source): JsonResponse
    {
        if (! $this->store->isAllowedSource($source)) {
            return response()->json([
                'ok' => false,
                'message' => 'Unknown snapshot source.',
                'allowed' => CommandCenterSnapshotStore::SOURCES,
            ], 404);
        }

        $snapshot = $this->store->readSnapshot($source);

        if ($snapshot === null) {
            return response()->json([
                'ok' => true,
                'source' => $source,
                'received_at' => null,
                'data' => null,
            ]);
        }

        return response()->json([
            'ok' => true,
            'source' => $snapshot['source'] ?? $source,
            'received_at' => $snapshot['received_at'] ?? null,
            'data' => $snapshot['data'] ?? null,
        ]);
    }
}
