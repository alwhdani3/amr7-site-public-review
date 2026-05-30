<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;

    // ✅ التوجيه إلى صفحة القائمة بعد الإنشاء الناجح
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function getCreatedNotificationTitle(): ?string
{
    return 'تم نشر/حفظ المقال بنجاح! 🎉';
}
}