<?php

namespace Portable\FilaCms\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Password as PasswordReset;
use Portable\FilaCms\Filament\Resources\UserResource\Pages;
use Portable\FilaCms\Filament\Resources\UserResource\RelationManagers;
use Portable\FilaCms\Filament\Traits\IsProtectedResource;
use Rawilk\FilamentPasswordInput\Password;

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
                    ->unique(ignoreRecord:true)
                    ->required(),
                Password::make('password')
                    ->regeneratePassword(color: 'warning')
                    ->copyable(color: 'info')
                    ->newPasswordLength(16),
                Forms\Components\Select::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
            ]);
    }

    public static function getModel(): string
    {
        return  config('auth.providers.users.model');
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
