<?php

namespace Portable\FilaCms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShortUrl extends Model
{
    use HasFactory;

    protected $table = 'short_urls';

    protected $fillable = ['url', 'shortable_id', 'shortable_type', 'redirect_status', 'enable', 'hits'];

    protected $casts = [
        'enable' => 'boolean',
    ];

    protected $attributes = [
        'enable' => true,
        'redirect_status' => 301,
        'hits' => 0,
    ];

    public function shortable()
    {
        return $this->morphTo();
    }
}
