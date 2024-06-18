<?php

namespace Portable\FilaCms\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Portable\FilaCms\Models\FormEntry;

class FormSubmittedSenderNotification extends Notification
{
    /**
     * Create a notification instance.
     *
     * @param FormEntry  $formEntry
     * @return void
     */
    public function __construct(public FormEntry $formEntry)
    {
    }

    /**
     * Get the notification's channels.
     *
     * @param  mixed  $notifiable
     * @return array|string
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return $this->buildMailMessage();
    }

    /**
     * Get the reset password notification mail message for the given URL.
     *
     * @param  string  $url
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    protected function buildMailMessage()
    {
        return (new MailMessage())
            ->subject('Form ' . $this->formEntry->form->title . ' submission copy')
            ->view('fila-cms::notifications.form-submitted-sender-copy', ['entry' => $this->formEntry]);
    }

    public function shouldSend($notifiable)
    {
        return !empty($notifiable->email) && filter_var($notifiable->email, FILTER_VALIDATE_EMAIL);
    }
}
