<?php

namespace App\Filament\Resources\ServiceRequestResource\Pages;

use App\Filament\Resources\ServiceRequestResource;
use Filament\Resources\Pages\CreateRecord;

class CreateServiceRequest extends CreateRecord
{
    protected static string $resource = ServiceRequestResource::class;

    protected function getCreateFormActionLabel(): string
    {
        return 'حفظ';
    }

    protected function getCreateAnotherFormActionLabel(): string
    {
        return 'حفظ وإضافة جديد';
    }

    protected function getCancelFormActionLabel(): string
    {
        return 'إلغاء';
    }
    protected function getRedirectUrl(): string
    {
        // يرجع لقائمة الجدول (Index) بدلاً من البقاء في نفس الصفحة
        return $this->getResource()::getUrl('index');
    }
}
