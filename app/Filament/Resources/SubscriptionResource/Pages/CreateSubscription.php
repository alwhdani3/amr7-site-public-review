<?php

namespace App\Filament\Resources\SubscriptionResource\Pages;

use App\Filament\Resources\SubscriptionResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateSubscription extends CreateRecord
{
    protected static string $resource = SubscriptionResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('تم إنشاء الاشتراك بنجاح');
    }
}
