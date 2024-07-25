<?php

namespace Portable\FilaCms\Listeners;

use Illuminate\Console\Events\CommandStarting;
use Portable\FilaCms\Events\BeforeSyncSearchSettings;
use Portable\FilaCms\Facades\FilaCms;

class CommandStartingListener
{
    public function handle(CommandStarting $event): void
    {
        $indexCommands = ['scout:sync-index-settings','tinker','fila-cms:sync-search'];
        if(in_array($event->command, $indexCommands)) {
            FilaCms::setMeilisearchConfigs();
            BeforeSyncSearchSettings::dispatch();
        }
    }
}
