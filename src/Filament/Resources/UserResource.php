<?php

namespace Portable\FilaCms\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Portable\FilaCms\Filament\Resources\UserResource\Pages;
use Portable\FilaCms\Filament\Traits\IsProtectedResource;
use Rawilk\FilamentPasswordInput\Password;
use Password as PasswordReset;
use Portable\FilaCms\Filament\Resources\UserResource\RelationManagers;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;

class UserResource extends AbstractConfigurableResource
{
    use IsProtectedResource;

    protected static ?string $model = null;

    protected static string $configKey = 'fila-cms.users';

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Security';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->prefixIcon('heroicon-m-envelope')
                    ->required(),
                Password::make('password')
                    ->regeneratePassword(color: 'warning')
                    ->copyable(color: 'info')
                    ->newPasswordLength(16)
                    ->required(),
                Forms\Components\Select::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
            ]);
    }

    public static function table(Table $table): Table
    {
        static::$model = config('auth.providers.users.model');

        return $table
            ->columns(static::getTableColumns())
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('send_reset_link')
                    ->label('Send Password Reset')
                    ->icon('heroicon-s-inbox')
                    ->action(function (Model $user) {
                        PasswordReset::broker()->sendResetLink(['email' => $user->email]);
                        Notification::make()
                            ->title('Reset Link Sent')
                            ->body('Password reset link has been sent to the **' . $user->email . '**')
                            ->success()
                            ->send();
                    })
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
            RelationManagers\UserLoginsRelationManager::class,
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
