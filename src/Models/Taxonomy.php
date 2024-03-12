<?php

namespace Portable\FilaCms\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class Taxonomy extends Model
{
    use RevisionableTrait;

    protected $fillable = [
        'name',
    ];

    protected $appends = [
        'taxonomy_resources',
    ];

    public function terms()
    {
        return $this->hasMany(TaxonomyTerm::class, 'taxonomy_id');
    }

    public function resources()
    {
        return $this->hasMany(TaxonomyResource::class, 'taxonomy_id');
    }

    public function taxonomyResources(): Attribute
    {
        return Attribute::make(function () {
            return $this->resources->pluck('resource_class');
        });
    }
}
