<?php

namespace Portable\FilaCms\Filament\Traits;

use Illuminate\Support\Facades\Schema;
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
        return Taxonomy::whereIn('id', TaxonomyResource::where('resource_class', static::$resourceName)->pluck('taxonomy_id'))->get();
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
            $items = isset($this->_saveTaxonomyFields[$fieldName . '_ids']) ? $this->_saveTaxonomyFields[$fieldName . '_ids'] : [];
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
            if (!isset($this->attributes[$field . '_ids'])) {
                $caster = $this->resolveCasterClass($field . '_ids');
                $values = $caster->get($this, $field . '_ids', null, $this->attributes);
                $this->attributes[$field . '_ids'] = $values;
            }
            $this->_saveTaxonomyFields[$field . '_ids'] = $this->attributes[$field . '_ids'];
            if ($creating) {
                unset($this->attributes[$field . '_ids']);
            } else {
                $this->original[$field] = $this->attributes[$field] = null;
                $this->original[$field . '_ids'] = $this->attributes[$field . '_ids'];
            }

        }
    }

    public function initializeHasTaxonomies()
    {
        // Potentially, this gets run before a database migration has occurred,
        // for example, if the "User" model HasTaxonomies.  So tables or even the db
        // may not exist.  Handle that gracefully.
        try {
            if (!Schema::hasTable('taxonomy_resources')) {
                return;
            }
        } catch (\Exception $e) {
        }



        TaxonomyResource::where('resource_class', static::$resourceName)->get()->each(function (TaxonomyResource $taxonomyResource) use (&$fields) {
            $fieldName = Str::slug(Str::plural($taxonomyResource->taxonomy->name), '_');
            $this->casts[$fieldName] = DynamicTermList::class . ':' . $taxonomyResource->taxonomy_id;
            $this->casts[$fieldName . '_ids'] = DynamicTermIds::class . ':' . $taxonomyResource->taxonomy_id;
            $this->append($fieldName);
            $this->append($fieldName . '_ids');
            $this->fillable[] = $fieldName . '_ids';
            $this->_virtualTaxonomyFields[] = $fieldName;
        });
    }
}
