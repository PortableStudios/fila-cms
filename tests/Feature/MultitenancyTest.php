<?php

namespace Portable\FilaCms\Tests\Feature;

use Filament\Models\Contracts\FilamentUser;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Portable\FilaCms\Tests\TestCase;
use Spatie\Permission\Traits\HasRoles;

class MultitenancyTest extends TestCase
{
    use WithFaker;

    protected $tenantedTables = [
        'roles',
        'authors',
        'forms',
        'media',
        'menus',
        'settings',
        'taxonomies'
    ];

    protected function setUp(): void
    {
        // Rebuild the database for each test
        static::$hasInstalled = false;
        parent::setUp();
    }

    public function test_install_defaults(): void
    {
        $this->artisan('migrate:rollback');
        Schema::dropIfExists('monitored_scheduled_tasks');
        Schema::dropIfExists('monitored_scheduled_task_log_items');
        $this->artisan('migrate');

        foreach($this->tenantedTables as $table) {
            $columns = collect(Schema::getColumns($table));
            $teamId = $columns->firstWhere('name', config('fila-cms.tenant_id_field'));
            $this->assertNull($teamId);
        }
    }


    protected function turnOffMultitenancy()
    {
        config(['fila-cms.multitenancy' => false]);
        config(['permission.teams' => false]);
        config(['permission.column_names.team_foreign_key' => config('fila-cms.tenant_id_field')]);
    }

    protected function turnOnMultitenancy()
    {
        config(['fila-cms.multitenancy' => true]);
        config(['permission.teams' => true]);
        config(['permission.column_names.team_foreign_key' => config('fila-cms.tenant_id_field')]);
    }

    public function test_install_multitenancy(): void
    {
        $this->turnOnMultitenancy();
        $this->artisan('migrate:rollback');
        Schema::dropIfExists('monitored_scheduled_tasks');
        Schema::dropIfExists('monitored_scheduled_task_log_items');
        $this->artisan('migrate');

        foreach($this->tenantedTables as $table) {
            $columns = collect(Schema::getColumns($table));
            $teamId = $columns->firstWhere('name', config('fila-cms.tenant_id_field'));
            $this->assertNotNull($teamId, "Table $table does not have a " . config('fila-cms.tenant_id_field') . " column");
        }

        $this->turnOffMultitenancy();
    }

    protected function resetInstallation()
    {
        // remove config files
        File::delete(config_path('fila-cms.php'));
        File::delete(config_path('filament-tiptap-editor.php'));

        // remove filament theme
        File::delete(resource_path('css/filament/admin/tailwind.config.js'));
        File::delete(resource_path('css/filament/admin/theme.css'));

        // refresh database
        $this->artisan('migrate:reset');

    }

    protected function runInstallation()
    {
        return $this->artisan('fila-cms:install')
        ->expectsOutputToContain('Installing Filament Base...')
        ->expectsQuestion('Would you like to publish the FilaCMS config?(Y/n)', 'Y')
        ->expectsQuestion('Would you like to run migrations(Y/n)?', 'Y')
        ->expectsQuestion('Would you like to add the required trait to your App\\Models\\User model?(Y/n)', 'Y')
        ->expectsOutputToContain('Finished')
        ->assertExitCode(0);
    }

    protected function verifyInstallation()
    {
        $this->assertTrue(File::exists(config_path('fila-cms.php')));
        $this->assertTrue(File::exists(config_path('filament-tiptap-editor.php')));

        $this->assertDatabaseHas('roles', ['name' => 'Admin']);
        $this->assertDatabaseHas('roles', ['name' => 'User']);
        $this->assertDatabaseHas('permissions', ['name' => 'access filacms-backend']);

        $userModel = config('auth.providers.users.model');
        $userReflection = new \ReflectionClass($userModel);
        $traitsAndInterfaces = [
            HasRoles::class,
            FilamentUser::class,
        ];

        $fileContents = file_get_contents($userReflection->getFileName());
        foreach ($traitsAndInterfaces as $traitOrInterface) {
            $this->assertStringContainsString($traitOrInterface, $fileContents, "User model does not have $traitOrInterface");
        }
    }
}
