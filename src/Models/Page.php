<?php

namespace Portable\FilaCms\Models;

use Illuminate\Database\Eloquent\Model;
use Portable\FilaCms\Models\AbstractContentResource;

class Page extends AbstractContentResource
{
    protected $table = 'pages';
}