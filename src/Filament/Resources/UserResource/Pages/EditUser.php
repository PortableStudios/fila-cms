<?php

namespace Portable\FilaCms\Filament\Resources\UserResource\Pages;

use Portable\FilaCms\Filament\Resources\UserResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
