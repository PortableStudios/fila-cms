<?php

namespace Portable\FilaCms\Tests\Providers;

use Illuminate\Console\Events\CommandStarting;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use Portable\FilaCms\Tests\Listeners\CommandStartingListener;

class WorkbenchServiceProvider extends EventServiceProvider
{
    protected $listen = [
        CommandStarting::class => [
            CommandStartingListener::class
        ]
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        parent::register();
        if(class_exists('Workbench\App\Models\User')) {
            config(['auth.providers.users.model' => 'Workbench\App\Models\User']);
        }

        config(['mail.mailers.smtp.host' => 'localhost']);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {

    }
}
