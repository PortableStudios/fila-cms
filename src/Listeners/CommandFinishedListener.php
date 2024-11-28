<?php

namespace Portable\FilaCms\Listeners;

use Illuminate\Console\Events\CommandFinished;
use Portable\FilaCms\Events\AfterSyncSearchSettings;

class CommandFinishedListener
{
    public function handle(CommandFinished $event): void
    {
        $indexCommands = ['scout:sync-index-settings','tinker','fila-cms:sync-search'];
        if (in_array($event->command, $indexCommands)) {
            AfterSyncSearchSettings::dispatch();
            // Now update the stop words for all the models
            // that are searchable
            $indexes = config('scout.meilisearch.index-settings');
            $stopWords = json_decode(\Portable\FilaCms\Models\Setting::get('search.stop_words'));
            if (!is_array($stopWords)) {
                $stopWords = [];
            }

            $client = app(\Laravel\Scout\EngineManager::class)->createMeilisearchDriver();
            foreach ($indexes as $indexName => $settings) {
                $client->index($indexName)->updateStopWords($stopWords);
            }
        }
    }
}
