<?php

namespace Portable\FilaCms\Models;

use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Portable\FilaCms\Contracts\HasSlug;
use Portable\FilaCms\Facades\FilaCms;
use Portable\FilaCms\Filament\Resources\FormResource;

class Form extends Model
{
    use HasSlug;
    use HasFactory;
    use Notifiable;
    use Searchable;

    protected static $resourceName = FormResource::class;

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

    public function url(): Attribute
    {
        return new Attribute(function () {
            return route(self::$resourceName::getRoutePrefix() . '.{slug}', $this->slug);
        });
    }
}
