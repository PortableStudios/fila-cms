<?php

namespace Portable\FilaCms\Commands;

use Filament\Support\Commands\InstallCommand as CommandsInstallCommand;
use Filament\Support\Commands\UpgradeCommand;
use Illuminate\Support\Facades\File;

class InstallCommand extends CommandsInstallCommand
{
    protected $signature = 'fila-cms:install {--publish-config} {--run-migrations} {--add-user-traits} {--scaffold} {--actions} {--forms} {--infolists} {--notifications} {--panels} {--tables} {--widgets} {--F|force}';

    protected $description = 'Install Fila CMS';

    protected function callOrFail($command, $args = [])
    {
        $result = $this->call($command, $args);
        if ($result !== 0) {
            $this->error("Failed to run command: $command");
            exit($result);
        }
    }

    public function handle()
    {
        $this->info('Installing Filament Base...');

        $this->installScaffolding();
        $this->callOrFail(UpgradeCommand::class);
        $this->installUpgradeCommand();

        $this->info('Installed Filament Base.  Installing Spatie Permissions');

        $this->callOrFail('fortify:install');

        $this->callOrFail('vendor:publish', ['--provider' => "Spatie\Permission\PermissionServiceProvider"]);
        $this->callOrFail('vendor:publish', ['--tag' => "seo-migrations"]);
        $this->callOrFail('vendor:publish', ['--tag' => "seo-config"]);
        $this->callOrFail('vendor:publish', ['--tag' => "config"]);
        $this->callOrFail('vendor:publish', ['--tag' => "filament-actions-migrations"]);
        $this->callOrFail('vendor:publish', [
            '--provider' => "Spatie\ScheduleMonitor\ScheduleMonitorServiceProvider",
            '--tag' => "schedule-monitor-migrations"
        ]);

        $this->info('Installed Spatie Permissions. Installing Fila CMS Config...');

        if ($this->option('publish-config') || ($this->ask('Would you like to publish the FilaCMS config?(Y/n)', 'Y') == 'Y')) {
            $this->callOrFail('vendor:publish', ['--tag' => 'fila-cms-config']);
        }
        $this->callOrFail('vendor:publish', ['--tag' => "fila-cms-migrations"]);

        // we need this for revisionable package
        $this->callOrFail('vendor:publish', ['--tag' => 'migrations']);
        $this->callOrFail('vendor:publish', ['--tag' => 'resource-lock-migrations']);


        if ($this->option('run-migrations') || strtoupper($this->ask('Would you like to run migrations(Y/n)?', 'Y')) == 'Y') {
            $this->info('Running migrations...');
            $this->callOrFail('migrate');
        }

        if ($this->option('add-user-traits') || strtoupper($this->ask('Would you like to add the required trait to your App\\Models\\User model?(Y/n)', 'Y')) == 'Y') {
            $this->callOrFail('fila-cms:add-user-concerns');
        }

        $this->info('Adding permissions');

        $this->callOrFail('db:seed', ['--class' => '\\Portable\\FilaCms\\Database\\Seeders\\RoleAndPermissionSeeder']);
        $this->callOrFail('db:seed', ['--class' => '\\Portable\\FilaCms\\Database\\Seeders\\RootMediaFoldersSeeder']);

        $this->info('Creating Custom Filament Theme');

        // @codeCoverageIgnoreStart
        if (!app()->runningUnitTests()) {
            // Only call this if there isn't already a filament theme
            if (!File::exists(resource_path('css/filament/admin/theme.css'))) {
                $this->callSilent('make:filament-theme');
            }
        }
        // @codeCoverageIgnoreEnd

        $this->callOrFail('vendor:publish', ['--tag' => 'filament-tiptap-editor-config']);
        $this->info('Finished');
    }
}
