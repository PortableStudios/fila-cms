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
        /** 
         * Some of the implementation uses "newEvent" (training, for example)
         * While others somehow breaks as they do not use this key
         * Instead of finding all the areas and align them on one format to another
         * I believe it's better to just accommodate both use cases for backward and forward compatibility
         */
        return new Attribute(function () {
            return FormBuilder::getDisplayFields($this->fields, $this->values['newEvent'] ?? $this->values);
        });
    }
}
