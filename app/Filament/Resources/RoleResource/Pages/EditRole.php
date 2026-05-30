<?php
// app/Filament/Resources/RoleResource/Pages/EditRole.php
namespace App\Filament\Resources\RoleResource\Pages;
use App\Filament\Resources\RoleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn () => ! in_array($this->record->name, ['super_admin', 'customer'])),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * تحميل الصلاحيات الحالية للـ form عند الفتح
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['permissions'] = $this->record->permissions->pluck('name')->toArray();
        return $data;
    }

    /**
     * حفظ الصلاحيات بعد تحديث الدور
     */
    protected function afterSave(): void
    {
        $data = $this->form->getState();
        $perms = collect($data['permissions'] ?? [])->flatten()->filter()->values();
        $this->record->syncPermissions($perms);
    }
}
