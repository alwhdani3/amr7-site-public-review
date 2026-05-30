<?php

namespace App\Filament\Resources\ObligationPeriodResource\Pages;

use App\Filament\Resources\ObligationPeriodResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditObligationPeriod extends EditRecord
{
    protected static string $resource = ObligationPeriodResource::class;

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
            ->title('تم تحديث الفترة بنجاح');
    }
}
