<?php
// app/Filament/Resources/UserResource/Pages/EditUser.php
namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Arrayable;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn () => auth()->user()?->hasPermissionTo('employees.delete')),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Defense in depth — mirrors CreateUser. The mobile field already
     * normalises via dehydrateStateUsing; re-running here guarantees that
     * any future code path bypassing the field still writes canonical
     * +9665XXXXXXXX. normalizeSaudiMobile is idempotent.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (array_key_exists('mobile', $data)) {
            $data['mobile'] = UserResource::normalizeSaudiMobile($data['mobile']);
        }

        return $data;
    }

    protected function afterSave(): void
    {
        // spatie_role and email_verified are ->dehydrated(false) in
        // UserResource::form(), so getState() returns them as null/false and
        // role sync + verification toggle silently fail. Read via
        // getRawState() to get the actual UI values.
        $roleName = $this->getRawFormValue('spatie_role');
        if ($roleName) {
            $this->record->syncRoles([$roleName]);
        }

        // Edit page treats the toggle as the source of truth: sync
        // email_verified_at both ways. (CreateUser only sets, never unsets —
        // a newly-created user without verification simply hasn't been
        // verified yet.)
        $emailVerified = (bool) $this->getRawFormValue('email_verified');
        if ($emailVerified && blank($this->record->email_verified_at)) {
            $this->record->forceFill(['email_verified_at' => now()])->save();
        } elseif (! $emailVerified && filled($this->record->email_verified_at)) {
            $this->record->forceFill(['email_verified_at' => null])->save();
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
