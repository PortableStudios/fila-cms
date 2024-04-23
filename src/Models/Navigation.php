<?php

namespace Portable\FilaCms\Models;

use Illuminate\Database\Eloquent\Model;
use Overtrue\LaravelVersionable\Versionable;
use Overtrue\LaravelVersionable\VersionStrategy;
use Illuminate\Database\Eloquent\SoftDeletes;

class Navigation extends Model
{
    use Versionable;
    use SoftDeletes;

    protected $versionStrategy = VersionStrategy::SNAPSHOT;

    protected $versionable = [
        'name',
        'type',
        'reference',
        'parent_id',
    ];

    protected $fillable = [
        'name',
        'type',
        'reference',
        'parent_id',
    ];

    protected $casts = [
        'reference' => 'array',
    ];

    public function parent()
    {
        return $this->belongsTo(Navigation::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Navigation::class, 'parent_id');
    }

    public function getResource()
    {

    }

    public function getContent()
    {

    }
}
