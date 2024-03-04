<?php

namespace Portable\FilaCms\Models;

use Illuminate\Database\Eloquent\Model;

class Taxonomyable extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'taxonomy_term_id',
        'taxonomyable_id',
        'taxonomyable_type',
    ];

    public function taxonomy()
    {
        return $this->belongsTo(Taxonomy::class, 'taxonomy_id');
    }
}
