<?php

namespace Portable\FilaCms\Filament\Actions;

use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\TextInput;
use FilamentTiptapEditor\TiptapEditor;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Portable\FilaCms\Filament\Forms\Components\MediaTable;

class MediaAction extends Action
{
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
            'title' => '',
            'width' => '',
            'height' => '',
        ])
            ->modalWidth('6xl')
            ->mountUsing(function (TiptapEditor $component, ComponentContainer $form, array $arguments) {
                $source = $arguments['src'] !== ''
                    ? $component->getDirectory().Str::of($arguments['src'])
                        ->after($component->getDirectory())
                    : null;

                $form->fill([
                    'src' => $source,
                    'alt' => $arguments['alt'] ?? '',
                    'title' => $arguments['title'] ?? '',
                    'width' => $arguments['width'] ?? '',
                    'height' => $arguments['height'] ?? '',
                ]);
            })->modalHeading(function (TiptapEditor $component, array $arguments) {
                $context = blank($arguments['src'] ?? null) ? 'insert' : 'update';

                return __('filament-tiptap-editor::media-modal.heading.'.$context);
            })->form(function (TiptapEditor $component) {
                return $this->getFormFields($component);
            })->action(function (TiptapEditor $component, $data) {
                if (config('filament-tiptap-editor.use_relative_paths')) {
                    $source = strlen($data['src']) > 0 ? Str::of($data['src']) : Str::of($data['existing-src']);
                    $source = $source
                        ->replace(config('app.url'), '')
                        ->ltrim('/')
                        ->prepend('/');
                } else {
                    $source = str_starts_with($data['src'], 'http')
                        ? $data['src']
                        : Storage::disk(config('filament-tiptap-editor.disk'))->url($data['src']);
                }

                $component->getLivewire()->dispatch(
                    'insert-content',
                    type: 'media',
                    statePath: $component->getStatePath(),
                    media: [
                        'src' => $source,
                        'alt' => $data['alt'] ?? null,
                        'title' => $data['title'],
                        'width' => $data['width'],
                        'height' => $data['height'],
                        'link_text' => $data['link_text'] ?? null,
                    ],
                );
            });
    }

    protected function getFileUploadField($component): FileUpload
    {
        return FileUpload::make('src')
            ->label(__('filament-tiptap-editor::media-modal.labels.file'))
            ->disk($component->getDisk())
            ->directory($component->getDirectory())
            ->visibility(config('filament-tiptap-editor.visibility'))
            ->preserveFilenames(config('filament-tiptap-editor.preserve_file_names'))
            ->acceptedFileTypes($component->getAcceptedFileTypes())
            ->maxFiles(1)
            ->maxSize($component->getMaxFileSize())
            ->imageResizeMode(config('filament-tiptap-editor.image_resize_mode'))
            ->imageCropAspectRatio(config('filament-tiptap-editor.image_crop_aspect_ratio'))
            ->imageResizeTargetWidth(config('filament-tiptap-editor.image_resize_target_width'))
            ->imageResizeTargetHeight(config('filament-tiptap-editor.image_resize_target_height'))
            ->live()
            ->afterStateUpdated(function (TemporaryUploadedFile $state, callable $set) {
                if (Str::contains($state->getMimeType(), 'image')) {
                    $set('type', 'image');
                } else {
                    $set('type', 'document');
                }
            })
            ->saveUploadedFileUsing(function (BaseFileUpload $component, TemporaryUploadedFile $file, callable $set, callable $get) {
                $type = $get('type');
                $filename = $component->shouldPreserveFilenames() ? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) : Str::uuid();
                $storeMethod = $component->getVisibility() === 'public' ? 'storePubliclyAs' : 'storeAs';
                $extension = $file->getClientOriginalExtension();

                if (Storage::disk($component->getDiskName())->exists(ltrim($component->getDirectory().'/'.$filename.'.'.$extension, '/'))) {
                    $filename = $filename.'-'.time();
                }

                if ($dimensions = $file->dimensions()) {
                    $set('width', $dimensions[0]);
                    $set('height', $dimensions[1]);
                }

                $upload = $file->{$storeMethod}($component->getDirectory(), $filename.'.'.$extension, $component->getDiskName());

                return Storage::disk($component->getDiskName())->url($upload);
            });
    }

    protected function getFormFields($component)
    {
        return [
            $this->getFileUploadField($component),
            Split::make([
                Section::make([ MediaTable::make('media') ]),
                Section::make([
                    TextInput::make('link_text')
                        ->label(__('filament-tiptap-editor::media-modal.labels.link_text'))
                        ->required()
                        ->visible(fn (callable $get) => $get('type') == 'document'),
                    TextInput::make('alt')
                        ->label(__('filament-tiptap-editor::media-modal.labels.alt'))
                        ->hidden(fn (callable $get) => $get('type') == 'document')
                        ->hintAction(
                            Action::make('alt_hint_action')
                                ->label('?')
                                ->color('primary')
                                ->url('https://www.w3.org/WAI/tutorials/images/decision-tree', true)
                        ),
                    TextInput::make('title')
                        ->label(__('filament-tiptap-editor::media-modal.labels.title')),
                    Hidden::make('width'),
                    Hidden::make('height'),
                    Hidden::make('type')
                        ->default('document'),
                ])->grow(false),
            ]),
        ];
    }
}
