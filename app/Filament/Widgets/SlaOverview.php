<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;

class SlaOverview extends Widget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.sla-overview';

    protected function getViewData(): array
    {
        // ✅ تعريف Query أساسي للتذاكر "النشطة" التي عليها SLA
        $baseQuery = Ticket::query()
            ->whereNotNull('sla_deadline')
            ->where('status', '!=', 'closed'); // التذاكر المفتوحة فقط

        // 1) إجمالي التذاكر النشطة التي لها SLA
        $totalTickets = (clone $baseQuery)->count();

        // 2) التذاكر التي تجاوزت SLA
        $breachedTickets = (clone $baseQuery)
            ->where('sla_deadline', '<', now())
            ->count();

        // 3) التذاكر الملتزمة (ضمن SLA)
        $compliantTickets = max($totalTickets - $breachedTickets, 0);

        // 4) نسبة الالتزام
        $complianceRate = $totalTickets > 0
            ? round(($compliantTickets / $totalTickets) * 100, 1)
            : 100.0;

        $color = match (true) {
            $complianceRate >= 90 => 'success',
            $complianceRate >= 70 => 'warning',
            default => 'danger',
        };

        return [
            'total'     => $totalTickets,
            'breached'  => $breachedTickets,
            'compliant' => $compliantTickets,
            'rate'      => $complianceRate,
            'color'     => $color,
        ];
    }
}
