<?php

namespace Portable\FilaCms\Models;

use Illuminate\Database\Eloquent\Model;

class Authorable extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'author_id',
        'authorable_id',
        'authorable_type',
    ];

    public function author()
    {
        return $this->belongsTo(Author::class, 'author_id');
    }
}
