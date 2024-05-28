<?php

namespace Portable\FilaCms\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Portable\FilaCms\FilaCms;
use Portable\FilaCms\Models\LinkCheck;
use Str;

class LinkChecker implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
    }

    public function handle()
    {
        $models = (new FilaCms())->getRawContentModels();
        $batch = Str::random(8);

        foreach ($models as $model => $resource) {
            $modelClass = $model::orderBy('id', 'desc')->lazy()->each(function ($record) use ($resource, $batch) {
                $content = json_decode(tiptap_converter()->asJson($record->contents), true);
                $links = $this->extractLinks($content);

                foreach ($links as $key => $link) {
                    $model = LinkCheck::create([
                        'title'             => $record->title,
                        'origin_resource'   => $resource,
                        'edit_url'          => $resource::getUrl('edit', ['record' => $record->slug]),
                        'url'               => $link,
                        'status_code'       => 0, //initially assigned as 0 to indicate it hasn't been checked yet
                        'timeout'           => 0,
                        'batch_id'          => $batch,
                    ]);

                    CheckLink::dispatch($model);
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
            switch ($key) {
                case 'type': $type = $value;
                    break;
                case 'content': $content = $value;
                    break;
                case 'marks': $marks = $value;
                    break;
                default: break;
            }
        }

        // looks like having marks could be the current way of checking
        // if there might have a link
        if (count($marks) === 0) {
            if (count($content) > 0) {
                foreach ($content as $row) {
                    $recursiveLinks = $this->extractLinks($row);

                    $links = array_merge($links, $recursiveLinks);
                }
            }
        } else {
            foreach ($marks as $key => $mark) {
                if ($mark['type'] === 'link') {
                    $url = $mark['attrs']['href'];
                    $parsed = parse_url($url);

                    if (isset($parsed['host'])) {
                        $links[] = $url;
                    } else {
                        $links[] = url($url);
                    }
                }
            }
        }
        return $links;
    }
}
