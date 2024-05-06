<?php

namespace Portable\FilaCms\Filament\Pages;

use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Portable\FilaCms\Facades\FilaCms;
use Portable\FilaCms\Filament\Traits\IsProtectedResource;
use Portable\FilaCms\Models\Setting;

class EditSettings extends Page implements HasForms
{
    use IsProtectedResource;
    use InteractsWithForms;

    public ?array $data = [];
    protected static ?string $title = 'Settings';
    protected static ?string $navigationIcon = 'heroicon-o-cog-8-tooth';
    protected static string $view = 'fila-cms::admin.pages.edit-settings';
    protected static ?string $navigationGroup = 'System';

    public function mount(): void
    {
        Setting::all()->each(function ($setting) {
            data_set($this->data, $setting->key, $setting->value);
        });

        $this->form->fill($this->data);
    }

    public function form(Form $form): Form
    {
        $settings = FilaCms::getSettingsFields();

        foreach($settings as $tabName => $groups) {
            $fieldsets = [];
            foreach($groups as $groupName => $groupFields) {
                $fieldsets[] = Fieldset::make($groupName)->schema($groupFields);
            }
            $tabs[] = Tab::make($tabName)->schema($fieldsets);
        }

        return $form
            ->schema([Tabs::make('Tab')->schema($tabs)])
            ->statePath('data');
    }

    public function save(): void
    {
        $formData = $this->form->getState();
        $records = [];
        collect(FilaCms::getSettingsFields())->flatten()->each(function ($field) use (&$formData, &$records) {
            if(method_exists($field, 'getName')) {
                $records[] = [
                    'key' => $field->getName(),
                    'value' => data_get($formData, $field->getName())
                ];
            }
        });

        Setting::upsert($records, ['key'], ['value']);
    }
}
