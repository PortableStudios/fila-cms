<?php

namespace Portable\FilaCms\Filament\Resources\PageResource\Pages;

use Portable\FilaCms\Filament\Resources\AbstractContentResource\Pages\EditAbstractContentResource;
use Portable\FilaCms\Filament\Resources\PageResource;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;

class EditPage extends EditAbstractContentResource
{
    protected static string $resource = PageResource::class;

    public function getTitle(): string | Htmlable
    {
        return Str::take($this->data['title'], 35);
    }
}
