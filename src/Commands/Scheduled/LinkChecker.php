<?php

namespace Portable\FilaCms\Commands\Scheduled;

use Illuminate\Console\Command;
use Portable\FilaCms\FilaCms;
use Portable\FilaCms\Models\LinkCheck;
use Str;
use Portable\FilaCms\Jobs\CheckLink;

class LinkChecker extends Command
{
    protected $signature = 'fila-cms:verify-links';

    protected $description = 'Install Fila CMS';

    public function handle()
    {
        $models = (new FilaCms)->getRawContentModels();
        $batch = Str::random(8);

        foreach ($models as $model => $resource) {
            $modelClass = $model::orderBy('id', 'desc')->chunk(10, function($records) use ($resource, $batch) {
                foreach ($records as $key => $record) {
                    $content = json_decode(tiptap_converter()->asJson($record->contents), TRUE);
                    $links = $this->extractLinks($content);

                    foreach ($links as $key => $link) {
                        $model = LinkCheck::create([
                            'title'             => $record->title,
                            'origin_resource'   => $resource,
                            'edit_url'          => $resource::getUrl('edit', ['record' => $record->id]),
                            'url'               => $link,
                            'status_code'       => 0, //initially assigned as 0 to indicate it hasn't been checked yet
                            'timeout'           => 0,
                            'batch_id'          => $batch,
                        ]);

                        CheckLink::dispatch($model);
                    }
                }
            });
        }
    }

    protected function extractLinks(array $data): array
    {
        $type = '';
        $content = [];
        $marks = [];

        $links = [];

        foreach ($data as $key => $value) {
            if ($key === 'type') $type = $value;
            if ($key === 'content') $content = $value;
            if ($key === 'marks') $marks = $value;
        }

        if ($type !== 'text') {
            if (count($content) > 0) {
                foreach ($content as $row) {
                    $recursiveLinks = $this->extractLinks($row);
    
                    $links = array_merge($links, $recursiveLinks);
                }
            }
        } else {
            // check if has marks
            if (count($marks) > 0) {
                // find type === link
                foreach ($marks as $key => $mark) {
                    if ($mark['type'] === 'link') {
                        $links[] = $mark['attrs']['href'];
                    }
                }
            }
        }

        return $links;
    }

    protected function checkLinkHealth($url)
    {

    }
}
