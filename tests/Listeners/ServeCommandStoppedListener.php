<?php

namespace Portable\FilaCms\Tests\Listeners;

use Illuminate\Support\Facades\Artisan;

class ServeCommandStoppedListener
{
    public function handle()
    {
        Artisan::call('package:drop-sqlite-db');
    }
}
