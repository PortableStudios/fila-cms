<?php

namespace Portable\FilaCms\Tests\Listeners;

use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Process;
use Portable\FilaCms\Tests\Factories\FormEntryFactory;
use Portable\FilaCms\Tests\Factories\FormFactory;
use Portable\FilaCms\Tests\Factories\PageFactory;

class CommandStartingListener
{
    public function handle(CommandStarting $event): void
    {
        if($event->command !== 'serve') {
            return;
        }

        // Create the user model stub
        config(['auth.providers.users.model', 'Workbench\App\Models\User']);

        Artisan::call('package:create-sqlite-db');
        Artisan::call('migrate:fresh');

        // Setup the FilaCMS install
        // remove config files
        File::delete(config_path('fila-cms.php'));
        File::delete(config_path('filament-tiptap-editor.php'));

        // remove migrations from revisionable
        File::delete(database_path('migrations/2013_04_09_062329_create_revisions_table.php'));

        // remove filament theme
        File::delete(resource_path('css/filament/admin/tailwind.config.js'));
        File::delete(resource_path('css/filament/admin/theme.css'));

        Artisan::call('fila-cms:install', ['--publish-config' => true,'--run-migrations' => true,'--add-user-traits' => true]);

        File::copy(getcwd() . '/vite.config.js', resource_path('../vite.config.js'));
        File::ensureDirectoryExists(resource_path('css'));
        File::copy(getcwd() . '/resources/css/filacms.css', resource_path('css/filacms.css'));
        File::copy(getcwd() . '/package.json', resource_path('../package.json'));

        // Ensure there's an admin user
        $userModel = config('auth.providers.users.model');
        $admin = $userModel::where('email', 'admin@test.com')->first();
        if(!$admin) {
            $admin = new $userModel();
        }
        $admin->email = 'admin@test.com';
        $admin->password = Hash::make('password');
        $admin->name = 'Admin';
        $admin->save();
        $admin->assignRole('Admin');

        Process::path(app_path())->forever()->start('npm run dev');
        PageFactory::new()->count(10)->create();
        $form = FormFactory::new()->create([
            'title' => 'Contact Us',
            'slug' => 'contact-us'
        ]);

        FormEntryFactory::new(['form_id' => $form->id])->count(5)->create();
    }
}
