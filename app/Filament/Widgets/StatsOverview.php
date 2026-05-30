<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\ServiceRequest;
use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    // ✅ صحيح في Filament v5
    protected ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        // 1) عدّادات ServiceRequest (استعلام واحد)
        $serviceCounts = ServiceRequest::query()
            ->selectRaw("COUNT(*) as total")
            ->selectRaw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending")
            ->first();

        $totalRequests = (int) ($serviceCounts->total ?? 0);
        $pendingRequests = (int) ($serviceCounts->pending ?? 0);

        // 2) العملاء الجدد آخر 7 أيام
        $newCustomers = (int) User::query()
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        // 3) Charts ديناميكية (آخر 7 أيام)
        $requestsChart = $this->countByDay(ServiceRequest::query(), 'created_at', 7);
        $pendingChart  = $this->countByDay(ServiceRequest::query()->where('status', 'pending'), 'created_at', 7);
        $usersChart    = $this->countByDay(User::query(), 'created_at', 7);

        return [
            Stat::make('إجمالي الطلبات', $totalRequests)
                ->description('جميع الطلبات المسجلة')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart($requestsChart)
                ->color('success'),

            Stat::make('طلبات قيد الانتظار', $pendingRequests)
                ->description('تحتاج إلى اتخاذ إجراء')
                ->descriptionIcon('heroicon-m-bell-alert')
                ->chart($pendingChart)
                ->color('warning'),

            Stat::make('العملاء الجدد', $newCustomers)
                ->description('خلال آخر 7 أيام')
                ->descriptionIcon('heroicon-m-user-group')
                ->chart($usersChart)
                ->color('info'),
        ];
    }

    /**
     * يرجّع أرقام آخر N أيام (من الأقدم للأحدث) لعرضها في chart()
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
