<?php

namespace App\Filament\Resources\TaxReturnRequestResource\Pages;

use App\Filament\Resources\TaxReturnRequestResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditTaxReturnRequest extends EditRecord
{
    protected static string $resource = TaxReturnRequestResource::class;

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
            ->title('تم تحديث الإقرار بنجاح');
    }
}
