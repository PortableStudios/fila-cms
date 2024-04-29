<?php

namespace Portable\FilaCms\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

use Illuminate\Support\Str;
use Portable\FilaCms\Facades\FilaCms;
use Portable\FilaCms\Filament\Resources\MenuItemResource\Pages;
use Portable\FilaCms\Filament\Traits\IsProtectedResource;
use Portable\FilaCms\Models\MenuItem;

class MenuItemResource extends AbstractResource
{
    use IsProtectedResource;

    protected static ?string $model = MenuItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        $model = $form->model;

        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->columnSpan(2),
                Forms\Components\Select::make('parent_id')
                    ->label('Parent')
                    ->columnSpan(2)
                    ->disabled(function () use ($model) {
                        // if has children, can't be a children too
                        if (gettype($model) === 'object') {
                            if ($model->children->count() > 0) {
                                return true;
                            }
                        }
                        return false;
                    })
                    ->options(function (MenuItemResource $resource) use ($model) {
                        // do not show self, if edit
                        return MenuItem::when(gettype($model) === 'object', function ($query) use ($model) {
                            $query->whereNot('id', $model->id);
                        })
                            ->whereDoesntHave('parent') // if already a children, can't be a parent
                            ->get()
                            ->pluck('name', 'id');
                    }),
                Forms\Components\TextInput::make('order')->required()->numeric()->columnSpan(2),
                Forms\Components\Select::make('type')
                    ->options([
                        'index-page' => 'Content Listing Page',
                        'content' => 'Content Detail Page',
                        'url'  => 'URL',
                    ])
                    ->default('content')
                    ->selectablePlaceholder(false)
                    ->columnSpan(2)
                    ->live()
                    ->required(),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Select::make('reference_page')
                            ->label('Content Type')
                            ->visible(fn (Get $get) => $get('type') !== 'url' ? true : false)
                            ->options(FilaCms::getContentModels())
                            ->required()
                            ->live()
                            ->columnSpan(2),
                        Forms\Components\Select::make('reference_content')
                            ->label('Content Item')
                            ->visible(fn (Get $get) => $get('type') === 'content' ? true : false)
                            ->getSearchResultsUsing(fn (string $search, Get $get, MenuItemResource $resource): array => $resource->getContents($search, $get))
                            ->getOptionLabelUsing(fn (string $value, MenuItemResource $resource, Get $get): ?string => ($resource->getSourceModel($get('reference_page')))->select('id', 'title')->where('id', $value)->first()?->title)
                            ->required()
                            ->searchable()
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('reference_text')
                            ->visible(fn (Get $get) => $get('type') === 'url' ? true : false)
                            ->label('URL')
                            ->columnSpan(2)
                            ->required(),
                    ])
                    ->columnSpan(4)
                    ->columns(4),
            ])
            ->columns(4);
    }

    public static function getModel(): string
    {
        return MenuItem::class;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->sortable(),
                TextColumn::make('type')->label('Type'),
                TextColumn::make('parent.name')->label('Parent'),
            ])
            ->filters([

            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])->defaultSort('order');
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    public function getContents(string $search, $get): array
    {
        $data = [];
        $models = null;
        $source = $get('reference_page');

        $models = ($this->getSourceModel($source))
            ->select('id', 'title')
            ->where(function ($q) use ($search) {
                $q->where('contents', 'LIKE', '%' . $search . '%')
                ->orWhere('title', 'LIKE', '%' . $search . '%');
            })
            ->get();

        foreach ($models as $key => $model) {
            $data[$model->id] = $model->title;
        }

        return $data;
    }

    protected function getSources()
    {
        $sources = FilaCms::getContentModels();
        $list = [];

        foreach ($sources as $key => $source) {
            $list[Str::lower($source)] = $source;
        }

        return $list;
    }

    protected function getSourceModel($source)
    {
        $className = FilaCms::getModelFromResource($source);

        return new $className();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMenuItems::route('/'),
            'create' => Pages\CreateMenuItem::route('/create'),
            'edit' => Pages\EditMenuItem::route('/{record}/edit'),
        ];
    }
}
