<?php

namespace Portable\FilaCms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Portable\FilaCms\Filament\Resources\PageResource;

class Page extends AbstractContentModel
{
    use HasFactory;

    protected $table = 'pages';

    protected static $resourceName = PageResource::class;
}
