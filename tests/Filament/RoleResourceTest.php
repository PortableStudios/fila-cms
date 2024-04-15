<?php

namespace Portable\FilaCms\Tests\Filament;

use Illuminate\Foundation\Testing\Concerns\InteractsWithSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Portable\FilaCms\Filament\Resources\RoleResource as TargetResource;
use Portable\FilaCms\Filament\Resources\RoleResource\Pages\EditRole;
use Portable\FilaCms\Tests\TestCase;
use Portable\FilaCms\Tests\User;
use Spatie\Permission\Models\Role as TargetModel;
use Spatie\Permission\Models\Role;

class RoleResourceTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;
    use InteractsWithSession;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => '\\Portable\\FilaCms\\Database\\Seeders\\RoleAndPermissionSeeder']);
        $adminRole = Role::where('name', 'Admin')->first();
        $adminUser = $this->createUser();
        $adminUser->assignRole($adminRole);

        $this->actingAs($adminUser);
    }

    public function test_render_page(): void
    {
        $this->get(TargetResource::getUrl('index'))->assertSuccessful();
    }

    public function test_forbidden(): void
    {
        $user = $this->createUser();
        $this->be($user);
        $this->get(TargetResource::getUrl('index'))->assertForbidden();
    }

    public function test_can_list_data(): void
    {
        $data = TargetModel::all();

        Livewire::test(TargetResource\Pages\ListRoles::class)->assertCanSeeTableRecords($data);
    }

    public function test_can_create_record(): void
    {
        Livewire::test(TargetResource\Pages\CreateRole::class)
            ->fillForm([
                'name' => $this->faker->words(3, true)
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        Livewire::test(TargetResource\Pages\CreateRole::class)
            ->fillForm([
                'name' => ''
            ])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required']);
    }

    public function test_can_render_edit_page(): void
    {
        $data = TargetModel::first();

        $this->get(TargetResource::getUrl('edit', ['record' => $data]))->assertSuccessful();
    }

    public function test_can_retrieve_edit_data(): void
    {
        $data = TargetModel::first();

        Livewire::test(
            TargetResource\Pages\EditRole::class,
            ['record' => $data->getRouteKey()]
        )
            ->assertFormSet([
                'name'  => $data->name
            ]);
    }

    public function test_can_save_form(): void
    {
        $data = TargetModel::create(['name' => $this->faker->words(3, true)]);
        $new = TargetModel::make(['name' => $this->faker->words(3, true)]);

        Livewire::test(TargetResource\Pages\EditRole::class, [
            'record' => $data->getRoutekey(),
        ])
        ->fillForm([
            'name'  => $new->name
        ])
        ->call('save')
        ->assertHasNoFormErrors();

        $data->refresh();
        $this->assertEquals($data->name, $new->name);
    }

    public function test_can_delete_without_users()
    {
        $role = Role::create(['name' => 'dummy-role']);
        $livewireResponse = Livewire::test(EditRole::class, [
            'record' => $role->getRoutekey(),
        ])
        ->call('mountAction', 'delete')
        ->call('callMountedAction');

        $livewireResponse->assertSessionHas('filament.notifications', function ($notifications) {
            return collect($notifications)->first()['title'] === 'Deleted';
        });

        $role = Role::find($role->id);
        $this->assertNull($role);
    }


    public function test_cannot_delete_with_users()
    {
        $role = Role::create(['name' => 'dependant-role']);
        $user = User::factory()->create();
        $user->assignRole($role);

        $livewire = Livewire::test(EditRole::class, [
            'record' => $role->getRoutekey(),
        ]);

        $livewire = $livewire->call('mountAction', 'delete');
        $livewire = $livewire->call('callMountedAction');

        $livewire->assertSessionHas('filament.notifications', function ($notifications) {
            return collect($notifications)->first()['body'] === 'You cannot delete a role that is assigned to user(s)';
        });

        $role = Role::find($role->id);
        $this->assertNotNull($role);
    }
}
