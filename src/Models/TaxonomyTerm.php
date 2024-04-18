<?php

namespace Portable\FilaCms\Models;

use Illuminate\Database\Eloquent\Model;
use Overtrue\LaravelVersionable\Versionable;
use Overtrue\LaravelVersionable\VersionStrategy;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxonomyTerm extends Model
{
    use Versionable;
    use SoftDeletes;

    protected $versionStrategy = VersionStrategy::SNAPSHOT;

    protected $versionable = [
        'name',
    ];

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

    public function taxonomyables()
    {
        return $this->hasMany(Taxonomyable::class);
    }
}
