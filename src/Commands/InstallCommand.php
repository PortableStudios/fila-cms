<?php

namespace Portable\FilaCms\Commands;

use Filament\Support\Commands\UpgradeCommand;
use Filament\Support\Commands\InstallCommand as CommandsInstallCommand;
use Filament\PanelProvider;

class InstallCommand extends CommandsInstallCommand
{
    protected $signature = 'fila-cms:install {--scaffold} {--actions} {--forms} {--infolists} {--notifications} {--panels} {--tables} {--widgets} {--F|force}';

    protected $description = 'Install Fila CMS';

    public function handle()
    {
        $this->info('Installing Filament Base...');

        $this->installScaffolding();
        $this->call(UpgradeCommand::class);
        $this->installUpgradeCommand();

        $this->info('Installed Filament Base.  Installing Spatie Permissions');

        $this->call('vendor:publish', ['--provider' => "Spatie\Permission\PermissionServiceProvider"]);

        $this->info('Installed Spatie Permissions. Installing Fila CMS Config...');

        if ($this->ask('Would you like to public the FilaCMS config?(Y/n)', 'Y')=='Y') {
            $this->call('vendor:publish', ['--tag' => 'fila-cms-config']);
        }

        if (strtoupper($this->ask('Would you like to run migrations(Y/n)?', 'Y'))=='Y') {
            $this->info('Running migrations...');
            $this->call('migrate');
        }

        if (strtoupper($this->ask("Would you like to add the required trait to your App\\Models\\User model?(Y/n)", 'Y'))=='Y') {
            $this->call('fila-cms:add-user-concerns');
        }

        $this->call('db:seed', ['--class=\\Portable\\FilaCms\\Database\\Seeders\\RoleAndPermissionSeeder']);

        $this->info('Adding permissions');

        $this->info('Finished');
    }
}
