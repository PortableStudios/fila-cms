<?php

namespace Portable\FilaCms\Facades;

use Illuminate\Support\Facades\Facade;

class FilaCms extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'fila-cms';
    }
}
