<?php

namespace Portable\FilaCms\Filament\Traits;

use Illuminate\Support\Str;
use Portable\FilaCms\Casts\DynamicTermIds;
use Portable\FilaCms\Casts\DynamicTermList;
use Portable\FilaCms\Models\TaxonomyResource;
use Portable\FilaCms\Models\TaxonomyTerm;

trait HasTaxonomies
{
    protected static $resourceName;

    public function terms()
    {
        return $this->morphToMany(TaxonomyTerm::class, 'taxonomyable');
    }

    public function initializeHasTaxonomies()
    {
        TaxonomyResource::where('resource_class', static::$resourceName)->get()->each(function (TaxonomyResource $taxonomyResource) use (&$fields) {
            $fieldName = Str::slug(Str::plural($taxonomyResource->taxonomy->name), '_');
            $this->casts[$fieldName] = DynamicTermList::class.':'.$taxonomyResource->taxonomy_id;
            $this->casts[$fieldName.'_ids'] = DynamicTermIds::class.':'.$taxonomyResource->taxonomy_id;
            $this->append($fieldName);
            $this->append($fieldName.'_ids');
        });
    }
}
