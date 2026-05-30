<?php

namespace App\Filament\Resources\AgreementTemplateResource\Pages;

use App\Filament\Resources\AgreementTemplateResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateAgreementTemplate extends CreateRecord
{
    protected static string $resource = AgreementTemplateResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('تم إنشاء القالب بنجاح');
    }
}
