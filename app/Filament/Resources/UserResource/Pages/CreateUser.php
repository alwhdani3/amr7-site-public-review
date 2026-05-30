<?php
// app/Filament/Resources/UserResource/Pages/CreateUser.php
namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Arrayable;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Defense in depth: the mobile field already normalises via
     * dehydrateStateUsing, but re-running here guarantees that any future
     * code path (custom actions, bulk imports, etc.) that bypasses the
     * field still writes a canonical +9665XXXXXXXX value.
     * normalizeSaudiMobile is idempotent on already-normalised input.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (array_key_exists('mobile', $data)) {
            $data['mobile'] = UserResource::normalizeSaudiMobile($data['mobile']);
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $roleName = $this->getRawFormValue('spatie_role');

        if ($roleName) {
            $this->record->syncRoles([$roleName]);
        }

        $emailVerified = (bool) $this->getRawFormValue('email_verified');

        if ($emailVerified && blank($this->record->email_verified_at)) {
            $this->record->forceFill(['email_verified_at' => now()])->save();
        }
    }

    protected function getRawFormValue(string $key): mixed
    {
        $state = $this->form->getRawState();

        if ($state instanceof Arrayable) {
            $state = $state->toArray();
        }

        return data_get($state, $key);
    }
}
