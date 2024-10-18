<?php

namespace Portable\FilaCms\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Overtrue\LaravelVersionable\Versionable;
use Overtrue\LaravelVersionable\VersionStrategy;
use Portable\FilaCms\Models\Traits\IsTenanted;
use Portable\FilaCms\Models\Traits\Searchable;

class Author extends Model
{
    use HasFactory;
    use Versionable;
    use SoftDeletes;
    use Searchable;
    use IsTenanted;

    protected $versionStrategy = VersionStrategy::SNAPSHOT;

    protected $versionable = [
        'first_name',
        'last_name',
        'is_individual',
    ];

    protected $fillable = [
        'first_name',
        'last_name',
        'is_individual',
    ];

    protected $casts = [
        'is_individual' => 'boolean',
    ];

    protected $appends = ['display_name'];

    public function displayName(): Attribute
    {
        return Attribute::make(
            function () {
                return $this->is_individual ? $this->first_name . ' ' . $this->last_name : $this->first_name;
            }
        );
    }
}
