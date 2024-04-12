<?php

namespace Portable\FilaCms\Models;

use Illuminate\Database\Eloquent\Model;
use Overtrue\LaravelVersionable\Versionable;
use Overtrue\LaravelVersionable\VersionStrategy;

class TaxonomyTerm extends Model
{
    use Versionable;

    protected $versionStrategy = VersionStrategy::SNAPSHOT;

    protected $versionable = [
        'name',
    ];

    protected $fillable = [
        'name',
        'taxonomy_id',
    ];

    public function taxonomy()
    {
        return $this->belongsTo(Taxonomy::class, 'taxonomy_id');
    }
}
