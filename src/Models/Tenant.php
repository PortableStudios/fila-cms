<?php

namespace Portable\FilaCms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Overtrue\LaravelVersionable\Versionable;
use Overtrue\LaravelVersionable\VersionStrategy;

class Tenant extends Model
{
    use Versionable;
    use SoftDeletes;
    use HasFactory;

    protected $versionStrategy = VersionStrategy::SNAPSHOT;

    protected $versionable = [
        'name',
        'domain',
    ];

    protected $fillable = [
        'name',
        'domain',
    ];

    public function members()
    {
        return $this->belongsToMany(config('fila-cms.models.user'), 'tenant_members');
    }
}
