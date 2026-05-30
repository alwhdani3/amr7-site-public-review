<?php

namespace App\Filament\Resources\TaxReturnRequestResource\Pages;

use App\Filament\Resources\TaxReturnRequestResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateTaxReturnRequest extends CreateRecord
{
    protected static string $resource = TaxReturnRequestResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('تم إنشاء الإقرار بنجاح');
    }
}
