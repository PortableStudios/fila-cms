<?php

namespace Portable\FilaCms\Models\Traits;

use Illuminate\Support\Str;
use Portable\FilaCms\Models\TaxonomyResource;

trait ProvidesSearchSettings
{
    public static $searchableAttributes = ['title','contents'];
    public static $filterableAttributes = [];
    public static $sortableAttributes = ['title','created_at','updated_at'];

    public static function getSearchableAttributes()
    {
        $attrs = static::$searchableAttributes;
        $attrs = array_merge($attrs, static::getSearchableTaxonomies());
        return $attrs;
    }

    public static function getSortableAttributes()
    {
        return static::$sortableAttributes;
    }

    public static function getFilterableAttributes()
    {
        $attrs = static::$filterableAttributes;
        $attrs = array_merge($attrs, static::getFilterableTaxonomies());
        return $attrs;
    }

    public static function getSearchableTaxonomies()
    {
        try {
            $attrs = [];
            $taxes = TaxonomyResource::where('resource_class', static::$resourceName)->get();
            foreach($taxes as $taxonomyResource) {
                $fieldName = Str::slug(Str::plural($taxonomyResource->taxonomy->name), '_');
                $attrs[] = $fieldName;
            }
            return $attrs;
        } catch (\Exception $e) {
            return[];
        }

    }

    public static function getFilterableTaxonomies()
    {
        try {
            $attrs = [];
            $taxes = TaxonomyResource::where('resource_class', static::$resourceName)->get();
            foreach($taxes as  $taxonomyResource) {
                $fieldName = Str::slug(Str::plural($taxonomyResource->taxonomy->name), '_');
                $attrs[] = $fieldName . '_ids';
            }
            return $attrs;
        } catch (\Exception $e) {
            return [];
        }
    }
}
