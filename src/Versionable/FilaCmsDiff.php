<?php

namespace Portable\FilaCms\Versionable;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Jfcherng\Diff\Differ;
use Jfcherng\Diff\DiffHelper;
use Overtrue\LaravelVersionable\Diff;

class FilaCmsDiff extends Diff
{
    public function getStatistics(array $differOptions = []): array
    {
        if (empty($differOptions)) {
            $differOptions = $this->differOptions;
        }

        $oldContents = $this->fromVersion->contents;
        $newContents = $this->toVersion->contents;

        $diffStats = new Collection();

        foreach ($oldContents as $key => $value) {
            if ($newContents[$key] !== $oldContents[$key]) {
                $newData = $newContents[$key];
                $oldData = $oldContents[$key];

                if (is_array($newData) || is_array($oldData)) {
                    $newData = tiptap_converter()->asText($newData);
                    $oldData = tiptap_converter()->asText($oldData);
                }

                $diffStats->push(
                    (new Differ(
                        explode("\n", $newData),
                        explode("\n", $oldData),
                    ))->getStatistics()
                );
            }
        }

        return [
            'inserted' => $diffStats->sum('inserted'),
            'deleted' => $diffStats->sum('deleted'),
            'unmodified' => $diffStats->sum('unmodified'),
            'changedRatio' => $diffStats->sum('changedRatio'),
        ];
    }


    public function render(?string $renderer = null, array $differOptions = [], array $renderOptions = []): array
    {
        if (empty($differOptions)) {
            $differOptions = $this->differOptions;
        }

        if (empty($renderOptions)) {
            $renderOptions = $this->renderOptions;
        }

        $oldContents = $this->fromVersion->contents;
        $newContents = $this->toVersion->contents;

        $diff = [];
        $createDiff = function ($key, $old, $new) use (&$diff, $renderer, $differOptions, $renderOptions) {
            if ($renderer) {
                $old = is_string($old) ? $old : (is_array($old) ? tiptap_converter()->asText($old) : json_encode($old));
                $new = is_string($new) ? $new : (is_array($new) ? tiptap_converter()->asText($new) : json_encode($new));
                $diff[$key] = str_replace('\n No newline at end of file', '', DiffHelper::calculate($old, $new, $renderer, $differOptions, $renderOptions));
            } else {
                $diff[$key] = compact('old', 'new');
            }
        };

        foreach ($oldContents as $key => $value) {
            $createDiff($key, Arr::get($newContents, $key), Arr::get($oldContents, $key));
        }

        foreach (array_diff_key($oldContents, $newContents) as $key => $value) {
            $createDiff($key, null, $value);
        }

        return $diff;
    }
}
