<?php

namespace Portable\FilaCms\Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

trait CreatesApplication
{
    /**
     * Creates the application.
     */
    public function createApplication(): Application
    {
        $app = require __DIR__.'/../vendor/orchestra/testbench-core/laravel/bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
