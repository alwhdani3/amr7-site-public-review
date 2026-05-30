<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TicketsOverview extends BaseWidget
{
    protected ?string $pollingInterval = '10s';

    protected function getStats(): array
    {
        // ✅ استعلام واحد بدل 3
        $counts = Ticket::query()
            ->selectRaw("SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open_count")
            ->selectRaw("SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_count")
            ->selectRaw("SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed_count")
            ->first();

        $open = (int) ($counts->open_count ?? 0);
        $inProgress = (int) ($counts->in_progress_count ?? 0);
        $closed = (int) ($counts->closed_count ?? 0);

        // ✅ Charts ديناميكية لآخر 7 أيام
        $openChart = $this->countByDay(Ticket::query()->where('status', 'open'), 'created_at', 7);
        $inProgressChart = $this->countByDay(Ticket::query()->where('status', 'in_progress'), 'created_at', 7);
        $closedChart = $this->countByDay(Ticket::query()->where('status', 'closed'), 'created_at', 7);

        return [
            Stat::make('تذاكر مفتوحة', $open)
                ->description('بانتظار الرد')
                ->descriptionIcon('heroicon-m-envelope-open')
                ->color('danger')
                ->chart($openChart),

            Stat::make('قيد المعالجة', $inProgress)
                ->description('جار العمل عليها')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('primary')
                ->chart($inProgressChart),

            Stat::make('مغلقة', $closed)
                ->description('تم الانتهاء منها')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart($closedChart),
        ];
    }

    /**
     * يرجّع أرقام آخر N أيام (من الأقدم للأحدث) لاستخدامها في chart()
     */
    private function countByDay($query, string $dateColumn, int $days = 7): array
    {
        $start = now()->startOfDay()->subDays($days - 1);
        $end   = now()->endOfDay();

        $rows = (clone $query)
            ->whereBetween($dateColumn, [$start, $end])
            ->selectRaw('DATE(' . $dateColumn . ') as d, COUNT(*) as c')
            ->groupBy('d')
            ->orderBy('d')
            ->pluck('c', 'd')
            ->toArray();

        $series = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $day = now()->subDays($i)->toDateString();
            $series[] = (int) ($rows[$day] ?? 0);
        }

        return $series;
    }
}
