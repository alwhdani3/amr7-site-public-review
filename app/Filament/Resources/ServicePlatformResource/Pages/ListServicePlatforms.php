<?php

namespace App\Filament\Resources\ServicePlatformResource\Pages;

use App\Filament\Resources\ServicePlatformResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListServicePlatforms extends ListRecords
{
    protected static string $resource = ServicePlatformResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
