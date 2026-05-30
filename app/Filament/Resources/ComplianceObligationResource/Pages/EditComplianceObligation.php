<?php

namespace App\Filament\Resources\ComplianceObligationResource\Pages;

use App\Filament\Resources\ComplianceObligationResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditComplianceObligation extends EditRecord
{
    protected static string $resource = ComplianceObligationResource::class;

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
            ->title('تم تحديث الالتزام بنجاح');
    }
}
