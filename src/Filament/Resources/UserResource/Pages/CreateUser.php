<?php

namespace Portable\FilaCms\Filament\Resources\UserResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Portable\FilaCms\Filament\Resources\UserResource;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}