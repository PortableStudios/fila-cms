<?php

namespace Portable\FilaCms\Models;

use Illuminate\Database\Eloquent\Model;
use Portable\FilaCms\Models\Author;
use App\Models\User;

use Venturecraft\Revisionable\RevisionableTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

use Portable\FilaCms\Events\ContentCreating;
use Portable\FilaCms\Events\ContentUpdating;

use Portable\FilaCms\Models\Scopes\PublishedScope;

abstract class AbstractContentResource extends Model
{
    use RevisionableTrait, SoftDeletes;

    protected $table = 'contents';

    protected $revisionForceDeleteEnabled = true;
    protected $revisionEnabled = true;

    protected $fillable = [
        'title',
        'slug',
        'is_draft',
        'publish_at',
        'expire_at',
        'contents',
        'created_user_id',
        'updated_user_id',
        'author_id',
    ];

    protected $appends = [ 'status' ];

    protected $casts = [
        'publish_at'    => 'datetime',
        'expire_at'     => 'datetime',
    ];

    protected $dispatchesEvents = [
        'creating'  => ContentCreating::class,
        'updating'  => ContentUpdating::class,
    ];

    protected static function booting(): void
    {
        static::addGlobalScope(new PublishedScope);
    }

    public function author()
    {
        return $this->belongsTo(Author::class, 'author_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_user_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_user_id');
    }

    protected function status(): Attribute
    {
        if ($this->is_draft) {
            return Attribute::make(get: fn() => 'Draft');
        } else {
            if ($this->publish_at?->isFuture()) {
                if ($this->expire_at?->isFuture() || $this->expire_at === NULL) {
                    return Attribute::make(get: fn() => 'Pending');
                }
            } else {
                // publish_at in past
                if ($this->expire_at?->isFuture() || $this->expire_at === NULL) {
                    return Attribute::make(get: fn() => 'Published');
                }
                if ($this->expire_at?->isPast()) {
                    return Attribute::make(get: fn() => 'Expired');
                }
            }
        }
        throw new \ErrorException('Content condition does not satisfy any status');
    }

    public function scopeWithPublished(Builder $query): void
    {
        $query->where(function($q1) {
            // apply published condition
            $q1->where('is_draft', FALSE)
                ->where('publish_at', '<', now())
                ->where(function($q2) {
                    $q2->whereNull('expire_at')
                        ->orWhere('expire_at', '>', now());
                });
        });
    }

    public function scopeWithDrafts(Builder $query): void
    {
        $query->withAllStatuses()->where(function($q1) {
            $q1->where('is_draft', TRUE);
        })
        ->orWhere->withPublished();
    }

    public function scopeWithPending(Builder $query): void
    {
        $query->withAllStatuses()
            ->where(function($q1) {
                $q1->where('is_draft', FALSE)
                    ->where('publish_at', '>', now())
                    ->where(function($q2) {
                        $q2->whereNull('expire_at')
                            ->orWhere('expire_at', '>', now());
                    });
            })    
            ->orWhere->withPublished();
    }
    
    public function scopeWithExpired(Builder $query): void
    {
        $query->withAllStatuses()
            ->where(function($q) {
                $q1->where('is_draft', FALSE)
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
}