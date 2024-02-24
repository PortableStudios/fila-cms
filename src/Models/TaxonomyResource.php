<?php

namespace Portable\FilaCms\Models;

use Illuminate\Database\Eloquent\Model;

class TaxonomyResource extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'resource_class',
        'taxonomy_id',
    ];

    public function taxonomy()
    {
        return $this->belongsTo(Taxonomy::class, 'taxonomy_id');
    }
}
