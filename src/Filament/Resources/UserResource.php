<?php

namespace Portable\FilaCms\Filament\Resources;

use App\Models\User;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Portable\FilaCms\Filament\Resources\UserResource\Pages;
use Portable\FilaCms\Filament\Traits\IsProtectedResource;

class UserResource extends AbstractConfigurableResource
{
    use IsProtectedResource;

    protected static ?string $model = User::class;

    protected static string $configKey = 'fila-cms.users';

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Security';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(static::getTableColumns())
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
