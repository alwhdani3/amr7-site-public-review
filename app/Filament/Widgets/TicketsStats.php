<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TicketsStats extends BaseWidget
{
    protected ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        // ✅ استعلام واحد يجمع الثلاث قيم
        $counts = Ticket::query()
            ->selectRaw("COUNT(*) as total_count")
            ->selectRaw("SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open_count")
            ->selectRaw("SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today_count")
            ->first();

        $total = (int) ($counts->total_count ?? 0);
        $open  = (int) ($counts->open_count ?? 0);
        $today = (int) ($counts->today_count ?? 0);

        // ✅ Charts ديناميكية: آخر 7 أيام
        $openChart  = $this->countByDay(Ticket::query()->where('status', 'open'), 'created_at', 7);
        $todayChart = $this->countByDay(Ticket::query(), 'created_at', 7);

        return [
            Stat::make('التذاكر المفتوحة', $open)
                ->description('تذاكر بانتظار الرد')
                ->descriptionIcon('heroicon-m-inbox-stack')
                ->color('danger')
                ->chart($openChart),

            Stat::make('تذاكر اليوم', $today)
                ->description('تم إنشاؤها اليوم')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('success')
                ->chart($todayChart),

            Stat::make('إجمالي التذاكر', $total)
                ->description('منذ بداية النظام')
                ->descriptionIcon('heroicon-m-ticket')
                ->color('primary'),
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
