<?php

namespace Portable\FilaCms\Models;

use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Overtrue\LaravelVersionable\Versionable;
use Overtrue\LaravelVersionable\VersionStrategy;
use Illuminate\Support\Facades\Schema;

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
        'order',
    ];

    protected $fillable = [
        'name',
        'code',
        'order',
    ];

    protected $appends = [
        'taxonomy_resources',
    ];

    protected $cascadeDeletes = ['terms'];

    // Memoised so the backwards-compat guard doesn't hit information_schema on
    // every query/hydration (Doctrine introspection is slow and memory-heavy).
    protected static ?bool $hasOrderColumn = null;

    protected static function hasOrderColumn(): bool
    {
        return static::$hasOrderColumn ??= Schema::hasColumn('taxonomies', 'order');
    }

    public function terms()
    {
        if (static::hasOrderColumn()) {
            return $this->hasMany(TaxonomyTerm::class, 'taxonomy_id')->orderBy('order');
        }
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

    public function newQuery(): Builder
    {
        if (static::hasOrderColumn()) {
            return parent::newQuery()->orderBy('order');
        }
        return parent::newQuery();
    }

    public static function booted(): void
    {
        static::deleting(function (Taxonomy $item) {
            $item->resources()->delete();
        });

        static::created(function (Taxonomy $item) {
            if (static::hasOrderColumn()) {
                // auto-add order with end of list
                $count = Taxonomy::max('order');
                $item->order = $count + 1;
                $item->save();
            }
        });
    }
}
