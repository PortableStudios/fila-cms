<?php

namespace Portable\FilaCms\Commands;

use Filament\Support\Commands\InstallCommand as CommandsInstallCommand;
use Filament\Support\Commands\UpgradeCommand;
use Illuminate\Support\Facades\File;

class InstallCommand extends CommandsInstallCommand
{
    protected $signature = 'fila-cms:install {--publish-config} {--run-migrations} {--add-user-traits} {--scaffold} {--actions} {--forms} {--infolists} {--notifications} {--panels} {--tables} {--widgets} {--F|force}';

    protected $description = 'Install Fila CMS';

    public function handle()
    {
        $this->info('Installing Filament Base...');

        $this->installScaffolding();
        $this->call(UpgradeCommand::class);
        $this->installUpgradeCommand();

        $this->info('Installed Filament Base.  Installing Spatie Permissions');

        $this->call('fortify:install');

        $this->call('vendor:publish', ['--provider' => "Spatie\Permission\PermissionServiceProvider"]);
        $this->call('vendor:publish', ['--tag' => "seo-migrations"]);
        $this->call('vendor:publish', ['--tag' => "seo-config"]);
        $this->call('vendor:publish', ['--tag' => "config"]);
        $this->call('vendor:publish', ['--tag' => "filament-actions-migrations"]);
        $this->call('vendor:publish', [
            '--provider' => "Spatie\ScheduleMonitor\ScheduleMonitorServiceProvider",
            '--tag' => "schedule-monitor-migrations"
        ]);

        $this->info('Installed Spatie Permissions. Installing Fila CMS Config...');

        if ($this->option('publish-config') || ($this->ask('Would you like to publish the FilaCMS config?(Y/n)', 'Y') == 'Y')) {
            $this->call('vendor:publish', ['--tag' => 'fila-cms-config']);
        }
        // we need this for revisionable package
        $this->call('vendor:publish', ['--tag' => 'migrations']);
        $this->call('vendor:publish', ['--tag' => 'resource-lock-migrations']);


        if ($this->option('run-migrations') || strtoupper($this->ask('Would you like to run migrations(Y/n)?', 'Y')) == 'Y') {
            $this->info('Running migrations...');
            $this->call('migrate');
        }

        if ($this->option('add-user-traits') || strtoupper($this->ask('Would you like to add the required trait to your App\\Models\\User model?(Y/n)', 'Y')) == 'Y') {
            $this->call('fila-cms:add-user-concerns');
        }

        $this->call('db:seed', ['--class' => '\\Portable\\FilaCms\\Database\\Seeders\\RoleAndPermissionSeeder']);
        $this->call('db:seed', ['--class' => '\\Portable\\FilaCms\\Database\\Seeders\\RootMediaFoldersSeeder']);

        $this->info('Adding permissions');

        $this->info('Creating Custom Filament Theme');

        // @codeCoverageIgnoreStart
        if (!app()->runningUnitTests()) {
            // Only call this if there isn't already a filament theme
            if (!File::exists(resource_path('css/filament/admin/theme.css'))) {
                $this->callSilent('make:filament-theme');
            }
        }
        // @codeCoverageIgnoreEnd

        $this->call('vendor:publish', ['--tag' => 'filament-tiptap-editor-config']);
        $this->info('Finished');
    }
}
