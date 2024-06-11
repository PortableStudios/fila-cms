<?php

namespace Portable\FilaCms\Commands;

use Illuminate\Console\Command;
use Portable\FilaCms\Facades\FilaCms;

class SyncSearch extends Command
{
    protected $signature = 'fila-cms:sync-search';

    protected $description = 'Sync search settings, flush indexes and reimport content models';

    public function handle()
    {
        $this->call('scout:delete-all-indexes');
        $this->call('scout:sync-index-settings');

        foreach(FilaCms::getRawContentModels() as $model => $resource) {
            $this->call('scout:import', ['model' => $model]);
        }
    }
}
