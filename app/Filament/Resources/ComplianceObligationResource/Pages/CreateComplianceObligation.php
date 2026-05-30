<?php

namespace App\Filament\Resources\ComplianceObligationResource\Pages;

use App\Filament\Resources\ComplianceObligationResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateComplianceObligation extends CreateRecord
{
    protected static string $resource = ComplianceObligationResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('تم إنشاء الالتزام بنجاح');
    }
}
