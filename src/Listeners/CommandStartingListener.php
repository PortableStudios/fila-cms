<?php

namespace Portable\FilaCms\Listeners;

use Illuminate\Console\Events\CommandStarting;
use Portable\FilaCms\Events\BeforeSyncSearchSettings;
use Portable\FilaCms\Facades\FilaCms;

class CommandStartingListener
{
    public function handle(CommandStarting $event): void
    {
        $indexCommands = ['scout:sync-index-settings','tinker'];
        if(in_array($event->command, $indexCommands)) {
            // Pre-setup all the models we can reasonably know about
            foreach(FilaCms::getRawContentModels() as $model => $resource) {
                if(method_exists($model, 'getSearchableAttributes')) {
                    $searchableAttributes = $model::getSearchableAttributes();
                    $sortableAttributes = $model::getSortableAttributes();
                    $filterableAttributes = $model::getFilterableAttributes();
                    $indexName = (new $model())->searchableAs();
                    $indexSettings = [
                        'searchableAttributes' => $searchableAttributes,
                        'sortableAttributes' => $sortableAttributes,
                        'filterableAttributes' => $filterableAttributes,
                    ];
                    config(['scout.meilisearch.index-settings' => array_merge(config('scout.meilisearch.index-settings'), [$indexName => $indexSettings])]);
                }
            }
            BeforeSyncSearchSettings::dispatch();
        }
    }
}
