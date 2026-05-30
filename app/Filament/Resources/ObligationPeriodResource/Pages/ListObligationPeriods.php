<?php

namespace App\Filament\Resources\ObligationPeriodResource\Pages;

use App\Filament\Resources\ObligationPeriodResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListObligationPeriods extends ListRecords
{
    protected static string $resource = ObligationPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('إضافة فترة'),
        ];
    }
}
