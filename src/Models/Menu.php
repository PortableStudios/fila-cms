<?php

namespace Portable\FilaCms\Models;

use Illuminate\Database\Eloquent\Model;
use Overtrue\LaravelVersionable\Versionable;
use Overtrue\LaravelVersionable\VersionStrategy;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menu extends Model
{
    use Versionable;
    use SoftDeletes;

    protected $versionStrategy = VersionStrategy::SNAPSHOT;

    protected $versionable = [
        'name',
        'note',
    ];

    protected $fillable = [
        'name',
        'type',
    ];
    
    public function items()
    {
        return $this->hasMany(MenuItem::class, 'menu_id');
    }
}