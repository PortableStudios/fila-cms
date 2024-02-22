<?php

namespace Portable\FilaCms\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use File;

use Spatie\Permission\Traits\HasRoles;
use Filament\Models\Contracts\FilamentUser;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class InstallTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        // remove config files
        File::delete(config_path('fila-cms.php'));
        File::delete(config_path('filament-tiptap-editor.php'));
        
        // remove migrations from revisionable
        File::delete(database_path('migrations\2013_04_09_062329_create_revisions_table.php'));
        
        // remove filament theme
        File::delete(resource_path('css\filament\admin\tailwind.config.js'));
        File::delete(resource_path('css\filament\admin\theme.css'));

    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * A basic test example.
     */
    public function test_install_command(): void
    {
        $this->artisan('fila-cms:install')
            ->expectsOutputToContain('Installing Filament Base...')
            ->expectsQuestion('Would you like to public the FilaCMS config?(Y/n)', 'Y')
            ->expectsQuestion('Would you like to run migrations(Y/n)?', 'Y')
            ->expectsQuestion('Would you like to add the required trait to your App\\Models\\User model?(Y/n)', 'Y')
            ->expectsOutputToContain('Finished')
            ->assertExitCode(0);

        $this->assertTrue(File::exists(config_path('fila-cms.php')));
        $this->assertTrue(File::exists(config_path('filament-tiptap-editor.php')));

        $this->assertTrue(File::exists(database_path('migrations\2013_04_09_062329_create_revisions_table.php')));

    }
    
    public function test_user_model_has_traits(): void
    {
        // check if User Model has the trait
        $existingTraits = class_uses_recursive('App\Models\User');
        $this->assertTrue(in_array(HasRoles::class, $existingTraits));

        // check if has interface
        $userContents = file_get_contents(app_path("Models/User.php"));
        $this->assertGreaterThan(0, strpos($userContents, FilamentUser::class));
    }

    public function test_roles_table_seeded(): void
    {
        $this->assertDatabaseHas('roles', ['name' => 'Admin']);
        $this->assertDatabaseHas('roles', ['name' => 'User']);
        $this->assertDatabaseHas('permissions', ['name' => 'access filacms-backend']);
    }
}
