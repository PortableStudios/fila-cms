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
        'parent_id',
    ];

    public function taxonomy()
    {
        return $this->belongsTo(Taxonomy::class, 'taxonomy_id');
    }

    public function parent()
    {
        return $this->belongsTo(TaxonomyTerm::class, 'parent_id');
    }
}
