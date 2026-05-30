<?php

namespace App\Filament\Resources\CompanyFileResource\Pages;

use App\Filament\Resources\CompanyFileResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListCompanyFiles extends ListRecords
{
    protected static string $resource = CompanyFileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('رفع ملف'),
        ];
    }
}