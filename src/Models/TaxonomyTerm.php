<?php

namespace Portable\FilaCms\Models;

use Illuminate\Database\Eloquent\Model;

class TaxonomyTerm extends Model
{
    protected $fillable = [
        'name',
        'taxonomy_id',
    ];

    public function taxonomy()
    {
        return $this->belongsTo(Taxonomy::class, 'taxonomy_id');
    }
}
