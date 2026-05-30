<?php

namespace App\Filament\Pages;

use App\Services\N8n\N8nClient;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class N8nOperationsCenter extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-bolt';
    protected static string | \UnitEnum | null $navigationGroup = 'التشغيل الآلي والذكاء';
    protected static ?string $navigationLabel = 'مركز التشغيل الآلي';
    protected static ?string $title = 'مركز التشغيل الآلي';
    protected static ?string $slug = 'n8n-operations-center';
    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.n8n-operations-center';

    public static function canAccess(): bool
    {
        return static::userCanOperateN8n(auth()->user());
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::userCanOperateN8n(auth()->user());
    }

    /**
     * Restrict n8n Operations Center to operators only.
     *
     * Backoffice panel auth already excludes customers, but support /
     * employee / accountant should not be able to fire workflows that may
     * cost external API quota. Allow admin / super_admin (via the
     * is_admin accessor that covers Spatie + legacy) plus manager.
     */
    protected static function userCanOperateN8n(?\App\Models\User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->is_admin) {
            return true;
        }

        if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['manager'])) {
            return true;
        }

        return strtolower((string) ($user->role ?? '')) === 'manager';
    }

    public function health(): array
    {
        return $this->client()->healthCheck();
    }

    public function groupedWorkflows(): array
    {
        return collect($this->client()->workflows())
            ->groupBy('category')
            ->map(fn ($items) => $items->values()->all())
            ->all();
    }

    public function categoryLabel(string $category): string
    {
        return match ($category) {
            'contracts' => 'العقود',
            'content' => 'المحتوى',
            'tickets' => 'التذاكر',
            'documents' => 'الوثائق',
            'seo' => 'تحسين المحركات (SEO)',
            'sales' => 'العروض والمبيعات',
            default => 'عام',
        };
    }

    public function statusLabel(string $status): string
    {
        return match ($status) {
            'success', 'healthy' => 'ناجح',
            'failed' => 'فشل',
            'running' => 'قيد التشغيل',
            'disabled' => 'غير مفعّل',
            'not_configured' => 'غير مهيّأ',
            default => $status,
        };
    }

    public function workflowStats(string $workflowKey): array
    {
        return $this->client()->statsFor($workflowKey);
    }

    public function recentRuns(): array
    {
        return $this->client()->recentRuns();
    }

    public function failuresLast24Hours(): int
    {
        return $this->client()->failuresLast24Hours();
    }

    public function lastSuccessfulRun(): ?\App\Models\N8nWorkflowRun
    {
        return $this->client()->lastSuccessfulRun();
    }

    public function isWorkflowRunnable(string $workflowKey): bool
    {
        $workflow = $this->client()->workflows()[$workflowKey] ?? null;

        if (! $workflow) {
            return false;
        }

        // A file-based workflow cannot be safely demoed: there is no
        // sanctioned sample file, and we never feed real customer documents
        // into a demo run.
        if (($workflow['requires_file'] ?? false) === true) {
            return false;
        }

        return $this->client()->isEnabled()
            && (bool) ($workflow['enabled'] ?? false)
            && filled($workflow['webhook_path'] ?? null);
    }

    public function probeSandbox(): void
    {
        $result = $this->client()->probe();

        $status = $result['http_status'] ?? null;
        $duration = $result['duration_ms'] ?? null;

        $notification = Notification::make()
            ->title('اختبار اتصال n8n')
            ->body(sprintf(
                '%s%s%s',
                $result['message'] ?? 'انتهى الاختبار.',
                $status ? ' HTTP ' . $status . '.' : '',
                $duration ? ' المدة: ' . $duration . 'ms.' : '',
            ));

        if (($result['reachable'] ?? false) === true) {
            $notification->success();
        } elseif (($result['configured'] ?? false) === true) {
            $notification->warning();
        } else {
            $notification->danger();
        }

        $notification->send();
    }

    public function trigger(string $workflowKey): void
    {
        $result = $this->client()->triggerWorkflow($workflowKey, $this->samplePayload($workflowKey));

        $notification = Notification::make()
            ->title($this->statusLabel($result['status']) . ' - n8n')
            ->body($result['message']);

        match ($result['status']) {
            'success' => $notification->success(),
            'disabled' => $notification->warning(),
            default => $notification->danger(),
        };

        $notification->send();
    }

    private function samplePayload(string $workflowKey): array
    {
        // Prefer the curated, secret-free sample payload declared in
        // config/n8n.php. Fall back to the inline defaults below only if a
        // workflow has no sample_payload defined.
        $workflow = $this->client()->workflows()[$workflowKey] ?? null;
        if (is_array($workflow['sample_payload'] ?? null)) {
            return array_merge($workflow['sample_payload'], [
                'requested_by' => auth()->id(),
                'language' => 'ar',
            ]);
        }

        if (str_starts_with($workflowKey, 'contracts.')) {
            return [
                'contract_type' => 'general',
                'company_id' => null,
                'requested_by' => auth()->id(),
                'language' => 'ar',
                'notes' => null,
            ];
        }

        if (str_starts_with($workflowKey, 'content.')) {
            return [
                'title' => null,
                'platform' => null,
                'tone' => null,
                'company_id' => null,
                'requested_by' => auth()->id(),
                'language' => 'ar',
                'brief' => null,
            ];
        }

        if ($workflowKey === 'documents.extract') {
            return [
                'document_id' => null,
                'company_id' => null,
                'requested_by' => auth()->id(),
            ];
        }

        if ($workflowKey === 'tickets.summarize') {
            return [
                'ticket_id' => null,
                'requested_by' => auth()->id(),
            ];
        }

        if ($workflowKey === 'seo.sitemap_robots' || $workflowKey === 'seo.broken_links') {
            return [
                'domain' => 'amr-7.sa',
                'requested_by' => auth()->id(),
                'language' => 'ar',
            ];
        }

        if (str_starts_with($workflowKey, 'seo.')) {
            return [
                'topic' => null,
                'page_url' => null,
                'requested_by' => auth()->id(),
                'language' => 'ar',
            ];
        }

        if (str_starts_with($workflowKey, 'sales.')) {
            return [
                'service_id' => null,
                'company_id' => null,
                'requested_by' => auth()->id(),
                'language' => 'ar',
            ];
        }

        if ($workflowKey === 'documents.service_requirements') {
            return [
                'service_id' => null,
                'requested_by' => auth()->id(),
                'language' => 'ar',
            ];
        }

        return [
            'requested_by' => auth()->id(),
            'language' => 'ar',
        ];
    }

    private function client(): N8nClient
    {
        return app(N8nClient::class);
    }
}
