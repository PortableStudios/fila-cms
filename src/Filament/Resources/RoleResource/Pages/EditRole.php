<?php

namespace Portable\FilaCms\Filament\Resources\RoleResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Portable\FilaCms\Filament\Resources\RoleResource;
use Filament\Notifications\Notification;
use Spatie\Permission\Models\Role;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function (Actions\DeleteAction $action, Role $role) {
                    if ($role->users->count() > 0) {
                        Notification::make()
                            ->title('Unable to perform action')
                            ->body('You cannot delete a role that are assigned to user(s)')
                            ->status('warning')
                            ->send();
                        $action->cancel();
                    }
                }),
        ];
    }
}
