<?php

namespace App\Filament\Resources\ServiceResource\Pages;

use App\Filament\Resources\ServiceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateService extends CreateRecord
{
    protected static string $resource = ServiceResource::class;

    protected function getCreateFormActionLabel(): string
    {
        return 'حفظ';
    }

    protected function getCreateAnotherFormActionLabel(): string
    {
        return 'حفظ وإضافة جديد';
    }
    protected function getRedirectUrl(): string
    {
        // يرجع لقائمة الجدول (Index) بدلاً من البقاء في نفس الصفحة
        return $this->getResource()::getUrl('index');
    }
}
