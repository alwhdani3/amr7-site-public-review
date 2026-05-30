<?php

namespace App\Filament\Resources\ComplianceObligationResource\Pages;

use App\Filament\Resources\ComplianceObligationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListComplianceObligations extends ListRecords
{
    protected static string $resource = ComplianceObligationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('إضافة التزام'),
        ];
    }
}
