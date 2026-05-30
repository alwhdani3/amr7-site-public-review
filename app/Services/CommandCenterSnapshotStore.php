<?php

namespace App\Services;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

/**
 * Read/write JSON snapshots for the AMR7 Command Center.
 *
 * Storage layout:
 *   storage/app/private/command-center/{source}.json
 *
 * Snapshots are written by trusted internal callers (n8n workflow, server
 * probe, monitoring script) via the internal API, and read by the Filament
 * Command Center page. No live external calls happen at render time.
 */
class CommandCenterSnapshotStore
{
    public const SOURCES = [
        'n8n',
        'whatsapp',
        'server',
        'websites',
        'ssl',
        'backups',
        'security',
    ];

    /**
     * Keys that must never be persisted in a snapshot payload.
     * Treated case-insensitively. If any of these appears at any depth, the
     * write is rejected.
     */
    public const FORBIDDEN_KEYS = [
        'token',
        'secret',
        'password',
        'api_key',
        'apikey',
        'authorization',
        'auth',
        'cookie',
        'set-cookie',
        'x-amr7-site-token',
    ];

    public function __construct(private ?Filesystem $disk = null)
    {
        $this->disk ??= Storage::disk('private');
    }

    /**
     * Returns true if the given source name is allowed.
     */
    public function isAllowedSource(string $source): bool
    {
        return in_array($source, self::SOURCES, true);
    }

    /**
     * Returns the offending key (case-insensitive match) if the payload
     * contains a forbidden key at any depth, or null if clean.
     */
    public function findForbiddenKey(array $payload): ?string
    {
        foreach ($payload as $key => $value) {
            if (is_string($key) && in_array(strtolower($key), self::FORBIDDEN_KEYS, true)) {
                return $key;
            }

            if (is_array($value)) {
                $nested = $this->findForbiddenKey($value);
                if ($nested !== null) {
                    return $nested;
                }
            }
        }

        return null;
    }

    /**
     * Write a snapshot for a given source. Adds received_at automatically.
     * Returns the stored array (including received_at).
     *
     * @throws \InvalidArgumentException if the source is not allowed or the
     *                                   payload contains a forbidden key.
     */
    public function writeSnapshot(string $source, array $payload): array
    {
        if (! $this->isAllowedSource($source)) {
            throw new \InvalidArgumentException("Unknown snapshot source: {$source}");
        }

        if (($key = $this->findForbiddenKey($payload)) !== null) {
            throw new \InvalidArgumentException("Forbidden key in payload: {$key}");
        }

        $envelope = [
            'source' => $source,
            'received_at' => now()->toIso8601String(),
            'data' => $payload,
        ];

        $this->disk->put(
            $this->pathFor($source),
            json_encode($envelope, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
        );

        return $envelope;
    }

    /**
     * Read the latest snapshot for a source. Returns null if missing or
     * unreadable. Never throws.
     */
    public function readSnapshot(string $source): ?array
    {
        if (! $this->isAllowedSource($source)) {
            return null;
        }

        $path = $this->pathFor($source);

        if (! $this->disk->exists($path)) {
            return null;
        }

        try {
            $raw = $this->disk->get($path);

            if (! is_string($raw) || $raw === '') {
                return null;
            }

            $decoded = json_decode($raw, true);

            return is_array($decoded) ? $decoded : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * List all snapshots that currently exist on disk.
     *
     * @return array<string, array|null>  keyed by source name
     */
    public function listSnapshots(): array
    {
        $result = [];

        foreach (self::SOURCES as $source) {
            $result[$source] = $this->readSnapshot($source);
        }

        return $result;
    }

    private function pathFor(string $source): string
    {
        return 'command-center/' . $source . '.json';
    }
}
