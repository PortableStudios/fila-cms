#

# How to use

## Install via Composer

In your composer.json, add the following line:
` "repositories": [`
`    {`
`        "type": "path",`
`        "url": "H:/projects/fila-cms"`
`    }`
`],`

Then in your composer's require, add this line: `"portable/fila-cms": "@dev"`

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

To run the test cases, you must set it up on a fresh laravel project. Then run `php artisan test vendor/portable/fila-cms`

## Protecting resources

Add the `IsProtectedResource` trait to your Filament resources to have them automatically obey `view <resource-name>` and `manage <resource-name>` permissions.
