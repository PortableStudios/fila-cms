<?php

namespace Portable\FilaCms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShortUrl extends Model
{
    use HasFactory;

    protected $table = 'short_urls';

    protected $fillable = ['url', 'shortable_id', 'shortable_type'];

    public function shortable()
    {
        return $this->morphTo();
    }
}
