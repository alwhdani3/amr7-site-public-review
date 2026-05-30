<?php

namespace App\Filament\Resources\CompanyResource\Pages;

use App\Filament\Resources\CompanyResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditCompany extends EditRecord
{
    protected static string $resource = CompanyResource::class;

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
            ->title('تم تحديث بيانات الشركة بنجاح');
    }
}
