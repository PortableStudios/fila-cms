<?php

namespace Portable\FilaCms\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class DynamicTermIds implements CastsAttributes
{
    protected $taxonomyId;

    public function __construct($taxonomyId)
    {
        $this->taxonomyId = $taxonomyId;
    }

    public function get($model, $key, $value, $attributes)
    {
        return $model->terms()->where('taxonomy_id', $this->taxonomyId)->pluck('id');
    }

    public function set($model, $key, $value, $attributes)
    {

    }
}
