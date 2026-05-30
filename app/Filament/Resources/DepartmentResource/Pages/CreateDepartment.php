<?php

namespace App\Filament\Resources\DepartmentResource\Pages;

use App\Filament\Resources\DepartmentResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateDepartment extends CreateRecord
{
    protected static string $resource = DepartmentResource::class;

    protected function afterCreate(): void
    {
        Notification::make()
            ->title('تم إنشاء القسم بنجاح')
            ->success()
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
