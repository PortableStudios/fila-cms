<?php

namespace Portable\FilaCms\Versionable;

use Illuminate\Database\Eloquent\Model;
use Overtrue\LaravelVersionable\Diff;
use Overtrue\LaravelVersionable\Version;

class FilaCmsVersion extends \Overtrue\LaravelVersionable\Version
{
    protected $table = 'versions';

    public function diff(?Version $toVersion = null, array $differOptions = [], array $renderOptions = []): Diff
    {
        if (! $toVersion) {
            $toVersion = $this->previousVersion() ?? new static();
        }

        return new FilaCmsDiff($this, $toVersion, $differOptions, $renderOptions);
    }

    public function revertWithoutSaving(): ?Model
    {
        $oldContents = $newContents = $this->contents;
        $newContents['contents'] = json_encode($this->contents['contents']);
        $this->contents = $newContents;
        $result = parent::revertWithoutSaving();
        $this->contents = $oldContents;

        return $result;
    }
}
