<?php

namespace Portable\FilaCms\Livewire;

use Closure;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Get;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Livewire\Component;
use Portable\FilaCms\Filament\Tables\Columns\ThumbnailColumn;
use Portable\FilaCms\Models\Media;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class MediaLibraryTable extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    public $current_parent;
    public $current_file;
    public $jsKey;
    public $cookieKey = 'media_library_parent';

    public function mount($jsKey = null)
    {
        $this->jsKey = $jsKey ?: $this->id();

        $this->setParent($_COOKIE[$this->cookieKey] ?? null);
    }

    public function breadcrumbs()
    {
        $paths = [];

        if ($this->current_parent) {
            $currentItem = Media::find($this->current_parent);
            $key = $currentItem->id;
            $paths['id-' . $key] = $currentItem->filename;

            while ($currentItem = Media::find($currentItem->parent_id)) {
                $key = $currentItem->id;
                $paths['id-' . $key] = $currentItem->filename;
            }
        }
        $key = '';
        $paths[$key] = 'Root';
        return array_reverse($paths);
    }

    public function setParent($id)
    {
        if(!Media::find($id)) {
            $id = null;
        }

        $this->current_parent = Str::replace('id-', '', $id);
        $this->current_file = null;
        $this->dispatch('media-file-selected', ['id' => null, 'jsKey' => $this->jsKey ]);
        $this->dispatch('set-current-parent-cookie', $id);

    }

    public function setFile($id)
    {
        $this->current_file = $id;
        $this->dispatch('media-file-selected', ['id' => $id, 'jsKey' => $this->jsKey ]);
    }

    public function table(Table $table): Table
    {
        $query = Media::query();

        return $table
            ->recordClasses(function (Media $media) {
                return $media->id == $this->current_file ? ['bg-gray-100'] : '';
            })
            ->headerActions([
                $this->getUploadAction(),
                $this->getNewFolderAction(),
            ])
            ->query($query)
            ->columns($this->getTableColumns())
            ->actions(
                [
                    $this->getEditAction(),
                    $this->getViewAction(),
                    $this->getDeleteAction(),

                ]
            );
    }

    public function render(): View
    {
        return view('fila-cms::admin.livewire.media-library-table');
    }

    protected function getDeleteAction(): Action
    {
        return Action::make('delete')->requiresConfirmation()
            ->action(function (Media $media) {
                $media->delete();
                if ($this->current_file == $media->id) {
                    $this->setFile(null);
                }
            })
            ->label('Delete')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->visible(function () {
                return ($this->current_parent || config('fila-cms.media_library.allow_root_uploads'));
            });
    }

    protected function getViewAction(): Action
    {
        return Action::make('view')
            ->label('View')
            ->icon('heroicon-o-eye')
            ->form(function (Media $media) {
                return [
                Split::make([

                    Placeholder::make('url')->label('')
                    ->content($this->getMediaPreviewHtml($media, 'large')),
                    Group::make([
                        Placeholder::make('mediaModel.filename')
                            ->content(function () use ($media) {
                                return new HtmlString('<strong>' . $media->filename . '</strong><br>' . $media->displaySize);
                            })
                            ->hiddenLabel(true),
                        Placeholder::make('mediaModel.alt_text')
                            ->content(function () use ($media) {
                                return $media->alt_text;
                            })
                            ->inlineLabel(true)
                            ->label('Alt Text'),
                        Placeholder::make('mediaModel.uploaded_by')
                            ->content(function () use ($media) {
                                return $media->uploaded_user?->name;
                            })
                            ->inlineLabel(true)
                            ->label('Uploaded by'),
                        Placeholder::make('mediaModel.uploaded_at')
                            ->content(function () use ($media) {
                                return $media->created_at->toFormattedDateString();
                            })
                            ->inlineLabel(true)
                            ->label('Uploaded'),
                        Placeholder::make('mediaModel.modified')
                            ->content(function () use ($media) {
                                return $media->updated_at->toFormattedDateString();
                            })
                            ->inlineLabel(true)
                            ->label('Modified'),
                        Placeholder::make('mediaModel.dimensions')
                            ->content(function () use ($media) {
                                return $media->width . 'x' . $media->height;
                            })
                            ->inlineLabel(true)
                            ->label('Dimensions'),
                        Placeholder::make('mediaModel.ID')
                            ->content(function () use ($media) {
                                return $media->id;
                            })
                            ->inlineLabel(true)
                            ->label('ID'),
                    ])
                ])
                ];
            })
            ->slideover()
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Close')
            ->hidden(function (Media $media) {
                return $media->is_folder;
            });
    }

    /**
     * Return the action for creating a new folder
     */
    protected function getNewFolderAction(): Action
    {
        $action = Action::make('create_folder')
                ->form([
                    TextInput::make('folder_name')
                        ->label('Folder Name')
                        ->required()
                ])
                ->action(function (array $data) {
                    $currentItem = $this->current_parent ? Media::find($this->current_parent) : null;
                    $disk = $currentItem ? $currentItem->disk : config('filesystems.default');
                    $path = $currentItem ? $currentItem->filepath : '';
                    $folderName = $data['folder_name'];

                    $storage = Storage::disk($disk);
                    $storage->createDirectory($path . '/' . $folderName);


                    $media = Media::create([
                        'filename' => $folderName,
                        'filepath' => $path . '/' . $folderName,
                        'disk' => $disk,
                        'is_folder' => true,
                        'parent_id' => $this->current_parent,
                    ]);
                })
                ->disabled(function () {
                    return !($this->current_parent || config('fila-cms.media_library.allow_root_uploads'));
                })
                ->label("Create folder")
                ->slideover()
                ->color('info')
                ->icon('heroicon-m-folder-plus');

        return $action;
    }

    /**
     * Return the action for uploading a file
     */
    protected function getUploadAction(): Action
    {
        $action = Action::make('upload')
            ->form([
                FileUpload::make('upload_media')
                    ->label('Upload File')
                    ->storeFiles(false)
                    ->multiple()
                    ->required(),
                TextInput::make('alt_text')
                    ->label('Alt Text')
                    ->required()

            ])
            ->action(function (array $data) {
                if(!is_array($data['upload_media'])) {
                    $this->saveFile($data['upload_media'], $data['alt_text']);
                    return;
                }
                if(count($data['upload_media'])) {
                    foreach($data['upload_media'] as $item) {
                        $this->saveFile($item, $data['alt_text']);
                    }
                }
            })
            ->disabled(function () {
                return !($this->current_parent || config('fila-cms.media_library.allow_root_uploads'));
            })
            ->label("Upload media")
            ->slideover()
            ->color('primary')
            ->icon('heroicon-m-arrow-up-tray');

        return $action;
    }

    protected function saveFile(TemporaryUploadedFile $uploadedFile, string $altText): Media
    {
        $currentItem = $this->current_parent ? Media::find($this->current_parent) : null;
        $disk = $currentItem ? $currentItem->disk : config('filesystems.default');
        $path = $currentItem ? $currentItem->filepath : '';
        $filename = $this->uniqueFilename($disk, $path, $uploadedFile->getClientOriginalName());

        $uploadedFile->storeAs($path, $filename, [
            'disk' => $disk,
        ]);

        $image = getimagesize(Storage::disk($disk)->path($path . '/' . $filename));
        if (is_array($image)) {
            $width = $image[0];
            $height = $image[1];
        } else {
            $width = $height = 0;
        }

        return Media::create([
            'filename' => $filename,
            'filepath' => $path,
            'disk' => $disk,
            'alt_text' => $altText,
            'size' => $uploadedFile->getSize(),
            'extension' => $uploadedFile->getClientOriginalExtension(),
            'mime_type' => mime_content_type(Storage::disk($disk)->path($path . '/' . $filename)),
            'width' => $width,
            'height' => $height,
            'is_folder' => false,
            'parent_id' => $this->current_parent,
        ]);
    }

    /**
     * Return an HtmlString showing the preview of the media item
     */
    protected function getMediaPreviewHtml(Media $media, $size = 'medium'): HtmlString
    {
        return new HtmlString('<img class="mx-auto" src="' . route('media.thumbnail.' . $size, $media) . '" alt="File Preview" />');
    }

    /**
     * Apply the search query to the table query.
     * Only apply a folder restriction if we're not searching
     */
    protected function applySearchToTableQuery(Builder $query): Builder
    {
        if (filled($searchQuery = $this->getTableSearch())) {
            $searchQuery = '%' . $searchQuery . '%';
            $query->where('filename', 'LIKE', $searchQuery)
                ->orWhere('alt_text', 'LIKE', $searchQuery);
        } elseif ($this->current_parent) {
            $query->where('parent_id', $this->current_parent);
        } else {
            $query->whereNull('parent_id');
        }

        return $query;
    }

    /**
     * Build the columsn for the table
     */
    protected function getTableColumns(): array
    {
        return [
            ThumbnailColumn::make('filepath')
                ->width('40px')
                ->label(''),
            TextColumn::make('filename')
                ->action(function (Media $media): void {
                    if ($media->is_folder) {
                        $this->setParent($media->id);
                    } else {
                        $this->setFile($media->id);
                    }
                })
                ->label('Name')
                ->searchable()
                ->sortable(),
            TextColumn::make('alt_text')
                ->label('Alt Text')
                ->searchable()
                ->sortable(),
             // Using the ID because we need a field with guaranteed content for formatStateUsing to be called
            TextColumn::make('id')
                ->label('Size')
                ->badge()
                ->sortable()
                ->formatStateUsing(function ($state, $record) {
                    return $record->display_size;
                })
                ->color(function ($state, $record) {
                    if ($record->is_folder) {
                        return $record->children->count() > 0 ? 'info' : 'gray';
                    } else {
                        return 'white';
                    }
                }),
            TextColumn::make('updated_at')->label('Modified')->sortable()
        ];
    }

    /**
     * Generate a unique filename for the file within a given path, based on the original name
     */
    protected function uniqueFilename($disk, $path, $name): string
    {
        $storage = Storage::disk($disk);
        $filename = $name;
        $i = 1;
        while ($storage->exists($path . '/' . $filename)) {
            $filename = pathinfo($name, PATHINFO_FILENAME) . '-' . $i . '.' . pathinfo($name, PATHINFO_EXTENSION);
            $i++;
        }
        return $filename;
    }

    protected function getEditAction(): Action
    {
        return Action::make('edit')
                    ->slideover()
                    ->label('Edit')
                    ->icon('heroicon-o-pencil')
                    ->fillForm(function (Media $media) {
                        return [
                            'id' => $media->id,
                            'filename' => $media->filename,
                            'alt_text' => $media->alt_text,
                        ];
                    })
                    ->form(function (Media $media) {
                        return $this->getEditForm($media);
                    })
                    ->action(function (Media $media, $data): void {
                        if ($media->filename !== $data['filename']) {
                            $media->move($media->currentParent, $data['filename']);
                        }
                        $media->update([ 'alt_text' => isset($data['alt_text']) ? $data['alt_text'] : '']);
                    })
                    ->visible(function (Media $media) {
                        return ($this->current_parent || config('fila-cms.media_library.allow_root_uploads'));
                    });
    }

    protected function getEditForm(Media $media): array
    {
        return [
            Split::make([
                Placeholder::make('url')->label('')
                    ->content($this->getMediaPreviewHtml($media))
                    ->visible(function () use ($media) {
                        return !$media->is_folder;
                    }),
                Section::make()->schema([
                    Hidden::make('id'),
                    TextInput::make('filename')
                        ->label('Name')
                        ->required()
                        ->rules([
                            function (Get $get) {
                                return function (string $attribute, $value, Closure $fail) use ($get) {
                                    $media = Media::find($get('id'));
                                    if ($value == $media->filename) {
                                        return true;
                                    }
                                    if (Media::where('filename', $value)->where('parent_id', $media->parent_id)->where('id', '<>', $media->id)->exists()) {
                                        $fail('There is already a file called ' . $value . ' in this folder');
                                    }
                                };
                            }
                        ]),
                    TextInput::make('alt_text')
                        ->label('Alt Text')
                        ->required()
                        ->visible(function () use ($media) {
                            return !$media->is_folder;
                        }),
                ])
            ])
        ];
    }
}
