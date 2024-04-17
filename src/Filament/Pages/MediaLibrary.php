<?php

namespace Portable\FilaCms\Filament\Pages;

use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Get;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action as ActionsAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Portable\FilaCms\Models\Media;

class MediaLibrary extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Content';

    protected static string $view = 'fila-cms::pages.media-library';
    public ?array $data = [];
    public $current_parent = null;

    public function mount(): void
    {
        $this->current_parent = request()->get('parent', null);
        $this->form->fill();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('upload')
                ->form([
                    FileUpload::make('upload_media')
                        ->label('Upload File')
                        ->storeFiles(false)
                        ->required(),
                    TextInput::make('alt_text')
                        ->label('Alt Text')
                        ->required()

                ])
                ->action(function (array $data) {
                    $currentItem = $this->current_parent ? Media::find($this->current_parent) : null;
                    $disk = $currentItem ? $currentItem->disk : config('filesystems.default');
                    $path = $currentItem ? $currentItem->filepath : '';
                    $filename = $this->uniqueFilename($disk, $path, $data['upload_media']->getClientOriginalName());

                    $data['upload_media']->storeAs($path, $filename, [
                        'disk' => $disk,
                    ]);

                    $media = Media::create([
                        'filename' => $filename,
                        'filepath' => $path,
                        'disk' => $disk,
                        'alt_text' => $data['alt_text'],
                        'size' => $data['upload_media']->getSize(),
                        'is_folder' => false,
                        'parent_id' => $this->current_parent,
                    ]);
                })

                ->label("Upload media")
                ->icon('heroicon-o-arrow-up-tray')
        ];
    }

    public function uniqueFilename($disk, $path, $name)
    {
        $storage = Storage::disk($disk);
        $filename = $name;
        $i = 1;
        while($storage->exists($path . '/' . $filename)) {
            $filename = pathinfo($name, PATHINFO_FILENAME) . '-' . $i . '.' . pathinfo($name, PATHINFO_EXTENSION);
            $i++;
        }
        return $filename;
    }

    public function create()
    {

    }

    public function breadcrumbs()
    {
        $paths = [];

        if($this->current_parent) {
            $currentItem = Media::find($this->current_parent);
            $paths[route('filament.admin.pages.media-library', ['parent' => $currentItem->id])] = $currentItem->filename;

            while($currentItem = Media::find($currentItem->parent_id)) {
                $paths[route('filament.admin.pages.media-library', ['parent' => $currentItem->id])] = $currentItem->filename;
            }
        }
        $paths[route('filament.admin.pages.media-library')] = 'Home';
        return array_reverse($paths);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                return Media::query()->where('parent_id', $this->current_parent);
            })
            ->columns([
                TextColumn::make('filename')
                    ->action(function (Media $media): void {
                        if($media->is_folder) {
                            $this->current_parent = $media->id;
                        }
                    })
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('id')
                    ->label('Size')
                    ->badge()
                    ->formatStateUsing(function ($state, $record) {
                        return $record->display_size;
                    })
                    ->color(function ($state, $record) {
                        if($record->is_folder) {
                            return $record->children->count() > 0 ? 'info' : 'gray';
                        } else {
                            return 'white';
                        }
                    }),
                TextColumn::make('updated_at')->label('Modified'),
            ])
            ->actions(
                [
                    ActionsAction::make('edit')
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
                            return [
                                Split::make([
                                    Placeholder::make('url')->label('')
                                        ->content($this->getMediaPreviewHtml($media)),
                                    Section::make()->schema([
                                        Hidden::make('id'),
                                        TextInput::make('filename')
                                            ->label('Name')
                                            ->required()
                                            ->rules([
                                                function (Get $get) {
                                                    return function (string $attribute, $value, Closure $fail) use ($get) {
                                                        $media = Media::find($get('id'));
                                                        if($value == $media->filename) {
                                                            return true;
                                                        }
                                                        if(Media::where('filename', $value)->where('parent_id', $media->parent_id)->where('id', '<>', $media->id)->exists()) {
                                                            $fail('There is already a file called ' . $value . ' in this folder');
                                                        }
                                                    };
                                                }
                                            ]),
                                        TextInput::make('alt_text')
                                            ->label('Alt Text')
                                            ->required()
                                    ])

                                ])
                            ];
                        })
                        ->action(function (Media $media, $data): void {
                            if($media->filename !== $data['filename']) {
                                $media->move($media->currentParent, $data['filename']);
                            }
                            $media->update(
                                [
                                    'alt_text' => $data['alt_text']
                                ]
                            );
                        }),
                    ActionsAction::make('view')
                        ->label('View')
                        ->icon('heroicon-o-eye')
                        ->form(function (Media $media) {
                            return [
                                Placeholder::make('url')->label('')
                                ->content($this->getMediaPreviewHtml($media, 'large')),
                            ];
                        })
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Close')
                        ->hidden(function (Media $media) {
                            return $media->is_folder;
                        }),
                ]
            );
    }

    protected function getMediaPreviewHtml(Media $media, $size = 'medium')
    {
        return new HtmlString('<img class="mx-auto" src="' . route('media.thumbnail.' . $size, $media) . '" alt="File Preview" />');
    }
}
