# Settings

You can provide settings fields to FilaCms in the register method of a service provider, using the FilaCms facade (`Portable\FilaCms\Facades\FilaCms`) method "registerSetting".

This takes four parameters:
- tabName: The name (title) of the tab for your settings fields
- groupName: The fieldset in which your settings fields should appear
- order: The order within the fieldset to append your fields
- fields: An array of Filament Form inputs to render.

Here is a simple example of adding a new social networking field to the existing Organisation Details within the SEO & Analytics tab:

```
<?php

namespace App\Providers;

use Portable\FilaCms\Facades\FilaCms;
use Filament\Forms\Components\TextInput;

class AppServiceProvider() {

    ...
    public function register()
    {
         ...
        FilaCms::registerSetting(
            'SEO & Analytics', 
            'Organisation Details', 
            0,
            function () {
                return [
                    TextInput::make('seo.organisation.socially')->label('Socially Url'),
                ];
        });
    }
}
```

## Data storage and manipulation for non-string types
Within the database, all settings are stored as strings, in a long text field.

Sometimes, you may need to manipulate the data as it is loaded from the database into the form field (`afterStateHydrated`) , and then again before it is put back into the database (`mutateDehydratedStateUsing`), for example, if the form component you are using returns an array.  Here is an example of this for a CheckboxList:


```
<?php

namespace App\Providers;

use Portable\FilaCms\Facades\FilaCms;
use Filament\Forms\Components\CheckboxList;

class AppServiceProvider() {

    ...
    public function register()
    {
         ...
        FilaCms::registerSetting(
            'SEO & Analytics', 
            'Organisation Details', 
            0,
            function () {
                return [
                    CheckboxList::make('seo.analytics')
                        ->label('Analytics')
                        ->options([
                            'google' => 'Google Analytics',
                            'facebook' => 'Facebook Pixel',
                            'hotjar' => 'Hotjar',
                            'gtm' => 'Google Tag Manager',
                    ])
                    ->mutateDehydratedStateUsing(function ($state) {
                        return implode(",", $state);
                    })->afterStateHydrated(function (CheckboxList $component, $state, Set $set) {
                        $component->state(explode(",", $state));
                    })
                ];
        });
    }
}
```