<?php

namespace Portable\FilaCms\Models;

use Portable\FilaCms\Filament\Resources\PageResource;

class Page extends AbstractContentResource
{
    protected $table = 'pages';

    protected static $resourceName = PageResource::class;
}
