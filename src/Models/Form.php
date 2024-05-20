<?php

namespace Portable\FilaCms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Scout\Searchable;
use Portable\FilaCms\Contracts\HasSlug;
use Portable\FilaCms\Facades\FilaCms;

class Form extends Model
{
    use HasSlug;
    use HasFactory;
    use Notifiable;
    use Searchable;

    protected $fillable = [
        'title',
        'slug',
        'only_for_logged_in',
        'confirmation_title',
        'notification_emails',
        'confirmation_text',
        'user_id',
        'fields'
    ];

    protected $casts = [
        'fields' => 'json',
        'confirmation_text' => 'json',
        'notification_emails' => 'json',
        'only_for_logged_in' => 'boolean',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($form) {
            $form->user_id = auth()->user() ? auth()->user()->id : FilaCms::systemUser()->id;
        });
    }

    public function entries()
    {
        return $this->hasMany(FormEntry::class);
    }

    public function user()
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    public function routeNotificationForMail($notification)
    {
        return collect($this->notification_emails)->pluck('email')->toArray();

    }
}
