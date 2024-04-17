<?php

namespace Portable\FilaCms\Facades;

use Illuminate\Support\Facades\Facade;

class MediaLibrary extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'fila-cms-media';
    }
}
