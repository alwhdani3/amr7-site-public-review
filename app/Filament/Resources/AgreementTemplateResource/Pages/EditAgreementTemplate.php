<?php

namespace App\Filament\Resources\AgreementTemplateResource\Pages;

use App\Filament\Resources\AgreementTemplateResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditAgreementTemplate extends EditRecord
{
    protected static string $resource = AgreementTemplateResource::class;

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
            ->title('تم حفظ التعديلات');
    }
}
