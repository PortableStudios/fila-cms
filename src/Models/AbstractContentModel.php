<?php

namespace Portable\FilaCms\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Kenepa\ResourceLock\Models\Concerns\HasLocks;
use Overtrue\LaravelVersionable\Versionable;
use Overtrue\LaravelVersionable\VersionStrategy;
use Portable\FilaCms\Contracts\HasSlug;
use Portable\FilaCms\Events\ContentCreating;
use Portable\FilaCms\Events\ContentUpdating;
use Portable\FilaCms\Exceptions\InvalidStatusException;
use Portable\FilaCms\Facades\FilaCms;
use Portable\FilaCms\Filament\Traits\HasAuthors;
use Portable\FilaCms\Filament\Traits\HasContentRoles;
use Portable\FilaCms\Filament\Traits\HasExcerpt;
use Portable\FilaCms\Filament\Traits\HasShortUrl;
use Portable\FilaCms\Filament\Traits\HasTaxonomies;
use Portable\FilaCms\Models\Scopes\PublishedScope;
use Portable\FilaCms\Models\Traits\ProvidesSearchSettings;
use Portable\FilaCms\Models\Traits\Searchable;
use Portable\FilaCms\Versionable\FilaCmsVersion;
use RalphJSmit\Laravel\SEO\Support\HasSEO;
use RalphJSmit\Laravel\SEO\Support\SEOData;

abstract class AbstractContentModel extends Model
{
    use HasExcerpt;
    use HasTaxonomies;
    use HasAuthors;
    use HasShortUrl;
    use Versionable;
    use SoftDeletes;
    use HasLocks;
    use HasSEO;
    use HasContentRoles;
    use HasSlug;
    use Searchable;
    use ProvidesSearchSettings;

    protected $table = 'contents';

    protected $versionStrategy = VersionStrategy::SNAPSHOT;

    protected ?string $descriptionField = null;

    // This is required to handle TipTap content
    public string $versionModel = FilaCmsVersion::class;

    protected $versionable = [
        'title',
        'slug',
        'is_draft',
        'publish_at',
        'expire_at',
        'contents',
    ];

    protected $fillable = [
        'title',
        'slug',
        'is_draft',
        'publish_at',
        'expire_at',
        'contents',
        'created_user_id',
        'updated_user_id',
    ];

    protected $appends = ['status'];

    protected $casts = [
        'publish_at'    => 'datetime',
        'expire_at'     => 'datetime',
        'contents'      => 'json',
    ];

    protected $dispatchesEvents = [
        'creating' => ContentCreating::class,
        'updating' => ContentUpdating::class,
    ];

    public static function getResourceName()
    {
        return static::$resourceName;
    }

    public function getVersionUserId()
    {
        return auth()->user() ? auth()->user()->id : FilaCms::systemUser()->id;
    }

    public function shortDescription($length = 50, $omission = '...'): string
    {
        foreach ($this->contents['content'] as $key => $value) {
            if ($value['type'] === 'paragraph') {
                return Str::of($value['content'][0]['text'])->take($length) . $omission;
            }
        }

        return '...';
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function booting(): void
    {
        static::addGlobalScope(new PublishedScope());
    }

    public function getDynamicSEOData(): SEOData
    {
        return new SEOData(
            title: $this->title,
            description: $this->descriptionField,
            author: $this->display_author,
        );
    }

    public function createdBy()
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'created_user_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'updated_user_id');
    }

    public function displayAuthor(): Attribute
    {
        return Attribute::make(get: fn () => $this->authors->count() ? implode(", ", $this->authors->pluck('display_name')->all()) : $this->createdBy->name);
    }

    protected function status(): Attribute
    {
        if ($this->deleted_at) {
            return Attribute::make(get: fn () => 'Deleted');
        }

        if ($this->is_draft) {
            return Attribute::make(get: fn () => 'Draft');
        } else {
            if ($this->publish_at?->isFuture()) {
                if ($this->expire_at?->isFuture() || $this->expire_at === null) {
                    return Attribute::make(get: fn () => 'Pending');
                }
            } else {
                // publish_at in past
                if ($this->expire_at?->isFuture() || $this->expire_at === null) {
                    return Attribute::make(get: fn () => 'Published');
                }
                if ($this->expire_at?->isPast()) {
                    return Attribute::make(get: fn () => 'Expired');
                }
            }
        }
        throw new InvalidStatusException('Content condition does not satisfy any status');
    }

    public function scopeWithPublished(Builder $query): void
    {
        $query->where(function ($q1) {
            // apply published condition
            $q1->where('is_draft', false)
                ->where(function ($q2) {
                    $q2->whereNull('publish_at')
                        ->orWhere('publish_at', '<', now());
                })
                ->where(function ($q2) {
                    $q2->whereNull('expire_at')
                        ->orWhere('expire_at', '>', now());
                });
        });
    }

    public function scopeWithDrafts(Builder $query): void
    {
        $query->withAllStatuses()->where(function ($q1) {
            $q1->where('is_draft', true);
        })->orWhere->withPublished();
    }

    public function scopeWithPending(Builder $query): void
    {
        $query->withAllStatuses()
            ->where(function ($q1) {
                $q1->where('is_draft', false)
                    ->where('publish_at', '>', now())
                    ->where(function ($q2) {
                        $q2->whereNull('expire_at')
                            ->orWhere('expire_at', '>', now());
                    });
            })
            ->orWhere->withPublished();
    }

    public function scopeWithExpired(Builder $query): void
    {
        $query->withAllStatuses()
            ->where(function ($q) {
                $q->where('is_draft', false)
                    ->where('publish_at', '<', now())
                    ->where('expire_at', '<', now());
            })
            ->orWhere->withPublished();
    }

    public function scopeWithAllStatuses(Builder $query): void
    {
        $query->withoutGlobalScope(
            PublishedScope::class,
        );
    }

    public function url(): Attribute
    {
        return new Attribute(function () {
            $resource = static::$resourceName;
            if ($resource::getFrontendRoutePrefix() == '') {
                return '/' . $this->slug;
            } else {
                return route(static::$resourceName::getFrontendShowRoute(), $this->slug);
            }
        });
    }
}
