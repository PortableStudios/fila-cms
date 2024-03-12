<?php

namespace Portable\FilaCms\Models;

use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class TaxonomyTerm extends Model
{
    use RevisionableTrait;

    protected $fillable = [
        'name',
        'taxonomy_id',
    ];

    public function taxonomy()
    {
        return $this->belongsTo(Taxonomy::class, 'taxonomy_id');
    }
}
