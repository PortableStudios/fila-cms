<?php

namespace Portable\FilaCms\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Overtrue\LaravelVersionable\Versionable;
use Overtrue\LaravelVersionable\VersionStrategy;

class MenuItem extends Model
{
    use Versionable;
    use SoftDeletes;
    use HasFactory;

    protected $versionStrategy = VersionStrategy::SNAPSHOT;
    protected static $unguarded = true;

    protected $versionable = [
        'name',
        'type',
        'reference',
        'parent_id',
    ];

    protected $appends = [
        'reference_text',
        'reference_page',
        'reference_content'
    ];

    protected $casts = [
        'reference' => 'json'
    ];

    public static function boot()
    {
        parent::boot();

        static::saving(function ($menuItem) {
            return;
            unset($menuItem->attributes['reference_page']);
            unset($menuItem->attributes['reference_content']);
            unset($menuItem->attributes['reference_text']);
            $menuItem->attributes['reference'] = $menuItem->reference;
        });
    }

    public function parent()
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(MenuItem::class, 'parent_id');
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    public function referenceText(): Attribute
    {
        return Attribute::make(
            function () {
                return data_get($this->reference, 'reference_text', '');
            },
            function ($value) {
                $reference = $this->reference ?? [];
                $reference['reference_text'] = $value;
                $this->reference = $reference;

                return ['reference' =>  json_encode($reference)];
            }
        );
    }


    public function referencePage(): Attribute
    {
        return Attribute::make(
            function () {
                return data_get($this->reference, 'reference_page', '');
            },
            function ($value) {
                $reference = $this->reference ?? [];
                $reference['reference_page'] = $value;
                return ['reference' =>  json_encode($reference)];
            }
        );
    }


    public function referenceContent(): Attribute
    {
        return Attribute::make(
            function () {
                return data_get($this->reference, 'reference_content', '');
            },
            function ($value) {
                $reference = $this->reference ?? [];
                $reference['reference_content'] = $value;
                return ['reference' =>  json_encode($reference)];
            }
        );
    }
}
