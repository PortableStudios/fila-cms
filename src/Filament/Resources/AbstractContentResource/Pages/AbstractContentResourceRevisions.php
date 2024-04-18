<?php

namespace Portable\FilaCms\Filament\Resources\AbstractContentResource\Pages;

use Portable\FilaCms\Filament\Resources\AbstractContentResource;
use Mansoor\FilamentVersionable\RevisionsPage;

class AbstractContentResourceRevisions extends RevisionsPage
{
    protected static string $resource = AbstractContentResource::class;
}
