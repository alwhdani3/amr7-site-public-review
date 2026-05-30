<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TicketsToday extends BaseWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        // ✅ استعلام واحد (مناسب MySQL)
        $counts = Ticket::query()
            ->selectRaw("SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today_count")
            ->selectRaw("SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open_count")
            ->first();

        $today = (int) ($counts->today_count ?? 0);
        $open  = (int) ($counts->open_count ?? 0);

        // ✅ Charts ديناميكية: آخر 7 أيام
        $todayChart = $this->countByDay(Ticket::query(), 'created_at', 7);
        $openChart  = $this->countByDay(Ticket::query()->where('status', 'open'), 'created_at', 7);

        return [
            Stat::make('طلبات اليوم', $today)
                ->description('تذاكر تم إنشاؤها اليوم')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->chart($todayChart)
                ->color('primary'),

            Stat::make('التذاكر المفتوحة', $open)
                ->description('تحتاج إلى متابعة')
                ->descriptionIcon('heroicon-m-inbox-stack')
                ->chart($openChart)
                ->color('warning'),
        ];
    }

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
