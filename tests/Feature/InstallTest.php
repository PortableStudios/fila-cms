<?php

namespace Portable\FilaCms\Tests\Feature;

use Portable\FilaCms\Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use File;

use Spatie\Permission\Traits\HasRoles;
use Filament\Models\Contracts\FilamentUser;

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

    public function test_install_command(): void
    {
        $this->assertTrue(File::exists(config_path('fila-cms.php')));
        $this->assertTrue(File::exists(config_path('filament-tiptap-editor.php')));

        $this->assertTrue(File::exists(database_path('migrations\2013_04_09_062329_create_revisions_table.php')));

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
}
