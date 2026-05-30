<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Widgets\TicketsStats;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            TicketsStats::class,
        ];
    }
}
