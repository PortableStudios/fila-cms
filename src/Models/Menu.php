<?php

namespace Portable\FilaCms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Overtrue\LaravelVersionable\Versionable;
use Overtrue\LaravelVersionable\VersionStrategy;

class Menu extends Model
{
    use Versionable;
    use SoftDeletes;
    use HasFactory;

    protected $versionStrategy = VersionStrategy::SNAPSHOT;

    protected $versionable = [
        'name',
        'note',
    ];

    protected $fillable = [
        'name',
        'note',
    ];

    public function items()
    {
        return $this->hasMany(MenuItem::class, 'menu_id');
    }

    // Return only direct descendants
    public function children()
    {
        return $this->items()->whereNull('parent_id');
    }
}
