<?php

namespace Portable\FilaCms\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Portable\FilaCms\Filament\FormBlocks\FormBuilder;
use Portable\FilaCms\Notifications\FormSubmittedNotification;
use Portable\FilaCms\Notifications\FormSubmittedSenderNotification;

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

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            auth()->user()?->notify(new FormSubmittedSenderNotification($model));
            $model->form->notify(new FormSubmittedNotification($model));
        });
    }

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function displayHtml(): Attribute
    {
        return new Attribute(function () {
            return FormBuilder::getDisplayFields($this->fields, $this->values);
        });
    }
}
