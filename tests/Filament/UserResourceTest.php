<?php

namespace Portable\FilaCms\Tests\Filament;

use Portable\FilaCms\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Portable\FilaCms\Filament\Resources\UserResource;
use Portable\FilaCms\Tests\User;
use Spatie\Permission\Models\Role;
use Livewire\Livewire;

class UserResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => '\\Portable\\FilaCms\\Database\\Seeders\\RoleAndPermissionSeeder']);
        $adminRole = Role::where('name', 'Admin')->first();
        $adminUser = User::factory()->create();
        $adminUser->assignRole($adminRole);

        $this->actingAs($adminUser);
    }

    public function test_render_page(): void
    {
        $this->get(UserResource::getUrl('index'))->assertSuccessful();
    }

    public function test_forbidden(): void
    {
        $user = User::factory()->create();
        $this->be($user);
        $this->get(UserResource::getUrl('index'))->assertForbidden();
    }

    public function test_can_list_users(): void
    {
        $users = User::factory()->count(5)->create();

        Livewire::test(UserResource\Pages\ListUsers::class)->assertCanSeeTableRecords($users);
    }
}
