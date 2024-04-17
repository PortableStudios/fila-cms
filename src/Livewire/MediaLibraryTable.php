<?php

namespace Portable\FilaCms\Livewire;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Portable\FilaCms\Models\Media;

class MediaLibraryTable extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    public $current_parent;

    public function table(Table $table): Table
    {
        return $table
            ->query(Media::query())
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
            ->filters([
                // ...
            ])
            ->actions([

            ])
            ->bulkActions([
                // ...
            ]);
    }

    public function render(): View
    {
        return view('fila-cms::livewire.media-library-table');
    }
}
