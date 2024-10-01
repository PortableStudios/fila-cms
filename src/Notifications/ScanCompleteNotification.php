<?php

namespace Portable\FilaCms\Notifications;

use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Filament\Notifications\Actions\Action;

class ScanCompleteNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title('Broken link scan is complete')
            ->actions([
                Action::make('View Result')
                    ->button()
                    ->url(
                        route('filament.admin.resources.link-checks.index')
                    ),
            ])
            ->getDatabaseMessage();
    }
}
