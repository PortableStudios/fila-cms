<?php

namespace Portable\FilaCms\Filament\Traits;

use Illuminate\Support\Str;
use Portable\FilaCms\Casts\DynamicTermIds;
use Portable\FilaCms\Casts\DynamicTermList;
use Portable\FilaCms\Models\Taxonomy;
use Portable\FilaCms\Models\Taxonomyable;
use Portable\FilaCms\Models\TaxonomyResource;
use Portable\FilaCms\Models\TaxonomyTerm;

trait HasTaxonomies
{
    protected static $resourceName;

    protected static $_taxonomies = null;

    protected $_saveTaxonomyFields = [];

    protected $_virtualTaxonomyFields = [];

    public function terms()
    {
        return $this->morphToMany(TaxonomyTerm::class, 'taxonomyable');
    }

    public function taxonomyable()
    {
        return $this->hasMany(Taxonomyable::class, 'taxonomyable_id')->where('taxonomyable_type', static::class);
    }

    public static function taxonomies()
    {
        if (isset(static::$_taxonomies)) {
            return static::$_taxonomies;
        }

        static::$_taxonomies = Taxonomy::whereIn('id', TaxonomyResource::where('resource_class', static::$resourceName)->pluck('taxonomy_id'))->get();

        return static::$_taxonomies;
    }

    public static function bootHasTaxonomies()
    {
        static::creating(function ($model) {
            $model->undirtyVirtualAttributes(true);
        });

        static::updating(function ($model) {
            $model->undirtyVirtualAttributes();
        });

        static::updated(function ($model) {
            $model->persistVirtualTaxonomies();
        });

        static::created(function ($model) {
            $model->persistVirtualTaxonomies();
        });
    }

    protected function persistVirtualTaxonomies()
    {
        $this->taxonomyable()->delete();
        foreach ($this->_virtualTaxonomyFields as $fieldName) {
            $items = $this->_saveTaxonomyFields[$fieldName.'_ids'];
            $this->terms()->attach($items);
        }
    }

    protected function undirtyVirtualAttributes($creating = false)
    {
        foreach ($this->_virtualTaxonomyFields as $field) {
            if (isset($this->attributes[$field])) {
                if ($creating) {
                    unset($this->attributes[$field]);
                } else {
                    $this->original[$field] = $this->attributes[$field];
                }
            }
            if (isset($this->attributes[$field.'_ids'])) {
                $this->_saveTaxonomyFields[$field.'_ids'] = $this->attributes[$field.'_ids'];
                if ($creating) {
                    unset($this->attributes[$field . '_ids']);
                } else {
                    $this->original[$field] = $this->attributes[$field] = null;
                    $this->original[$field.'_ids'] = $this->attributes[$field.'_ids'];
                }
            }
        }
    }

    public function initializeHasTaxonomies()
    {
        TaxonomyResource::where('resource_class', static::$resourceName)->get()->each(function (TaxonomyResource $taxonomyResource) use (&$fields) {
            $fieldName = Str::slug(Str::plural($taxonomyResource->taxonomy->name), '_');
            $this->casts[$fieldName] = DynamicTermList::class.':'.$taxonomyResource->taxonomy_id;
            $this->casts[$fieldName.'_ids'] = DynamicTermIds::class.':'.$taxonomyResource->taxonomy_id;
            $this->append($fieldName);
            $this->append($fieldName.'_ids');
            $this->fillable[] = $fieldName.'_ids';
            $this->_virtualTaxonomyFields[] = $fieldName;
        });
    }
}
