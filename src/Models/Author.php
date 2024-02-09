<?php

namespace Portable\FilaCms\Models;

use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'is_individual',
    ];

    protected $casts = [
        'is_individual' => 'boolean',
    ];

    public function getDisplayNameAttribute()
    {
        if ($this->is_indiviudal) {
            return $this->first_name;
        }
        return $this->first_name . ' ' . $this->last_name;
    }
}
