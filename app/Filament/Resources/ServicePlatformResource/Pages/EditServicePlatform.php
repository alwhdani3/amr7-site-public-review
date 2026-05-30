<?php

namespace App\Filament\Resources\ServicePlatformResource\Pages;

use App\Filament\Resources\ServicePlatformResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditServicePlatform extends EditRecord
{
    protected static string $resource = ServicePlatformResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
