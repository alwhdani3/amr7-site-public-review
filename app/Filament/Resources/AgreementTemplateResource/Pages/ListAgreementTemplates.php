<?php

namespace App\Filament\Resources\AgreementTemplateResource\Pages;

use App\Filament\Resources\AgreementTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAgreementTemplates extends ListRecords
{
    protected static string $resource = AgreementTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('إضافة قالب'),
        ];
    }
}
