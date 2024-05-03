<?php

namespace Portable\FilaCms\Filament\FormBlocks;

use Closure;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Illuminate\Database\Eloquent\Model;
use Portable\FilaCms\Facades\FilaCms;
use Portable\FilaCms\Models\Author;
use Portable\FilaCms\Models\Taxonomy;
use Portable\FilaCms\Models\TaxonomyTerm;

class RelationshipBlock extends AbstractFormBlock
{
    protected Closure|string|null $icon = 'heroicon-o-rectangle-stack';

    public static function getBlockName(): string
    {
        return 'Relationship Field';
    }

    public function getSchema(): Closure|array
    {
        return [
            Grid::make('general')
                ->columns(2)
                ->schema([
                    TextInput::make('field_name')
                        ->label('Field Name')
                        ->default($this->getName())
                        ->required(),
                ]),
                Grid::make('settings')
                    ->columns(4)
                    ->schema(function () {
                        return static::getRequirementFields();
                    })
                ];
    }

    protected static function createField($fieldData, $readOnly = false)
    {
        if(!isset($fieldData['component_class'])) {
            $fieldData['component_class'] = Select::class;
        }

        return ($fieldData['component_class'])::make($fieldData['field_name'])
            ->options(static::getOptions($fieldData))
            ->default(isset($fieldData['default_value']) ? $fieldData['default_value'] : null);
    }

    protected static function getOptions($fieldData)
    {
        $options = [];

        $relationshipClass = $fieldData['relationship'];
        if(is_subclass_of($relationshipClass, Model::class)) {
            $modelClass = $relationshipClass;
            switch($modelClass) {
                case Author::class:
                    $titleField = 'display_name';
                    break;
                default:
                    $titleField = 'name';
            }
        } else {
            $modelClass = FilaCms::getModelFromResource($relationshipClass);
            $titleField = 'title';
        }
        $taxonomyId = $fieldData['taxonomy_id'] ?? null;

        if($modelClass) {
            $options = $modelClass::all()->sortBy($titleField)->pluck($titleField, 'id')->toArray();
        } elseif($taxonomyId) {
            $options = TaxonomyTerm::where('taxonomy_id', $taxonomyId)->get()->sortBy('name')->pluck('name', 'id')->toArray();
        }

        return $options;
    }

    protected static function applyRequirementFields(Component $field, array $fieldData): Component
    {
        if(isset($fieldData['required']) && $fieldData['required']) {
            $field->required();
        }

        if(isset($fieldData['multiselect']) && $fieldData['multiselect'] && (!in_array($fieldData['componentClass'], [Radio::class, CheckboxList::class]))) {
            $field->multiple();
        }

        if(isset($fieldData['searchable']) && $fieldData['searchable']) {
            $field->searchable();
        }

        return $field;
    }

    protected static function getRequirementFields(): array
    {
        return [
            Select::make('component_class')
                ->options([
                    CheckboxList::class => 'Checkbox List',
                    Radio::class => 'Radio Buttons',
                    Select::class => 'Select',
                ])
                ->default(Select::class)
                ->label('Component Class')
                ->live()
                ->required(),
            Checkbox::make('required')
                ->inline(false)
                ->label('Required'),
            Checkbox::make('multiselect')
                ->inline(false)
                ->disabled(function (Get $get) {
                    return in_array($get('component_class'), [Radio::class, CheckboxList::class]);
                })
                ->live()
                ->label('Multiselect'),
            Checkbox::make('searchable')
                ->inline(false)
                ->live()
                ->disabled(function (Get $get) {
                    return $get('component_class') == Radio::class;
                })
                ->label('Searchable'),
            Select::make('relationship')
                ->options(static::getRelationshipOptions())
                ->required()->live(),
            Select::make('taxonomy_id')
                ->label('Taxonomy')
                ->required()
                ->options(Taxonomy::all()->pluck('name', 'id')->toArray())
                ->visible(function (Get $get) {
                    return $get('relationship') == TaxonomyTerm::class;
                })->live()
        ];
    }

    protected static function getRelationshipOptions(): array
    {
        $options = FilaCms::getContentModels();
        $options[Author::class] = 'Authors';
        $options[Taxonomy::class] = 'Taxonomies';
        $options[TaxonomyTerm::class] = 'Taxonomy Terms';

        return $options;
    }
}
