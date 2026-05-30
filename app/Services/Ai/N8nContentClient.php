<?php

namespace App\Services\Ai;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * موحّد لاستدعاءات n8n (تحسين / إعادة صياغة / استيراد من رابط / مستشار العقود).
 *
 * الردّ دائماً مغلَّف بشكل موحد:
 * [
 *     'ok'     => bool,        // true إذا 2xx + body غير فاضي + JSON صالح (أو نص للعقود)
 *     'reason' => string|null, // سبب الفشل من REASON_*
 *     'status' => int|null,    // HTTP status لو وُجد
 *     'data'   => array,       // محتوى الرد (مفاتيح JSON أو ['raw' => string])
 * ]
 */
class N8nContentClient
{
    public const REASON_DISABLED      = 'disabled';
    public const REASON_EMPTY         = 'empty_response';
    public const REASON_INVALID_JSON  = 'invalid_json';
    public const REASON_HTTP          = 'http_error';
    public const REASON_EXCEPTION     = 'exception';

    public function rewrite(string $title, string $content, string $url = '', string $locale = 'ar'): array
    {
        return $this->callJson(
            hookKey: 'rewrite_webhook',
            label: 'rewrite',
            payload: [
                'title'   => $title,
                'content' => $content,
                'url'     => $url,
                'lang'    => $locale,
                'brand'   => 'amr7',
            ],
        );
    }

    public function improve(string $content, string $locale = 'ar'): array
    {
        return $this->callJson(
            hookKey: 'improve_webhook',
            label: 'improve',
            payload: [
                'content' => $content,
                'lang'    => $locale,
                'brand'   => 'amr7',
                'action'  => 'improve_only',
            ],
        );
    }

    public function importFromUrl(string $url, string $locale = 'ar'): array
    {
        return $this->callJson(
            hookKey: 'import_webhook',
            label: 'import',
            payload: [
                'url'   => $url,
                'lang'  => $locale,
                'brand' => 'amr7',
            ],
        );
    }

    /**
     * مستشار العقود — يقبل multipart مع ملف اختياري ويتسامح مع رد text/plain.
     *
     * @param  resource|null $fileResource
     */
    public function callContractAgent(array $payload, $fileResource = null, ?string $fileName = null): array
    {
        $hook    = config('services.n8n.webhooks.contracts');
        $secret  = (string) config('services.n8n.secret', '');
        $timeout = (int) config('services.n8n.timeout', 120);

        if (! $hook) {
            Log::warning('n8n.contracts: webhook not configured');
            return $this->fail(self::REASON_DISABLED);
        }

        try {
            $request = Http::timeout($timeout)->withHeaders([
                'x-api-key' => $secret,
            ]);

            if (is_resource($fileResource) && $fileName) {
                $request = $request->attach('file', $fileResource, $fileName);
            }

            $response = $request->post($hook, $payload);
            $status   = $response->status();

            if (! $response->successful()) {
                Log::error("n8n.contracts: http {$status}", [
                    'body_excerpt' => $this->excerpt($response->body()),
                ]);
                return $this->fail(self::REASON_HTTP, $status);
            }

            $body = (string) $response->body();
            if (trim($body) === '') {
                Log::error('n8n.contracts: empty body', ['status' => $status]);
                return $this->fail(self::REASON_EMPTY, $status);
            }

            // نحاول JSON أولاً، وإذا فشل نقبل النص الخام كنتيجة.
            $json = $response->json();
            if (is_array($json) && $json !== []) {
                return [
                    'ok'     => true,
                    'reason' => null,
                    'status' => $status,
                    'data'   => [
                        'json'   => $json,
                        'result' => is_string($json['result'] ?? null) ? $json['result'] : null,
                        'raw'    => null,
                    ],
                ];
            }

            return [
                'ok'     => true,
                'reason' => null,
                'status' => $status,
                'data'   => [
                    'json'   => null,
                    'result' => null,
                    'raw'    => trim($body),
                ],
            ];
        } catch (\Throwable $e) {
            Log::error('n8n.contracts: exception', ['error' => $e->getMessage()]);
            return $this->fail(self::REASON_EXCEPTION);
        }
    }

    /**
     * يحوّل reason إلى رسالة بالعربية تصلح لعرضها في Notification.
     */
    public function reasonMessage(?string $reason, ?int $status = null): string
    {
        return match ($reason) {
            self::REASON_DISABLED     => 'الخدمة غير مفعّلة: لم يتم ضبط رابط webhook الخاص بـ n8n.',
            self::REASON_EMPTY        => 'n8n رجّع رداً فارغاً (bytes=0) — لم يتم تعديل المحتوى.',
            self::REASON_INVALID_JSON => 'n8n رجّع رداً غير صالح (ليس JSON صحيحاً).',
            self::REASON_HTTP         => 'فشل الاتصال بـ n8n (الكود: ' . ($status ?? '?') . ').',
            self::REASON_EXCEPTION    => 'انقطع الاتصال بـ n8n. حاول مرة أخرى.',
            default                   => 'فشل غير معروف من جانب n8n.',
        };
    }

    private function callJson(string $hookKey, string $label, array $payload): array
    {
        $hook    = config('services.n8n.' . $hookKey);
        $secret  = (string) config('services.n8n.secret', '');
        $timeout = (int) config('services.n8n.timeout', 120);

        if (! $hook) {
            Log::warning("n8n.{$label}: webhook not configured", ['hook_key' => $hookKey]);
            return $this->fail(self::REASON_DISABLED);
        }

        try {
            $response = Http::timeout($timeout)
                ->withHeaders([
                    'x-api-key' => $secret,
                    'accept'    => 'application/json',
                ])
                ->post($hook, $payload);

            return $this->parseJson($response, $label);
        } catch (\Throwable $e) {
            Log::error("n8n.{$label}: exception", ['error' => $e->getMessage()]);
            return $this->fail(self::REASON_EXCEPTION);
        }
    }

    private function parseJson(Response $response, string $label): array
    {
        $status = $response->status();

        if (! $response->successful()) {
            Log::error("n8n.{$label}: http {$status}", [
                'body_excerpt' => $this->excerpt($response->body()),
            ]);
            return $this->fail(self::REASON_HTTP, $status);
        }

        $body = (string) $response->body();

        if (trim($body) === '') {
            Log::error("n8n.{$label}: empty body", ['status' => $status]);
            return $this->fail(self::REASON_EMPTY, $status);
        }

        $data = $response->json();

        if (! is_array($data)) {
            Log::error("n8n.{$label}: invalid json", [
                'status'       => $status,
                'body_excerpt' => $this->excerpt($body),
            ]);
            return $this->fail(self::REASON_INVALID_JSON, $status);
        }

        if ($data === []) {
            Log::error("n8n.{$label}: empty json object", ['status' => $status]);
            return $this->fail(self::REASON_EMPTY, $status);
        }

        return [
            'ok'     => true,
            'reason' => null,
            'status' => $status,
            'data'   => $data,
        ];
    }

    private function fail(string $reason, ?int $status = null): array
    {
        return [
            'ok'     => false,
            'reason' => $reason,
            'status' => $status,
            'data'   => [],
        ];
    }

    private function excerpt(string $body, int $max = 500): string
    {
        $body = trim($body);
        if (mb_strlen($body) <= $max) {
            return $body;
        }
        return mb_substr($body, 0, $max) . '... [truncated]';
    }
}
