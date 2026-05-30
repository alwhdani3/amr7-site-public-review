<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema as DbSchema;

/**
 * Phase 9C-5 — Stats overview for operational health.
 *
 * كل عداد محروس بـSchema::hasTable. إذا الجدول غير موجود يعرض 0.
 * لا notifications، لا jobs، لا API.
 */
class OperationsOverviewStatsWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    protected ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        return [
            Stat::make('اشتراكات قريبة الانتهاء', $this->countExpiringSubscriptions())
                ->description('خلال الـ30 يومًا القادمة')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('التزامات متأخرة', $this->countLateObligationPeriods())
                ->description('تجاوزت تاريخ الاستحقاق')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),

            Stat::make('إقرارات بانتظار المراجعة', $this->countTaxReturnsAwaitingReview())
                ->description('files_pending / under_review / client_approval')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),

            Stat::make('فترات مفتوحة', $this->countOpenObligationPeriods())
                ->description('قيد التشغيل حالياً')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('success'),
        ];
    }

    private function countExpiringSubscriptions(): int
    {
        if (! DbSchema::hasTable('subscriptions')) {
            return 0;
        }

        $query = DB::table('subscriptions')
            ->whereBetween('expires_at', [now(), now()->addDays(30)]);

        if (DbSchema::hasColumn('subscriptions', 'status')) {
            $query->where('status', 'active');
        }

        return (int) $query->count();
    }

    private function countLateObligationPeriods(): int
    {
        if (! DbSchema::hasTable('obligation_periods')) {
            return 0;
        }

        return (int) DB::table('obligation_periods')
            ->where(function ($q) {
                $q->where('status', 'late')
                  ->orWhere(function ($q2) {
                      $q2->whereIn('status', [
                          'open', 'client_uploading', 'files_uploaded', 'under_review', 'client_approval',
                      ])->where('due_date', '<', now()->toDateString());
                  });
            })
            ->count();
    }

    private function countTaxReturnsAwaitingReview(): int
    {
        if (! DbSchema::hasTable('tax_return_requests')) {
            return 0;
        }

        return (int) DB::table('tax_return_requests')
            ->whereIn('status', ['files_pending', 'under_review', 'client_approval'])
            ->count();
    }

    private function countOpenObligationPeriods(): int
    {
        if (! DbSchema::hasTable('obligation_periods')) {
            return 0;
        }

        return (int) DB::table('obligation_periods')
            ->whereIn('status', [
                'open', 'client_uploading', 'files_uploaded', 'under_review', 'client_approval',
            ])
            ->count();
    }
}
