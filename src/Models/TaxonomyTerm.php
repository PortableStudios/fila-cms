<?php

namespace Portable\FilaCms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Overtrue\LaravelVersionable\Versionable;
use Overtrue\LaravelVersionable\VersionStrategy;

class TaxonomyTerm extends Model
{
    use Versionable;
    use SoftDeletes;
    use HasFactory;

    protected $versionStrategy = VersionStrategy::SNAPSHOT;

    protected $versionable = [
        'name',
        'order',
    ];

    protected $fillable = [
        'name',
        'taxonomy_id',
        'parent_id',
        'order',
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

    public static function booted(): void
    {
        static::created(function (TaxonomyTerm $item) {
            // auto-add order with end of list
            $count = TaxonomyTerm::where('taxonomy_id', $item->taxonomy_id)->max('order');
            $item->order = $count + 1;
            $item->save();
        });
    }
}
