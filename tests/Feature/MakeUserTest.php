<?php

namespace Portable\FilaCms\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Portable\FilaCms\Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Schema;

class MakeUserTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => '\\Portable\\FilaCms\\Database\\Seeders\\RoleAndPermissionSeeder']);
    }


    public function test_can_detect_missing_role(): void
    {
        Role::query()->delete();
        Permission::query()->delete();

        $this->artisan('fila-cms:make-user')
            ->expectsOutputToContain('Cannot locate Admin role, have you run php artisan fila-cms:install?')
            ->assertExitCode(0);
    }

    public function test_roles_created(): void
    {
        $userModel = config('auth.providers.users.model');

        $userFieldsRaw = Schema::getColumnListing((new $userModel())->getTable());

        $excludeFields = [ 'id', 'created_at', 'updated_at', 'deleted_at', 'remember_token', 'email_verified_at' ];
        $userFields = array_diff($userFieldsRaw, $excludeFields);
        foreach ($userFields as $key => $field) {
            $this->expectedQuestions[] = ['Enter admin ' . $field, 'test'];
        }

        $artisan = $this->artisan('fila-cms:make-user')
            ->expectsOutputToContain('User created')
            ->assertExitCode(1)
            ->run();

        // verify user has been created
        $this->assertDatabaseHas('users', [
            'name' => 'test',
        ]);

        // verify user has role
        $user = $userModel::first();
        $this->assertTrue($user->can('access filacms-backend'));
    }
}
