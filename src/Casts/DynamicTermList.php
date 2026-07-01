<?php

namespace Portable\FilaCms\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class DynamicTermList implements CastsAttributes
{
    protected $taxonomyId;

    public function __construct($taxonomyId)
    {
        $this->taxonomyId = $taxonomyId;
    }

    public function get($model, $key, $value, $attributes)
    {
        // Use the eager-loaded relation when present to avoid an N+1 query per
        // taxonomy field on list views; fall back to a scoped query otherwise.
        if ($model->relationLoaded('terms')) {
            return $model->terms
                ->where('taxonomy_id', $this->taxonomyId)
                ->values();
        }

        return $model->terms()->where('taxonomy_id', $this->taxonomyId)->get();
    }

    // @codeCoverageIgnoreStart
    public function set($model, $key, $value, $attributes)
    {
    }
    // @codeCoverageIgnoreEnd
}
