<?php

namespace Portable\FilaCms\Models;

use Illuminate\Database\Eloquent\Model;
use \Venturecraft\Revisionable\RevisionableTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

abstract class AbstractContentResource extends Model
{
    use RevisionableTrait, SoftDeletes;

    protected $revisionForceDeleteEnabled = true;
    protected $revisionEnabled = true;

    protected $dates = [
        'publish_at',
        'expire_at',
        'created_at',
        'updated_at',
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
        'author_id',
    ];

    protected $appends = [ 'status' ];

    public function author()
    {
        return $this->belongsTo('App/Models/User', 'author_id');
    }

    public function createdBy()
    {
        return $this->belongsTo('App/Models/User', 'created_user_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo('App/Models/User', 'updated_user_id');
    }

    protected function status(): Attribute
    {
        if ($this->is_draft) {
            return 'Draft';
        } else {
            if ($this->publish_at->isFuture()) {
                if ($this->expire_at->isFuture() || $this->expire_at === NULL) {
                    return 'Pending';
                }
            } else {
                // publish_at in past
                if ($this->expire_at->isFuture() || $this->expire_at === NULL) {
                    return 'Published';
                }
                if ($this->expire_at->isPast()) {
                    return 'Expired';
                }
            }
        }
        throw new \ErrorException('Content condition does not satisfy any status');
    }
}