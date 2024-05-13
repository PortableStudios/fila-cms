<?php

namespace Portable\FilaCms\Models;

use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Overtrue\LaravelVersionable\Versionable;
use Overtrue\LaravelVersionable\VersionStrategy;

class Taxonomy extends Model
{
    use Versionable;
    use SoftDeletes;
    use CascadeSoftDeletes;
    use HasFactory;

    protected $versionStrategy = VersionStrategy::SNAPSHOT;

    protected $versionable = [
        'name',
        'taxonomy_resources',
    ];

    protected $fillable = [
        'name','code'
    ];

    protected $appends = [
        'taxonomy_resources',
    ];

    protected $cascadeDeletes = ['terms'];

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
