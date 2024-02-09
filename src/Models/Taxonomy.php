<?php

namespace Portable\FilaCms\Models;

use Illuminate\Database\Eloquent\Model;

class Taxonomy extends Model
{
    protected $fillable = [
        'name',
    ];

    public function terms()
    {
        return $this->hasMany(TaxonomyTerm::class, 'taxonomy_id');
    }
}