<?php

namespace Portable\FilaCms\Filament\Resources;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Portable\FilaCms\Filament\Resources\RoleResource\Pages;
use Portable\FilaCms\Filament\Traits\IsProtectedResource;
use Spatie\Permission\Models\Role;

class RoleResource extends AbstractResource
{
    use IsProtectedResource;

    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationGroup = 'Security';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->required()->autofocus()->columnSpanFull(),
                CheckboxList::make('permissions')->relationship('permissions', 'name')->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->sortable(),
                TextColumn::make('permissions.name')
                    ->distinctList()
                    ->listWithLineBreaks()
                    ->bulleted(),
            ])
            ->searchable()
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
