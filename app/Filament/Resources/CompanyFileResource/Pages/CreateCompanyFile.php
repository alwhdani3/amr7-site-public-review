<?php

namespace App\Filament\Resources\CompanyFileResource\Pages;

use App\Filament\Resources\CompanyFileResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCompanyFile extends CreateRecord
{
    protected static string $resource = CompanyFileResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['uploaded_by'] = auth()->id();
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}