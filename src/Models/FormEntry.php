<?php

namespace Portable\FilaCms\Models;

use Illuminate\Database\Eloquent\Model;

class FormEntry extends Model
{
    protected $fillable = [
        'status',
        'form_id',
        'user_id',
        'fields',
        'values'
    ];

    protected $casts = [
        'values' => 'json',
        'fields' => 'json'
    ];
}
