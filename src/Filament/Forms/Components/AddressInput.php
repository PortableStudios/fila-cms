<?php

namespace Portable\FilaCms\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Concerns\EntanglesStateWithSingularRelationship;
use Filament\Forms\Components\Contracts\CanEntangleWithSingularRelationships;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;

class AddressInput extends Component implements CanEntangleWithSingularRelationships
{
    use EntanglesStateWithSingularRelationship;

    protected $name;
    protected bool | Closure $isRequired = false;


    /**
     * @var view-string
     */
    protected string $view = 'fila-cms::filament.forms.components.address';

    /**
     * @param  array<Component> | Closure  $schema
     */
    final public function __construct(array | Closure $schema = [])
    {
        $this->schema($schema);
    }

    public function getName()
    {
        return $this->name;
    }

    public function required(bool | Closure $condition = true): static
    {
        $this->isRequired = $condition;

        return $this;
    }

    public function isRequired(): bool
    {
        return (bool) $this->evaluate($this->isRequired);
    }

    /**
     * @param  string $name
     */
    public static function make(string $name): static
    {
        $static = app(static::class, ['schema' => [
            Select::make('country')->placeholder('Country')->options(static::getCountries())->required(function (Select $component) {
                return $component->getContainer()->getParentComponent()->isRequired();
            })->searchable()->live(),
            TextInput::make('street_1')->placeholder('Street 1')->required(function (TextInput $component) {
                return $component->getContainer()->getParentComponent()->isRequired();
            }),
            TextInput::make('street_2')->placeholder('Street 2'),
            Group::make([
                TextInput::make('suburb')->placeholder('Suburb'),
                Select::make('state')->placeholder('State')
                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                        $currentState = $get('state');
                        if ($currentState == '') {
                            return;
                        }
                        $set('country', static::getCountry($currentState));
                    })
                    ->options(function (Get $get) {
                        $country = $get('country');
                        return static::getStates($country);
                    })->searchable()->live(),
                TextInput::make('postcode')->placeholder('Postcode'),
            ])->columns(3),

        ]]);
        $static->name = $name;
        $static->statePath($name);
        $static->configure();

        return $static;
    }

    protected static function getCountries()
    {
        return collect(countries())->pluck('name', 'iso_3166_1_alpha2')->toArray();
    }

    public static function getCountry($state)
    {
        $country = collect(countries())->first(function ($country) use ($state) {
            return collect(country($country['iso_3166_1_alpha2'])->getDivisions())->has($state);
        });
        return $country['iso_3166_1_alpha2'] ?? null;
    }

    public static function getStates($country = null)
    {
        if ($country) {
            return collect(country(\Str::lower($country))->getDivisions() ?: [])->map(
                function ($state, $key) {
                    return [
                        'label' => $state['name'] ?: '',
                        'value' => $key
                    ];
                }
            )->pluck('label', 'value')->toArray();
        } else {
            return collect(countries())->map(function ($country, $key) {
                $country = country($key);
                return [
                    'label' => $country->getName() ?: $key,
                    'states' => static::getStates($key)
                ];
            })->pluck('states', 'label')->toArray();
        }
    }
}
