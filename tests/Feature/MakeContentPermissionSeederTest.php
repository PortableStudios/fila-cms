<?php

namespace Portable\FilaCms\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Portable\FilaCms\Tests\TestCase;

class MakeContentPermissionSeederTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_creates_seeder_no_run(): void
    {
        $timestamp = date('Y_m_d_His');
        $this->artisan('make:filacms-content-permissions', ['name' => 'MyContent'])
            ->expectsQuestion('Would you like to run the seeder now? (Y/n)', 'n');
        $this->assertFileExists(database_path('seeders/MyContentRoleAndPermissionSeeder.php'));

        $this->assertDatabaseMissing('permissions', [
            'name' => 'view my contents',
        ]);
        $this->assertDatabaseMissing('permissions', [
            'name' => 'manager my contents',
        ]);
    }

    public function test_creates_seeder_do_run(): void
    {
        spl_autoload_register([$this, 'loadSeeders'], true, true);

        $this->artisan('make:filacms-content-permissions', ['name' => 'MyContent'])
            ->expectsQuestion('Would you like to run the seeder now? (Y/n)', 'y');
        $this->assertFileExists(database_path('seeders/MyContentRoleAndPermissionSeeder.php'));

        $this->assertDatabaseHas('permissions', [
            'name' => 'view my contents',
        ]);
        $this->assertDatabaseHas('permissions', [
            'name' => 'manage my contents',
        ]);

        spl_autoload_unregister([$this, 'loadSeeders']);
    }

    public function test_creates_model_no_argument(): void
    {
        $this->artisan('make:filacms-content-permissions')
            ->expectsQuestion('What is the model name?', '')
            ->assertExitCode(1);
    }

    public function loadSeeders($className)
    {
        if(Str::contains($className, 'MyContentRoleAndPermissionSeeder')) {
            require database_path('seeders/MyContentRoleAndPermissionSeeder.php');
        }
    }
}
