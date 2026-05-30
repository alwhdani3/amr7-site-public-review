<?php
// app/Filament/Resources/RoleResource/Pages/CreateRole.php
namespace App\Filament\Resources\RoleResource\Pages;
use App\Filament\Resources\RoleResource;
use Filament\Resources\Pages\CreateRecord;
use Spatie\Permission\Models\Permission;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        // حفظ الصلاحيات المختارة
        $data = $this->form->getState();
        $perms = collect($data['permissions'] ?? [])->flatten()->filter()->values();
        $this->record->syncPermissions($perms);
    }
}
