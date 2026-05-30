<?php

namespace App\Filament\Pages;

use App\Models\Customer;
use App\Models\Post;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\Ticket;
use App\Models\User;
use App\Services\CommandCenterSnapshotStore;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;

class CommandCenter extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-command-line';
    protected static string | \UnitEnum | null $navigationGroup = 'التشغيل الآلي والذكاء';
    protected static ?string $navigationLabel = 'مركز تحكم آمر سبعة';
    protected static ?string $title = 'مركز تحكم آمر سبعة / Amr 7 Command Center';
    protected static ?string $slug = 'command-center';
    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.command-center';

    public static function canAccess(): bool
    {
        return static::userCanViewCommandCenter(auth()->user());
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::userCanViewCommandCenter(auth()->user());
    }

    /**
     * Command Center surfaces aggregate operational metrics (lead counts,
     * service request volume, snapshot health). It's safe internal data
     * but it's still privileged read access — restrict to operators.
     */
    protected static function userCanViewCommandCenter(?User $user): bool
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

    /**
     * Sections rendered on the dashboard. Each section is composed of cards
     * that come from one of three sources:
     *   - Local DB count (Services, Tickets, Users, ...)            → Live (DB)
     *   - Local file (public/sitemap.xml)                           → Live (file)
     *   - JSON snapshot pushed by a trusted caller via internal API → Healthy/Warning/Critical/No data
     *
     * No live external HTTP calls are made at render time.
     */
    public function getSections(): array
    {
        $store = $this->snapshotStore();
        $snapshots = $store->listSnapshots();

        return [
            [
                'key' => 'operations',
                'title' => 'Operations Overview',
                'subtitle' => 'نظرة عامة على العمليات',
                'icon' => 'heroicon-o-squares-2x2',
                'cards' => $this->operationsCards(),
            ],
            [
                'key' => 'communication',
                'title' => 'Communication',
                'subtitle' => 'قنوات التواصل',
                'icon' => 'heroicon-o-chat-bubble-left-right',
                'cards' => $this->communicationCards($snapshots),
            ],
            [
                'key' => 'monitoring',
                'title' => 'Monitoring',
                'subtitle' => 'المراقبة والصحة',
                'icon' => 'heroicon-o-signal',
                'cards' => $this->monitoringCards($snapshots),
            ],
            [
                'key' => 'seo_indexing',
                'title' => 'SEO & Indexing',
                'subtitle' => 'الفهرسة و SEO',
                'icon' => 'heroicon-o-magnifying-glass',
                'cards' => $this->seoIndexingCards($snapshots),
            ],
            [
                'key' => 'security',
                'title' => 'Security',
                'subtitle' => 'الأمن والصلاحيات',
                'icon' => 'heroicon-o-shield-check',
                'cards' => $this->securityCards($snapshots),
            ],
            [
                'key' => 'backups',
                'title' => 'Backups',
                'subtitle' => 'النسخ الاحتياطية',
                'icon' => 'heroicon-o-archive-box',
                'cards' => $this->backupsCards($snapshots),
            ],
        ];
    }

    private function snapshotStore(): CommandCenterSnapshotStore
    {
        return app(CommandCenterSnapshotStore::class);
    }

    private function operationsCards(): array
    {
        return [
            $this->makeCard(
                key: 'services',
                title: 'Services',
                subtitle: 'الخدمات النشطة',
                icon: 'heroicon-o-rectangle-stack',
                accent: 'sky',
                description: 'إجمالي الخدمات المفعّلة على الموقع (is_active = true).',
                stat: $this->safeCount(Service::class, fn ($q) => $q->where('is_active', true)),
            ),
            $this->makeCard(
                key: 'service_requests',
                title: 'Service Requests',
                subtitle: 'طلبات الخدمات',
                icon: 'heroicon-o-clipboard-document-list',
                accent: 'indigo',
                description: 'إجمالي طلبات الخدمات المسجَّلة في قاعدة البيانات.',
                stat: $this->safeCount(ServiceRequest::class),
            ),
            $this->makeCard(
                key: 'tickets_open',
                title: 'Open Tickets',
                subtitle: 'التذاكر المفتوحة',
                icon: 'heroicon-o-ticket',
                accent: 'amber',
                description: 'التذاكر بحالة غير "closed".',
                stat: $this->safeCount(Ticket::class, fn ($q) => $q->where('status', '!=', 'closed')),
            ),
        ];
    }

    private function communicationCards(array $snapshots): array
    {
        return [
            $this->snapshotCard(
                snapshot: $snapshots['whatsapp'] ?? null,
                key: 'whatsapp_ai',
                title: 'WhatsApp AI',
                subtitle: 'مساعد واتساب الذكي',
                icon: 'heroicon-o-chat-bubble-left-right',
                accent: 'emerald',
                expectedFields: ['process_status', 'connected', 'last_seen_at', 'last_error'],
                valueResolver: fn (array $d) => isset($d['connected'])
                    ? ($d['connected'] ? 'Connected' : 'Disconnected')
                    : '—',
                descriptionResolver: function (array $d) {
                    $parts = [];
                    if (isset($d['process_status'])) {
                        $parts[] = 'Process: ' . (string) $d['process_status'];
                    }
                    if (! empty($d['last_seen_at'])) {
                        $parts[] = 'آخر ظهور: ' . $this->prettyTime($d['last_seen_at']);
                    }
                    if (! empty($d['last_error'])) {
                        $parts[] = 'آخر خطأ: ' . \Illuminate\Support\Str::limit((string) $d['last_error'], 60);
                    }
                    return implode(' — ', $parts) ?: 'حقول متوقَّعة: process_status / connected / last_seen_at / last_error.';
                },
                statusResolver: function (array $d) {
                    if (! isset($d['connected'])) {
                        return ['warning', 'Warning'];
                    }
                    if ($d['connected'] === false) {
                        return ['error', 'Critical'];
                    }
                    if (! empty($d['last_seen_at']) && $this->isOlderThanMinutes($d['last_seen_at'], 60)) {
                        return ['warning', 'Warning'];
                    }
                    return ['ok', 'Healthy'];
                },
            ),
        ];
    }

    private function monitoringCards(array $snapshots): array
    {
        return [
            $this->snapshotCard(
                snapshot: $snapshots['n8n'] ?? null,
                key: 'n8n',
                title: 'n8n Workflows',
                subtitle: 'سير العمل في n8n',
                icon: 'heroicon-o-bolt',
                accent: 'indigo',
                expectedFields: ['total_workflows', 'active_workflows', 'failed_last_24h', 'last_backup_at'],
                valueResolver: function (array $d) {
                    $total = $d['total_workflows'] ?? null;
                    $active = $d['active_workflows'] ?? null;
                    if ($total === null && $active === null) {
                        return '—';
                    }
                    return ($active ?? '?') . ' / ' . ($total ?? '?');
                },
                descriptionResolver: function (array $d) {
                    $parts = [];
                    if (isset($d['failed_last_24h'])) {
                        $parts[] = 'فشل آخر 24 ساعة: ' . (int) $d['failed_last_24h'];
                    }
                    if (! empty($d['last_backup_at'])) {
                        $parts[] = 'آخر backup: ' . $this->prettyTime($d['last_backup_at']);
                    }
                    return implode(' — ', $parts) ?: 'حقول متوقَّعة: total/active workflows + failed_last_24h + last_backup_at.';
                },
                statusResolver: function (array $d) {
                    $failed = (int) ($d['failed_last_24h'] ?? 0);
                    if ($failed === 0) return ['ok', 'Healthy'];
                    if ($failed <= 5) return ['warning', 'Warning'];
                    return ['error', 'Critical'];
                },
            ),
            $this->snapshotCard(
                snapshot: $snapshots['server'] ?? null,
                key: 'server_health',
                title: 'Server Health',
                subtitle: 'صحة الخادم',
                icon: 'heroicon-o-server-stack',
                accent: 'sky',
                expectedFields: ['disk_used_percent', 'memory_used_percent', 'load'],
                valueResolver: function (array $d) {
                    $disk = $d['disk_used_percent'] ?? null;
                    $mem = $d['memory_used_percent'] ?? null;
                    if ($disk === null && $mem === null) return '—';
                    return 'Disk ' . ($disk ?? '?') . '% / Mem ' . ($mem ?? '?') . '%';
                },
                descriptionResolver: function (array $d) {
                    $parts = [];
                    if (isset($d['load'])) {
                        $parts[] = 'Load: ' . (is_array($d['load']) ? implode(', ', $d['load']) : (string) $d['load']);
                    }
                    return implode(' — ', $parts) ?: 'حقول متوقَّعة: disk_used_percent / memory_used_percent / load.';
                },
                statusResolver: function (array $d) {
                    $max = max((float) ($d['disk_used_percent'] ?? 0), (float) ($d['memory_used_percent'] ?? 0));
                    if ($max <= 75) return ['ok', 'Healthy'];
                    if ($max <= 90) return ['warning', 'Warning'];
                    return ['error', 'Critical'];
                },
            ),
            $this->snapshotCard(
                snapshot: $snapshots['websites'] ?? null,
                key: 'website_health',
                title: 'Website Health',
                subtitle: 'صحة الموقع',
                icon: 'heroicon-o-globe-alt',
                accent: 'cyan',
                expectedFields: ['ok_count', 'down_count', 'last_check_at'],
                valueResolver: function (array $d) {
                    $ok = $d['ok_count'] ?? null;
                    $down = $d['down_count'] ?? null;
                    if ($ok === null && $down === null) return '—';
                    return ($ok ?? '?') . ' OK / ' . ($down ?? '?') . ' Down';
                },
                descriptionResolver: function (array $d) {
                    return ! empty($d['last_check_at'])
                        ? 'آخر فحص: ' . $this->prettyTime($d['last_check_at'])
                        : 'حقول متوقَّعة: ok_count / down_count / last_check_at.';
                },
                statusResolver: function (array $d) {
                    $down = (int) ($d['down_count'] ?? 0);
                    return $down === 0 ? ['ok', 'Healthy'] : ['error', 'Critical'];
                },
            ),
        ];
    }

    private function seoIndexingCards(array $snapshots): array
    {
        $sitemap = $this->sitemapStats();

        return [
            $this->makeCard(
                key: 'sitemap',
                title: 'Sitemap',
                subtitle: 'sitemap.xml',
                icon: 'heroicon-o-map',
                accent: 'violet',
                description: $sitemap['description'] ?? 'public/sitemap.xml',
                stat: [
                    'value' => $sitemap['value'],
                    'status' => $sitemap['status'],
                    'status_label' => $sitemap['status_label'],
                ],
            ),
            $this->makeCard(
                key: 'posts_published',
                title: 'Published Posts',
                subtitle: 'المقالات المنشورة',
                icon: 'heroicon-o-newspaper',
                accent: 'amber',
                description: 'المقالات بحالة is_published = true.',
                stat: $this->safeCount(Post::class, fn ($q) => $q->where('is_published', true)),
            ),
            $this->snapshotCard(
                snapshot: $snapshots['ssl'] ?? null,
                key: 'ssl_monitor',
                title: 'SSL Monitor',
                subtitle: 'مراقبة شهادات SSL',
                icon: 'heroicon-o-lock-closed',
                accent: 'rose',
                expectedFields: ['expiring_soon_count', 'nearest_expiry_domain'],
                valueResolver: fn (array $d) => isset($d['expiring_soon_count'])
                    ? ((int) $d['expiring_soon_count']) . ' soon'
                    : '—',
                descriptionResolver: function (array $d) {
                    return ! empty($d['nearest_expiry_domain'])
                        ? 'أقرب انتهاء: ' . (string) $d['nearest_expiry_domain']
                        : 'حقول متوقَّعة: expiring_soon_count / nearest_expiry_domain.';
                },
                statusResolver: function (array $d) {
                    $count = (int) ($d['expiring_soon_count'] ?? 0);
                    if ($count === 0) return ['ok', 'Healthy'];
                    if ($count <= 2) return ['warning', 'Warning'];
                    return ['error', 'Critical'];
                },
            ),
        ];
    }

    private function securityCards(array $snapshots): array
    {
        return [
            $this->makeCard(
                key: 'users_active',
                title: 'Active Users',
                subtitle: 'المستخدمون المفعّلون',
                icon: 'heroicon-o-users',
                accent: 'sky',
                description: 'المستخدمون بحالة is_active = true.',
                stat: $this->safeCount(User::class, fn ($q) => $q->where('is_active', true)),
            ),
            $this->makeCard(
                key: 'customers_active',
                title: 'Active Customers',
                subtitle: 'العملاء المفعّلون',
                icon: 'heroicon-o-identification',
                accent: 'emerald',
                description: 'العملاء النشطون في قاعدة البيانات.',
                stat: $this->safeCount(Customer::class, fn ($q) => $q->where('is_active', true)),
            ),
            $this->snapshotCard(
                snapshot: $snapshots['security'] ?? null,
                key: 'security_alerts',
                title: 'Security Alerts',
                subtitle: 'تنبيهات الأمن',
                icon: 'heroicon-o-shield-exclamation',
                accent: 'red',
                expectedFields: ['protected_webhooks', 'open_webhooks', 'last_audit_at'],
                valueResolver: function (array $d) {
                    $open = $d['open_webhooks'] ?? null;
                    $protected = $d['protected_webhooks'] ?? null;
                    if ($open === null && $protected === null) return '—';
                    return 'Open ' . ($open ?? '?') . ' / Protected ' . ($protected ?? '?');
                },
                descriptionResolver: function (array $d) {
                    return ! empty($d['last_audit_at'])
                        ? 'آخر تدقيق: ' . $this->prettyTime($d['last_audit_at'])
                        : 'حقول متوقَّعة: protected_webhooks / open_webhooks / last_audit_at.';
                },
                statusResolver: function (array $d) {
                    $open = (int) ($d['open_webhooks'] ?? 0);
                    if ($open === 0) return ['ok', 'Healthy'];
                    if ($open <= 2) return ['warning', 'Warning'];
                    return ['error', 'Critical'];
                },
            ),
        ];
    }

    private function backupsCards(array $snapshots): array
    {
        return [
            $this->snapshotCard(
                snapshot: $snapshots['backups'] ?? null,
                key: 'backups_status',
                title: 'Backups',
                subtitle: 'النسخ الاحتياطية',
                icon: 'heroicon-o-archive-box',
                accent: 'violet',
                expectedFields: ['last_backup_at', 'last_backup_size'],
                valueResolver: function (array $d) {
                    if (! empty($d['last_backup_at'])) {
                        return $this->prettyTime($d['last_backup_at']);
                    }
                    return '—';
                },
                descriptionResolver: function (array $d) {
                    return ! empty($d['last_backup_size'])
                        ? 'الحجم: ' . (string) $d['last_backup_size']
                        : 'حقول متوقَّعة: last_backup_at / last_backup_size.';
                },
                statusResolver: function (array $d) {
                    if (empty($d['last_backup_at'])) return ['warning', 'Warning'];
                    if ($this->isOlderThanMinutes($d['last_backup_at'], 60 * 48)) return ['error', 'Critical'];
                    if ($this->isOlderThanMinutes($d['last_backup_at'], 60 * 24)) return ['warning', 'Warning'];
                    return ['ok', 'Healthy'];
                },
            ),
        ];
    }

    /**
     * Standard card builder used for DB / file backed cards.
     *
     * @param  array{value: string, status: string, status_label: string}  $stat
     */
    private function makeCard(
        string $key,
        string $title,
        string $subtitle,
        string $icon,
        string $accent,
        string $description,
        array $stat,
    ): array {
        return [
            'key' => $key,
            'title' => $title,
            'subtitle' => $subtitle,
            'icon' => $icon,
            'accent' => $accent,
            'description' => $description,
            'value' => $stat['value'] ?? '—',
            'status' => $stat['status'] ?? 'pending',
            'status_label' => $stat['status_label'] ?? 'لم يُربط بعد',
            'received_at' => null,
        ];
    }

    /**
     * Card backed by a JSON snapshot pushed via the internal API.
     * If the snapshot is missing, the card shows "No data yet" (لم تصل بيانات بعد).
     */
    private function snapshotCard(
        ?array $snapshot,
        string $key,
        string $title,
        string $subtitle,
        string $icon,
        string $accent,
        array $expectedFields,
        \Closure $valueResolver,
        \Closure $descriptionResolver,
        \Closure $statusResolver,
    ): array {
        if (! is_array($snapshot) || ! isset($snapshot['data']) || ! is_array($snapshot['data'])) {
            return [
                'key' => $key,
                'title' => $title,
                'subtitle' => $subtitle,
                'icon' => $icon,
                'accent' => $accent,
                'description' => 'لم تصل بيانات بعد من المصدر. الحقول المتوقَّعة: ' . implode('، ', $expectedFields) . '.',
                'value' => '—',
                'status' => 'pending',
                'status_label' => 'No data',
                'received_at' => null,
            ];
        }

        $data = $snapshot['data'];

        try {
            [$statusCode, $statusLabel] = $statusResolver($data);
            $value = (string) $valueResolver($data);
            $description = (string) $descriptionResolver($data);
        } catch (\Throwable $e) {
            return [
                'key' => $key,
                'title' => $title,
                'subtitle' => $subtitle,
                'icon' => $icon,
                'accent' => $accent,
                'description' => 'تعذّر تفسير بيانات المصدر — تحقّق من شكل الـ JSON.',
                'value' => '—',
                'status' => 'warning',
                'status_label' => 'Warning',
                'received_at' => $snapshot['received_at'] ?? null,
            ];
        }

        return [
            'key' => $key,
            'title' => $title,
            'subtitle' => $subtitle,
            'icon' => $icon,
            'accent' => $accent,
            'description' => $description,
            'value' => $value,
            'status' => $statusCode,
            'status_label' => $statusLabel,
            'received_at' => $snapshot['received_at'] ?? null,
        ];
    }

    /**
     * @return array{value: string, status: string, status_label: string}
     */
    private function safeCount(string $modelClass, ?\Closure $constraint = null): array
    {
        try {
            if (! class_exists($modelClass)) {
                return ['value' => '—', 'status' => 'warning', 'status_label' => 'Model not found'];
            }

            /** @var \Illuminate\Database\Eloquent\Builder $query */
            $query = $modelClass::query();

            if ($constraint) {
                $constraint($query);
            }

            $count = $query->count();

            return [
                'value' => number_format($count),
                'status' => 'ok',
                'status_label' => 'Live (DB)',
            ];
        } catch (\Throwable $e) {
            return [
                'value' => '—',
                'status' => 'warning',
                'status_label' => 'Query failed',
            ];
        }
    }

    /**
     * @return array{value: string, status: string, status_label: string, description: ?string}
     */
    private function sitemapStats(): array
    {
        $path = public_path('sitemap.xml');

        if (! is_file($path)) {
            return [
                'value' => '—',
                'status' => 'warning',
                'status_label' => 'File missing',
                'description' => 'الملف public/sitemap.xml غير موجود — شغّل php artisan sitemap:generate.',
            ];
        }

        try {
            $size = @filesize($path);
            $mtime = @filemtime($path);
            $content = @file_get_contents($path);

            if ($content === false) {
                return [
                    'value' => '—',
                    'status' => 'warning',
                    'status_label' => 'Read failed',
                    'description' => 'تعذّر قراءة sitemap.xml من القرص.',
                ];
            }

            $urls = substr_count($content, '<loc>');
            $sizeLabel = $size !== false ? $this->formatBytes($size) : '—';
            $mtimeLabel = $mtime !== false
                ? Carbon::createFromTimestamp($mtime)->diffForHumans()
                : '—';

            return [
                'value' => number_format($urls) . ' URLs',
                'status' => 'ok',
                'status_label' => 'Live (file)',
                'description' => 'الحجم: ' . $sizeLabel . ' — آخر تحديث: ' . $mtimeLabel,
            ];
        } catch (\Throwable $e) {
            return [
                'value' => '—',
                'status' => 'warning',
                'status_label' => 'Read failed',
                'description' => 'تعذّر قياس sitemap.xml.',
            ];
        }
    }

    private function prettyTime(mixed $iso): string
    {
        if (! is_string($iso) && ! is_int($iso)) {
            return '—';
        }
        try {
            return Carbon::parse($iso)->diffForHumans();
        } catch (\Throwable $e) {
            return '—';
        }
    }

    private function isOlderThanMinutes(mixed $iso, int $minutes): bool
    {
        if (! is_string($iso) && ! is_int($iso)) {
            return true;
        }
        try {
            return Carbon::parse($iso)->lt(now()->subMinutes($minutes));
        } catch (\Throwable $e) {
            return true;
        }
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }
        if ($bytes < 1048576) {
            return number_format($bytes / 1024, 1) . ' KB';
        }
        return number_format($bytes / 1048576, 1) . ' MB';
    }
}
