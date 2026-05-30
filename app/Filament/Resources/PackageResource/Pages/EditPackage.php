<?php

namespace App\Filament\Resources\PackageResource\Pages;

use App\Filament\Resources\PackageResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPackage extends EditRecord
{
    protected static string $resource = PackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('تم تحديث الباقة بنجاح');
    }
}