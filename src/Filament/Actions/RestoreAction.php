<?php

namespace Portable\FilaCms\Filament\Actions;

use Filament\Pages\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Support\Facades\Schema;

class RestoreAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'restore-content';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('filament-actions::restore.single.label'));

        $this->modalHeading(fn (): string => __('filament-actions::restore.single.modal.heading', ['label' => $this->getRecordTitle()]));

        $this->modalSubmitActionLabel(__('filament-actions::restore.single.modal.actions.restore.label'));

        $this->successNotificationTitle(__('filament-actions::restore.single.notifications.restored.title'));

        $this->color('gray');

        $this->icon(FilamentIcon::resolve('actions::restore-action') ?? 'heroicon-m-arrow-uturn-left');

        $this->groupedIcon(FilamentIcon::resolve('actions::restore-action.grouped') ?? 'heroicon-m-arrow-uturn-left');

        $this->requiresConfirmation();

        $this->modalIcon(FilamentIcon::resolve('actions::restore-action.modal') ?? 'heroicon-o-arrow-uturn-left');

        $this->action(function (Model $record): void {
            if (! method_exists($record, 'restore')) {
                $this->failure();
                return;
            }

            $result = $record->restore();

            if (! $result) {
                $this->failure();
                return;
            }

            if (Schema::hasColumn($record->getTable(), 'is_draft')) {
                $record->update([
                    'is_draft' => true
                ]);
            }

            $this->redirect('edit');
            $this->success();
        });

        $this->visible(static function (Model $record): bool {
            if (! method_exists($record, 'trashed')) {
                return false;
            }
            return $record->trashed();
        });
    }
}
