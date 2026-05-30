<?php

namespace App\Filament\Resources\FinancialStatementRequestResource\Pages;

use App\Filament\Resources\FinancialStatementRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFinancialStatementRequests extends ListRecords
{
    protected static string $resource = FinancialStatementRequestResource::class;

    public function getTitle(): string
    {
        return 'طلبات القوائم المالية';
    }

    public function getBreadcrumb(): string
    {
        return 'طلبات القوائم المالية';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('إنشاء طلب جديد'),
        ];
    }
}
