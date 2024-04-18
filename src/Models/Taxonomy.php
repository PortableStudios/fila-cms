<?php

namespace Portable\FilaCms\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Overtrue\LaravelVersionable\Versionable;
use Overtrue\LaravelVersionable\VersionStrategy;
use Illuminate\Database\Eloquent\SoftDeletes;

class Taxonomy extends Model
{
    use Versionable;
    use SoftDeletes;

    protected $versionStrategy = VersionStrategy::SNAPSHOT;

    protected $versionable = [
        'name',
        'taxonomy_resources',
    ];

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
