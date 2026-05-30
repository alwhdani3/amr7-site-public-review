<?php

namespace App\Filament\Resources\FinancialStatementRequestResource\Pages;

use App\Filament\Resources\FinancialStatementRequestResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFinancialStatementRequest extends CreateRecord
{
    protected static string $resource = FinancialStatementRequestResource::class;

    public function getTitle(): string
    {
        return 'إنشاء طلب قوائم مالية';
    }

    public function getBreadcrumb(): string
    {
        return 'إنشاء طلب';
    }

    // ✅ إعادة التوجيه إلى قائمة الطلبات بعد الإنشاء الناجح
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // 💡 (اختياري) إذا كنت بحاجة لتعديل البيانات قبل الحفظ (مثلاً إضافة المستخدم الحالي)
    // /*
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        return $data;
    }
    
}