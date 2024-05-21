<?php

namespace Portable\FilaCms\Notifications;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification
{
    public string $appName;

    public function __construct(public Authenticatable $user)
    {
        $this->appName = config('app.name');
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return $this->buildMailMessage();
    }


    protected function buildMailMessage()
    {
        return (new MailMessage())
            ->subject("Welcome to $this->appName")
            ->view('fila-cms::notifications.welcome', ['user' => $this->user, 'appName' => $this->appName]);
    }
}
