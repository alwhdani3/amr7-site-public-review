<?php

namespace App\Filament\Resources\ServiceCategoryResource\Pages;

use App\Filament\Resources\ServiceCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateServiceCategory extends CreateRecord
{
    protected static string $resource = ServiceCategoryResource::class;

    /**
     * بعد الحفظ يرجع تلقائيًا لصفحة القائمة
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * نص زر الحفظ
     */
    protected function getCreateFormActionLabel(): string
    {
        return 'حفظ';
    }

    /**
     * نص زر (حفظ وإضافة جديد)
     */
    protected function getCreateAnotherFormActionLabel(): string
    {
        return 'حفظ وإضافة جديد';
    }

}
