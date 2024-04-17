#

# How to use

Require the package using composer: `composer require portable/fila-cms:@dev`

## Installation Command

To initialize the package, you need to run the `fila-cms:install` command.
This command also has arguments that will install other features of Filament.

You can read about the various Filament features [here](https://filamentphp.com/docs/3.x/panels/installation)

The following arguments can be added to add the feature

1. scaffold
2. actions
3. forms
4. infolists
5. notifications
6. panels
7. tables
8. widgets

## Add User Concerns

This command can be used by executing `php artisan fila-cms:add-user-concerns`.
With this command, it'll add the traits and interfaces to your user model.

**Note:** This command is also part of the `fila-cms:install`.
Upon executing the install command, you'll be asked if you want to add the required trait to your User Model.
By inputting yes, the install command will also execute this command

## Maker User

After installation, you can create a user with the admin role by executing `php artisan fila-cms:make-user`.
This command will ask for field values present in your users table and automatically assigns the admin role to it.

## Testing

From the project directory, run `./vendor/bin/pest`

## Interacting with the package

During development, you may like to actually interact with the FilaCMS UI.  In your console, run
```./vendor/bin/testbench serve```

You can now load the application at http://localhost:8000/admin

Username: admin@test.com
Password: password

If you're doing anything that needs to send mail, start the Mailhog daemon:
`docker run -p 8025:8025 -p 1025:1025 mailhog/mailhog`

Now you can go to http://localhost:8025 and see any mail that the application "sends"

## Protecting resources

Add the `IsProtectedResource` trait to your Filament resources to have them automatically obey `view <resource-name>` and `manage <resource-name>` permissions.

## Extending the Abstract Content

To add additional models or tables that extends the AbstractContent, you start by executing `php artisan make:filament-resource {Resource}`.

This command will generate a Resource file in your App\Filament\Resources folder. Add the next line in your class:
`use Portable\FilaCms\Filament\Resources\AbstractContentResource;`

Then go to your generated Resource file (e.g. `RecipeResource.php`), and change the `extends Resource` part to `extends AbstractContentResource`.

You should declare the proper model in your `$model` variable.

Then go to your model and add the following line:
`use Portable\FilaCms\Models\AbstractContentModel;`
Then change the `extends Model` to `extends AbstractContentModel`

Next is to create a Plugin class in your `app/Plugins` folder (or create the folder if it's not present yet). The content should look like this (change the appropriate values such as the Resource and the ID):

~~~
namespace App\Plugins;

use App\Filament\Resources\RecipeResource;
use Filament\Panel;
use Filament\Contracts\Plugin;

class RecipesPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filacms-recipes';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            RecipeResource::class
        ]);
    }
}
~~~

Finally, add the plugin in your `app/config/fila-cms.php` file