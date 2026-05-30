<?php

namespace App\Services\N8n;

use App\Models\N8nWorkflowRun;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class N8nClient
{
    public function isEnabled(): bool
    {
        return (bool) config('n8n.enabled') && filled(config('n8n.base_url'));
    }

    /**
     * Three-state readiness for the global integration (independent of any
     * single workflow): disabled / not_configured / healthy. Mirrors the
     * states surfaced by healthCheck() so callers can branch without
     * re-deriving them.
     */
    public function readinessStatus(): string
    {
        if (! (bool) config('n8n.enabled')) {
            return 'disabled';
        }

        if (! filled(config('n8n.base_url'))) {
            return 'not_configured';
        }

        return 'healthy';
    }

    /**
     * Returns null when the workflow is enabled and ready to fire, otherwise
     * an Arabic reason string explaining why it is locked. Used by callers
     * (and the UI) that want a single source of truth for gating without
     * actually triggering anything.
     */
    public function validateWorkflowEnabled(string $key): ?string
    {
        $workflow = $this->workflows()[$key] ?? null;

        if (! $workflow) {
            return 'Workflow غير معروف.';
        }

        if (! $this->isEnabled()) {
            return 'n8n غير مفعّل أو N8N_BASE_URL غير مضبوط.';
        }

        if (! ($workflow['enabled'] ?? false)) {
            return $workflow['disabled_reason'] ?? 'هذا workflow غير مفعّل من الإعدادات.';
        }

        if (! $this->endpointFor($workflow)) {
            return 'مسار webhook غير مضبوط لهذا workflow.';
        }

        return null;
    }

    public function healthCheck(): array
    {
        $workflows = $this->workflows();
        $enabledWorkflows = collect($workflows)->where('enabled', true)->count();

        if (! (bool) config('n8n.enabled')) {
            return [
                'status' => 'disabled',
                'label' => 'غير مفعّل',
                'message' => 'N8N_ENABLED غير مفعّل. لن يتم إرسال أي طلبات.',
                'enabled_workflows' => $enabledWorkflows,
                'total_workflows' => count($workflows),
            ];
        }

        if (! filled(config('n8n.base_url'))) {
            return [
                'status' => 'not_configured',
                'label' => 'غير مهيّأ',
                'message' => 'N8N_ENABLED=true لكن N8N_BASE_URL غير مضبوط. عيّنه في .env ثم نفّذ php artisan config:clear.',
                'enabled_workflows' => $enabledWorkflows,
                'total_workflows' => count($workflows),
            ];
        }

        return [
            'status' => 'healthy',
            'label' => 'جاهز',
            'message' => 'الإعدادات الأساسية جاهزة. اضغط اختبار اتصال n8n لاستدعاء /healthz فقط.',
            'enabled_workflows' => $enabledWorkflows,
            'total_workflows' => count($workflows),
        ];
    }

    public function probe(): array
    {
        $baseUrl = rtrim((string) config('n8n.base_url'), '/');
        $configured = (bool) config('n8n.enabled') && filled($baseUrl);

        if (! $configured) {
            return [
                'configured' => false,
                'reachable' => false,
                'http_status' => null,
                'duration_ms' => null,
                'message' => ! (bool) config('n8n.enabled')
                    ? 'n8n غير مفعّل. لم يتم إرسال أي طلب.'
                    : 'N8N_BASE_URL غير مضبوط. لم يتم إرسال أي طلب.',
            ];
        }

        $started = microtime(true);

        try {
            $response = Http::timeout(5)->acceptJson()->get($baseUrl . '/healthz');
            $durationMs = (int) round((microtime(true) - $started) * 1000);
            $status = $response->status();

            return [
                'configured' => true,
                'reachable' => $response->successful(),
                'http_status' => $status,
                'duration_ms' => $durationMs,
                'message' => $response->successful()
                    ? 'تم الوصول إلى n8n sandbox عبر /healthz.'
                    : 'تم الوصول إلى n8n sandbox لكن /healthz أعاد HTTP ' . $status . '. هذا لا يعني تشغيل workflow.',
            ];
        } catch (ConnectionException) {
            return [
                'configured' => true,
                'reachable' => false,
                'http_status' => null,
                'duration_ms' => (int) round((microtime(true) - $started) * 1000),
                'message' => 'تعذر الوصول إلى n8n sandbox خلال مهلة 5 ثوانٍ.',
            ];
        } catch (\Throwable $e) {
            report($e);

            return [
                'configured' => true,
                'reachable' => false,
                'http_status' => null,
                'duration_ms' => (int) round((microtime(true) - $started) * 1000),
                'message' => 'فشل غير متوقع أثناء اختبار اتصال n8n sandbox.',
            ];
        }
    }

    public function workflows(): array
    {
        return config('n8n.workflows', []);
    }

    /**
     * Alias of triggerWorkflow() — exists so callers can use the more
     * intuitive verb. Behavior is identical: gated by N8N_ENABLED, the
     * per-workflow `enabled` flag, base_url and webhook_path being set,
     * and (when configured) the workflow's allowed_domains list. Payload
     * is always sanitized before transit and before being persisted to
     * n8n_workflow_runs.
     */
    public function runWorkflow(string $key, array $payload = []): array
    {
        return $this->triggerWorkflow($key, $payload);
    }

    public function triggerWorkflow(string $key, array $payload = []): array
    {
        $workflow = $this->workflows()[$key] ?? null;

        if (! $workflow) {
            return $this->result('failed', 'Workflow غير معروف.', [], null, $key);
        }

        $correlationId = (string) Str::uuid();

        $payload = $this->sanitizePayload(array_merge([
            'workflow_key' => $key,
            'requested_by' => auth()->id(),
            'language' => 'ar',
            'correlation_id' => $correlationId,
        ], $payload));

        if (! $this->isEnabled()) {
            return $this->result('disabled', 'n8n غير مفعّل أو N8N_BASE_URL غير مضبوط.', [], null, $key, $workflow, $payload, null, null, $correlationId);
        }

        if (! ($workflow['enabled'] ?? false)) {
            return $this->result('disabled', 'هذا workflow غير مفعّل من الإعدادات.', [], null, $key, $workflow, $payload, null, null, $correlationId);
        }

        $endpoint = $this->endpointFor($workflow);
        if (! $endpoint) {
            return $this->result('failed', 'مسار webhook غير مضبوط لهذا workflow.', [], null, $key, $workflow, $payload, null, null, $correlationId);
        }

        if (! empty($workflow['validation'])) {
            $validator = Validator::make($payload, (array) $workflow['validation']);
            if ($validator->fails()) {
                return $this->result('failed', 'فشل التحقق من صحة المدخلات: ' . $validator->errors()->first(), [], null, $key, $workflow, $payload, null, 'validation_failed', $correlationId);
            }
        }

        if (! empty($workflow['allowed_domains']) && array_key_exists('domain', $payload)) {
            $domainCheck = $this->checkDomainAllowed((string) $payload['domain'], (array) $workflow['allowed_domains']);
            if ($domainCheck !== null) {
                return $this->result('failed', $domainCheck, [], null, $key, $workflow, $payload, null, 'domain_not_allowed', $correlationId);
            }
        }

        $started = microtime(true);

        try {
            $timeout = (int) ($workflow['timeout'] ?? config('n8n.timeout', 15));

            $request = Http::timeout($timeout)
                ->acceptJson()
                ->asJson()
                ->withHeaders(['X-AMR7-Correlation-Id' => $correlationId]);

            $retry = $workflow['retry'] ?? null;
            if (is_array($retry) && (int) ($retry['times'] ?? 0) > 0) {
                // times = number of *retries*, so attempts = times + 1.
                // throw:false keeps the existing non-throwing contract — we
                // still branch on $response->successful() below.
                $request = $request->retry((int) $retry['times'] + 1, (int) ($retry['backoff_ms'] ?? 0), throw: false);
            }

            if (filled(config('n8n.webhook_secret'))) {
                // Header name is configurable so we can match whatever the n8n
                // webhook's Header Auth credential expects (e.g. X-AMR7-N8N-KEY
                // on the SEO workflows). The secret value is sent as a header
                // only — never logged or persisted.
                $secretHeader = (string) (config('n8n.webhook_secret_header') ?: 'X-N8N-Webhook-Secret');
                $request = $request->withHeaders([
                    $secretHeader => (string) config('n8n.webhook_secret'),
                ]);
            }

            $response = $request->post($endpoint, $payload);
            $durationMs = (int) round((microtime(true) - $started) * 1000);
            $data = $this->sanitizePayload($response->json() ?? ['body' => Str::limit($response->body(), 500)]);

            return $this->result(
                status: $response->successful() ? 'success' : 'failed',
                message: $response->successful() ? 'تم تشغيل workflow بنجاح.' : 'فشل تشغيل workflow. HTTP ' . $response->status(),
                data: $data,
                durationMs: $durationMs,
                key: $key,
                workflow: $workflow,
                requestPayload: $payload,
                responsePayload: $data,
                errorMessage: $response->successful() ? null : 'HTTP ' . $response->status(),
                correlationId: $correlationId,
            );
        } catch (ConnectionException) {
            return $this->result('failed', 'تعذر الاتصال بـ n8n.', [], null, $key, $workflow, $payload, null, 'Connection error', $correlationId);
        } catch (\Throwable $e) {
            report($e);

            return $this->result('failed', 'فشل غير متوقع أثناء تشغيل workflow.', [], null, $key, $workflow, $payload, null, 'Unexpected error', $correlationId);
        }
    }

    public function recentRuns(int $limit = 12): array
    {
        if (! Schema::hasTable('n8n_workflow_runs')) {
            return [];
        }

        return N8nWorkflowRun::query()
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (N8nWorkflowRun $run) => $run->toArray())
            ->all();
    }

    public function statsFor(string $workflowKey): array
    {
        if (! Schema::hasTable('n8n_workflow_runs')) {
            return ['last_run' => null, 'successes' => 0, 'failures' => 0];
        }

        $query = N8nWorkflowRun::query()->where('workflow_key', $workflowKey);

        return [
            'last_run' => (clone $query)->latest()->first(),
            'successes' => (clone $query)->where('status', 'success')->count(),
            'failures' => (clone $query)->where('status', 'failed')->count(),
        ];
    }

    public function failuresLast24Hours(): int
    {
        if (! Schema::hasTable('n8n_workflow_runs')) {
            return 0;
        }

        return N8nWorkflowRun::query()
            ->where('status', 'failed')
            ->where('created_at', '>=', now()->subDay())
            ->count();
    }

    public function lastSuccessfulRun(): ?N8nWorkflowRun
    {
        if (! Schema::hasTable('n8n_workflow_runs')) {
            return null;
        }

        return N8nWorkflowRun::query()
            ->where('status', 'success')
            ->latest()
            ->first();
    }

    private function endpointFor(array $workflow): ?string
    {
        $baseUrl = rtrim((string) config('n8n.base_url'), '/');
        $prefix = trim((string) config('n8n.webhook_prefix', 'webhook'), '/');
        $path = trim((string) ($workflow['webhook_path'] ?? ''), '/');

        if ($baseUrl === '' || $path === '') {
            return null;
        }

        // n8n serves production webhooks at base_url/<prefix>/<path>. healthz is
        // untouched (probe() builds base_url/healthz directly). Trim slashes so
        // we never produce a double slash.
        return $prefix === ''
            ? $baseUrl . '/' . $path
            : $baseUrl . '/' . $prefix . '/' . $path;
    }

    private function checkDomainAllowed(string $rawDomain, array $allowed): ?string
    {
        $domain = trim($rawDomain);
        if ($domain === '') {
            return 'الدومين مطلوب لهذا workflow.';
        }

        $domain = preg_replace('#^https?://#i', '', $domain) ?? '';
        $host = parse_url('http://' . $domain, PHP_URL_HOST) ?: $domain;
        $host = strtolower(rtrim((string) $host, '/'));

        if ($host === '' || in_array($host, ['localhost', '0.0.0.0'], true)) {
            return 'الدومين غير مسموح (داخلي أو فارغ).';
        }

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return 'الدومين غير مسموح (IP غير مسموح به).';
        }

        if (! in_array($host, array_map('strtolower', $allowed), true)) {
            return 'الدومين غير مسموح. مسموح فقط: ' . implode(', ', $allowed);
        }

        return null;
    }

    private function result(
        string $status,
        string $message,
        array $data = [],
        ?int $durationMs = null,
        ?string $key = null,
        ?array $workflow = null,
        ?array $requestPayload = null,
        ?array $responsePayload = null,
        ?string $errorMessage = null,
        ?string $correlationId = null,
    ): array {
        $run = null;

        if ($key && $workflow && Schema::hasTable('n8n_workflow_runs')) {
            $attributes = [
                'workflow_key' => $key,
                'workflow_name' => $workflow['name_ar'] ?? $key,
                'category' => $workflow['category'] ?? null,
                'status' => $status,
                'request_payload' => $requestPayload ? $this->sanitizePayload($requestPayload) : null,
                'response_payload' => $responsePayload ? $this->sanitizePayload($responsePayload) : null,
                'error_message' => $errorMessage ? $this->sanitizeError($errorMessage) : null,
                'triggered_by' => auth()->id(),
                'duration_ms' => $durationMs,
            ];

            // correlation_id is added by an additive migration; guard so the
            // audit write still works on a DB where it has not run yet.
            if ($correlationId && Schema::hasColumn('n8n_workflow_runs', 'correlation_id')) {
                $attributes['correlation_id'] = $correlationId;
            }

            $run = N8nWorkflowRun::create($attributes);
        }

        return [
            'status' => $status,
            'message' => $message,
            'data' => $data,
            'duration_ms' => $durationMs,
            'run_id' => $run?->id,
            'correlation_id' => $correlationId,
        ];
    }

    /**
     * Public, log-safe view of a payload. Same redaction rules as the
     * internal sanitizer; exposed so callers that log/inspect a payload
     * outside triggerWorkflow() never touch raw secrets.
     */
    public function sanitizePayloadForLogs(array $payload): array
    {
        return $this->sanitizePayload($payload);
    }

    /**
     * Strip anything that resembles a secret or a credentialed URL from an
     * error string before it is persisted/logged, and cap its length. Never
     * leaks Bearer tokens, API keys, or webhook secrets.
     */
    public function sanitizeError(string|\Throwable|null $error, string $fallback = 'حدث خطأ غير متوقع.'): string
    {
        if ($error === null) {
            return $fallback;
        }

        $message = $error instanceof \Throwable ? $error->getMessage() : $error;
        $message = preg_replace('/\b(bearer|token|secret|api[_-]?key|apikey|authorization|password)\b\s*[:=]?\s*\S+/i', '$1 [redacted]', $message) ?? $message;
        $message = preg_replace('#https?://\S+#i', '[url]', $message) ?? $message;
        $message = trim((string) $message);

        return $message === '' ? $fallback : Str::limit($message, 200);
    }

    private function sanitizePayload(array $payload): array
    {
        $blocked = [
            'token',
            'secret',
            'password',
            'api_key',
            'apikey',
            'authorization',
            'cookie',
            'webhook_secret',
            'client_secret',
            'private_key',
        ];

        $clean = [];

        foreach ($payload as $key => $value) {
            $normalizedKey = Str::lower((string) $key);

            if (collect($blocked)->contains(fn (string $blockedKey) => str_contains($normalizedKey, $blockedKey))) {
                $clean[$key] = '[redacted]';
                continue;
            }

            if (is_array($value)) {
                $clean[$key] = $this->sanitizePayload($value);
                continue;
            }

            $clean[$key] = is_string($value) ? Str::limit($value, 500) : $value;
        }

        return $clean;
    }
}
