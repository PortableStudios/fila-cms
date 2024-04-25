<?php

namespace Portable\FilaCms\Filament\Resources;

use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Portable\FilaCms\Facades\FilaCms;
use Portable\FilaCms\Filament\FormBlocks\FormBuilder;
use Portable\FilaCms\Filament\Resources\FormResource\Pages;
use Portable\FilaCms\Filament\Traits\IsProtectedResource;
use Portable\FilaCms\Models\Form as ModelsForm;

class FormResource extends AbstractResource
{
    use IsProtectedResource;

    protected static ?string $model = ModelsForm::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard';

    protected static ?string $navigationGroup = 'Forms';

    public static function form(Form $form): Form
    {

        $currentFields = [];
        $currentFields[] = Action::make('Add Field');

        $fields = [
            Section::make('Fields')
                ->schema([FormBuilder::make('fields')->hiddenLabel()->columnSpanFull()])
                ->columnSpan(2),
            Section::make('Form Information')->schema([
                TextInput::make('title')->required()->autofocus(),
                TextInput::make('slug')
                    ->rules([
                    function (Get $get) {
                        return function (string $attribute, $value, \Closure $fail) use ($get) {
                            $data = ModelsForm::withoutGlobalScopes()->where('slug', $value)
                                ->when($get('id') !== null, function ($query) use ($get) {
                                    $query->whereNot('id', $get('id'));
                                })
                                ->first();
                            if (is_null($data) === false) {
                                $fail('The :attribute already exists');
                            }
                        };
                    }
                ])->maxLength(255),
                Toggle::make('only_for_logged_in')->label('Restrict to logged in users'),
                TextInput::make('notification_email')->email()->helperText('Email to send form submissions to.  Leave blank for no notifications.'),
                TextInput::make('confirmation_title')->required(),
                FilaCms::tipTapEditor('confirmation_text')->required()->default(
                    tiptap_converter()->asJSON('Thank you for submitting the form.  We\'ll be in touch shortly.')
                )

            ])->columnSpan(1)
        ];
        return $form->schema($fields)->columns(['lg' => 3]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->sortable(),
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
            'index' => Pages\ListForms::route('/'),
            'create' => Pages\CreateForm::route('/create'),
            'edit' => Pages\EditForm::route('/{record}/edit'),
        ];
    }
}
