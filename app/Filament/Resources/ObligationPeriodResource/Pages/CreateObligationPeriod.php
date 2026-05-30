<?php

namespace App\Filament\Resources\ObligationPeriodResource\Pages;

use App\Filament\Resources\ObligationPeriodResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateObligationPeriod extends CreateRecord
{
    protected static string $resource = ObligationPeriodResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('تم إنشاء الفترة بنجاح');
    }
}
