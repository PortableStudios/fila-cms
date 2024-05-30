<?php

namespace Portable\FilaCms\Filament\Actions;

use Filament\Actions\StaticAction;
use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Get;
use Filament\Forms\Set;
use FilamentTiptapEditor\TiptapEditor;
use Illuminate\Support\Str;
use Portable\FilaCms\Filament\Forms\Components\MediaTable;
use Portable\FilaCms\Models\Media;

class MediaAction extends Action
{
    protected $currentFile;

    public static function getDefaultName(): ?string
    {
        return 'filament_tiptap_media';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->arguments([
            'src' => '',
            'alt' => '',
            'width' => '',
            'height' => '',
        ])
            ->modalWidth('6xl')
            ->mountUsing(function (TiptapEditor $component, ComponentContainer $form, array $arguments) {
                $source = $arguments['src'] !== ''
                    ? $component->getDirectory() . Str::of($arguments['src'])
                        ->after($component->getDirectory())
                    : null;

                $form->fill([
                    'src' => $source,
                    'alt' => $arguments['alt'] ?? '',
                    'width' => $arguments['width'] ?? '',
                    'height' => $arguments['height'] ?? '',
                ]);
            })->modalHeading(function (TiptapEditor $component, array $arguments) {
                $context = blank($arguments['src'] ?? null) ? 'insert' : 'update';

                return __('filament-tiptap-editor::media-modal.heading.' . $context);
            })->form(function (TiptapEditor $component) {
                return $this->getFormFields($component);
            })
            ->modalSubmitActionLabel(function (Get $get) {
                return ($this->currentFile && $this->currentFile->is_image) ? 'Insert Image' : 'Select an image';
            })
            ->modalSubmitAction(function (StaticAction $action) {
                return $action->disabled(function () {
                    return !($this->currentFile && $this->currentFile->is_image);
                });
            })
            ->action(function (Action $action, TiptapEditor $component, $data, Get $get) {
                $data['mediaModel'] = Media::find($data['media']);
                if (!$data['mediaModel']) {
                    $action->cancel();
                    return;
                }
                $data['src'] = $data['mediaModel']->url;
                if (config('filament-tiptap-editor.use_relative_paths')) {
                    $source = Str::of($data['src'])
                        ->replace(url()->to('/'), '')
                        ->ltrim('/')
                        ->prepend('/');
                } else {
                    $source = $data['src'];
                }

                $component->getLivewire()->dispatch(
                    'insert-content',
                    type: 'media',
                    statePath: $component->getStatePath(),
                    media: [
                        'src' => $source,
                        'alt' => $data['mediaModel']->alt_text ?? null,
                        'width' => $data['mediaModel']->width,
                        'height' => $data['mediaModel']->height,
                    ],
                );
            });
    }

    protected function getFormFields($component)
    {

        return [
            MediaTable::make('media')->grow(true)->columnSpan(3)->afterStateUpdated(function (Set $set, ?string $state) {
                if ($state) {
                    $media = Media::find($state);
                    $set('mediaModel', $media);
                    $this->currentFile = $media;
                } else {
                    $set('mediaModel', null);
                    $this->currentFile = null;
                }
            })
        ];
    }
}
