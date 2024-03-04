<?php

namespace Portable\FilaCms\Tests;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Orchestra\Testbench\Attributes\WithMigration;
use Orchestra\Testbench\Concerns\WithWorkbench;

#[WithMigration]
abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    use WithWorkbench;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withFactories(__DIR__ . '/tests/Factories');

        // remove config files
        File::delete(config_path('fila-cms.php'));
        File::delete(config_path('filament-tiptap-editor.php'));

        // remove migrations from revisionable
        File::delete(database_path('migrations\2013_04_09_062329_create_revisions_table.php'));

        // remove filament theme
        File::delete(resource_path('css\filament\admin\tailwind.config.js'));
        File::delete(resource_path('css\filament\admin\theme.css'));

        $this->artisan('fila-cms:install')
            ->expectsOutputToContain('Installing Filament Base...')
            ->expectsQuestion('Would you like to publish the FilaCMS config?(Y/n)', 'Y')
            ->expectsQuestion('Would you like to run migrations(Y/n)?', 'Y')
            ->expectsQuestion('Would you like to add the required trait to your App\\Models\\User model?(Y/n)', 'Y')
            ->expectsQuestion('theme.css already exists, do you want to overwrite it?', 'no')
            ->expectsQuestion('tailwind.config.js already exists, do you want to overwrite it?', 'no')

            ->expectsOutputToContain('Finished')
            ->assertExitCode(0);
    }

    protected function defineEnvironment($app)
    {
        tap($app['config'], function (Repository $config) {
            $config->set('auth.providers.users.model', User::class);
        });
    }
}
