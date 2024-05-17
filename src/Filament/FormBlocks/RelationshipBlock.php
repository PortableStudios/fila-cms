<?php

namespace Portable\FilaCms\Filament\FormBlocks;

use Closure;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Illuminate\Database\Eloquent\Model;
use Portable\FilaCms\Facades\FilaCms;
use Portable\FilaCms\Models\AbstractContentModel;
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
                ]),
                Grid::make('settings')
                    ->columns(3)
                    ->schema(function () {
                        return static::getRequirementFields();
                    })
                ];
    }

    protected static function getModelClass($fieldData)
    {
        $relationshipClass = $fieldData['relationship'];
        $taxonomyId = $fieldData['taxonomy_id'] ?? null;
        if(is_subclass_of($relationshipClass, Model::class)) {
            $modelClass = $relationshipClass;
        } elseif($taxonomyId) {
            $modelClass = TaxonomyTerm::class;
        } else {
            $modelClass = FilaCms::getModelFromResource($relationshipClass);
        }

        return $modelClass;
    }

    protected static function getTitleField($modelClass)
    {
        if($modelClass == Author::class || is_subclass_of($modelClass, Author::class)) {
            $titleField = 'display_name';
        } elseif(is_subclass_of($modelClass, AbstractContentModel::class)) {
            $titleField = 'title';
        } else {
            $titleField = 'name';
        }

        return $titleField;
    }

    protected static function getOptionsQuery($fieldData, $search = null)
    {
        $modelClass = static::getModelClass($fieldData);
        $titleField = static::getTitleField($modelClass);
        $taxonomyId = $fieldData['taxonomy_id'] ?? null;

        if(is_subclass_of($modelClass, TaxonomyTerm::class)) {
            $query = TaxonomyTerm::where('taxonomy_id', $taxonomyId);
            $titleField = 'name';
        } else {
            $query = $modelClass::query();
        }

        if($search) {
            $query = $query->where($titleField, 'LIKE', '%'. $search . '%');
        }

        return $query->orderBy($titleField);
    }

    protected static function createField($fieldData, $readOnly = false)
    {
        if(!isset($fieldData['component_class'])) {
            $fieldData['component_class'] = Select::class;
        }

        $field = ($fieldData['component_class'])::make($fieldData['field_name'])
            ->default(isset($fieldData['default_value']) ? $fieldData['default_value'] : null);

        if($fieldData['component_class'] == Select::class) {
            $field = $field->searchable()->getSearchResultsUsing(function (string $search) use ($fieldData) {
                $query = static::getOptionsQuery($fieldData, $search);
                $titleField = static::getTitleField($fieldData);

                $options = $query->get()->pluck($titleField, 'id')->toArray();

                return $options;
            });
        } else {
            $field = $field->options(static::getOptions($fieldData));
        }

        return $field;
    }

    protected static function getOptions($fieldData)
    {
        $titleField = static::getTitleField(static::getModelClass($fieldData));
        return static::getOptionsQuery($fieldData)->get()->sortBy($titleField)->pluck($titleField, 'id')->toArray();
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
            Toggle::make('required')
                ->inline(false),
            Toggle::make('multiselect')
                ->inline(false)
                ->disabled(function (Get $get) {
                    return in_array($get('component_class'), [Radio::class, CheckboxList::class]);
                })
                ->live()
                ->label('Multiselect'),
            Toggle::make('searchable')
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

    public static function displayValue($fieldData, $values): string
    {
        $fieldName = data_get($fieldData, 'field_name');
        $value = isset($values[$fieldName]) ? $values[$fieldName] : [];
        if(!is_array($value)) {
            $value = [$value];
        }

        $options = static::getOptionsQuery($fieldData)->whereIn('id', $value)->get()->pluck(static::getTitleField(static::getModelClass($fieldData)), 'id')->toArray();

        // Map value array keys to options
        $value = array_map(function ($val) use ($options) {
            return $options[$val] ?? $val;
        }, $value);

        return implode(", ", $value);
    }
}
