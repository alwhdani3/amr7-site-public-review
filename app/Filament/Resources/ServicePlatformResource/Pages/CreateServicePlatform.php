<?php

namespace App\Filament\Resources\ServicePlatformResource\Pages;

use App\Filament\Resources\ServicePlatformResource;
use Filament\Resources\Pages\CreateRecord;

class CreateServicePlatform extends CreateRecord
{
    protected static string $resource = ServicePlatformResource::class;

    /**
     * 🔁 بعد الحفظ رجّع لصفحة القائمة
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }


    /**
     * أزرار أوضح (اختياري لكن احترافي)
     */
    protected function getCreateFormActionLabel(): string
    {
        return 'حفظ';
    }

    protected function getCreateAnotherFormActionLabel(): string
    {
        return 'حفظ وإضافة منصة أخرى';
    }
}
