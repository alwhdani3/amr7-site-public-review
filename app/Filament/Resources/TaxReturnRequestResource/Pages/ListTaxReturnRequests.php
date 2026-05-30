<?php

namespace App\Filament\Resources\TaxReturnRequestResource\Pages;

use App\Filament\Resources\TaxReturnRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTaxReturnRequests extends ListRecords
{
    protected static string $resource = TaxReturnRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('إضافة إقرار'),
        ];
    }
}
