<?php

namespace Portable\FilaCms\Tests\Feature;

use Filament\Models\Contracts\FilamentUser;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\File;
use Portable\FilaCms\Tests\TestCase;
use Portable\FilaCms\Tests\UserNoImplements;
use Portable\FilaCms\Tests\UserSomeImplements;
use ReflectionClass;
use Spatie\Permission\Traits\HasRoles;

class InstallTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function test_install_command_no_traits(): void
    {
        $this->resetInstallation();

        $fileOldContents = File::get((new ReflectionClass(UserNoImplements::class))->getFileName());
        $oldModel = config('auth.providers.users.model');
        config([
            'auth.providers.users.model' => UserNoImplements::class,
        ]);

        $this->runInstallation();
        $this->verifyInstallation();

        static::$hasInstalled = false;

        // Reset state
        File::put((new ReflectionClass(UserNoImplements::class))->getFileName(), $fileOldContents);
        config([
            'auth.providers.users.model' => $oldModel,
        ]);
    }

    public function test_install_command_has_implements(): void
    {
        $this->resetInstallation();

        $fileOldContents = File::get((new ReflectionClass(UserSomeImplements::class))->getFileName());
        $oldModel = config('auth.providers.users.model');
        config([
            'auth.providers.users.model' => UserSomeImplements::class,
        ]);

        $this->runInstallation();
        $this->verifyInstallation();

        static::$hasInstalled = false;

        // Reset state
        File::put((new ReflectionClass(UserSomeImplements::class))->getFileName(), $fileOldContents);
        config([
            'auth.providers.users.model' => $oldModel,
        ]);
    }

    public function test_install_command_has_traits(): void
    {
        $this->assertTrue(File::exists(config_path('fila-cms.php')));
        $this->assertTrue(File::exists(config_path('filament-tiptap-editor.php')));

        $this->assertDatabaseHas('roles', ['name' => 'Admin']);
        $this->assertDatabaseHas('roles', ['name' => 'User']);
        $this->assertDatabaseHas('permissions', ['name' => 'access filacms-backend']);
    }

    public function test_user_model_has_traits(): void
    {
        $userModel = config('auth.providers.users.model');
        $reflection = new \ReflectionClass($userModel);

        // check if User Model has the trait
        $existingTraits = class_uses_recursive($userModel);
        $this->assertTrue(in_array(HasRoles::class, $existingTraits));

        // check if has interface
        $userContents = file_get_contents($reflection->getFilename());
        $this->assertGreaterThan(0, strpos($userContents, FilamentUser::class));
    }


    protected function resetInstallation()
    {
        // remove config files
        File::delete(config_path('fila-cms.php'));
        File::delete(config_path('filament-tiptap-editor.php'));

        // remove filament theme
        File::delete(resource_path('css/filament/admin/tailwind.config.js'));
        File::delete(resource_path('css/filament/admin/theme.css'));
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
