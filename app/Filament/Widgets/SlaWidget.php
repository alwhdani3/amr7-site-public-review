<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Filament\Widgets\Widget;

class SlaWidget extends Widget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.sla-widget';

    protected function getViewData(): array
    {
        // ✅ Query أساسي للتذاكر النشطة التي عليها SLA
        $base = Ticket::query()
            ->whereNotNull('sla_deadline')
            ->where('status', '!=', 'closed');

        $totalActive = (clone $base)->count();

        $breached = (clone $base)
            ->where('sla_deadline', '<', now())
            ->count();

        $onTrack = max($totalActive - $breached, 0);

        $compliance = $totalActive > 0
            ? (int) round(($onTrack / $totalActive) * 100)
            : 100;

        $color = match (true) {
            $compliance >= 90 => 'success',
            $compliance >= 70 => 'warning',
            default           => 'danger',
        };

        return [
            'total'      => $totalActive,
            'breached'   => $breached,
            'onTrack'    => $onTrack,
            'compliance' => $compliance,
            'color'      => $color,
        ];
    }
}
