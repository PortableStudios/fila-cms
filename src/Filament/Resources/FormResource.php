<?php

namespace Portable\FilaCms\Filament\Resources;

use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Portable\FilaCms\Facades\FilaCms;
use Portable\FilaCms\Filament\FormBlocks\FormBuilder;
use Portable\FilaCms\Filament\Resources\FormResource\Pages;
use Portable\FilaCms\Filament\Resources\FormResource\RelationManagers\FormEntriesRelationManager;
use Portable\FilaCms\Filament\Traits\IsProtectedResource;
use Portable\FilaCms\Models\Form as ModelsForm;

class FormResource extends AbstractResource
{
    use IsProtectedResource;

    protected static ?string $model = ModelsForm::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard';

    protected static ?string $navigationGroup = 'Forms';

    public static function getFrontendRoutePrefix()
    {
        return static::getRoutePrefix();
    }

    public static function getFrontendShowRoute()
    {
        return static::getFrontendRoutePrefix() . '.{slug}';
    }

    public static function getFrontendIndexRoute()
    {
        return static::getFrontendRoutePrefix() . '.index';
    }

    public static function registerIndexRoute()
    {
        return false;
    }

    public static function registerShowRoute()
    {
        return true;
    }

    public static function form(Form $form): Form
    {

        $currentFields = [];
        $currentFields[] = Action::make('Add Field');

        $fields = static::getFormDefinition();
        return $form->schema($fields);
    }

    public static function getEntriesDefinition($form)
    {
        return [
            Table::make($form->getLivewire())
                ->columns([
                    TextColumn::make('created_at')->label('Date Submitted')->sortable(),
                ])
        ];
    }

    public static function getFormDefinition()
    {
        $sections = [
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
                Repeater::make('notification_emails')
                        ->schema([
                            TextInput::make('email')->email()->required(),
                        ])->helperText('Email to send form submissions to.'),
                TextInput::make('confirmation_title')->required(),
                FilaCms::tipTapEditor('confirmation_text')->required()->default(
                    tiptap_converter()->asJSON('Thank you for submitting the form.  We\'ll be in touch shortly.')
                )
            ])->columnSpan(1)
        ];

        return [
            Group::make($sections)->columns(['lg' => 3])->columnSpanFull()
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->sortable(),
                TextColumn::make('user.name')->label("Creator"),
                TextColumn::make('entries_count')->counts('entries')->label("Entries")

            ])
            ->searchable()
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\EditAction::make()->url(
                    function (Model $record) {
                        return static::getUrl('edit', ['record' => $record, 'activeRelationManager' => 0]);
                    }
                )->label('Entries'),

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
            FormEntriesRelationManager::class
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
