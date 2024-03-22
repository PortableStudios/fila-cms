<?php

namespace Portable\FilaCms\Tests\Filament;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Portable\FilaCms\Filament\Resources\UserResource as TargetResource;
use Portable\FilaCms\Filament\Resources\UserResource;
use Portable\FilaCms\Tests\TestCase;
use Portable\FilaCms\Tests\User as TargetModel;
use Spatie\Permission\Models\Role;

class UserResourceTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

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
        $this->get(UserResource::getUrl('index'))->assertSuccessful();
    }

    public function test_forbidden(): void
    {
        $user = $this->createUser();
        $this->be($user);
        $this->get(UserResource::getUrl('index'))->assertForbidden();
    }

    public function test_can_list_users(): void
    {
        $users = [];

        for ($i = 0; $i < 5; $i++) {
            $users[] = $this->createUser();
        }

        Livewire::test(UserResource\Pages\ListUsers::class)->assertCanSeeTableRecords($users);
    }

    public function test_can_create_record(): void
    {
        Livewire::test(TargetResource\Pages\CreateUser::class)
            ->fillForm($this->generateModel(true))
            ->call('create')
            ->assertHasNoFormErrors();

        Livewire::test(TargetResource\Pages\CreateUser::class)
            ->fillForm([])
            ->call('create')
            ->assertHasFormErrors([
                'name' => 'required',
                'email' => 'required',
            ]);
    }

    public function test_can_render_edit_page(): void
    {
        $data = $this->generateModel();

        $this->get(TargetResource::getUrl('edit', ['record' => $data]))->assertSuccessful();
    }

    public function test_can_retrieve_edit_data(): void
    {
        $data = $this->generateModel();

        Livewire::test(
            TargetResource\Pages\EditUser::class,
            ['record' => $data->getRouteKey()]
        )
            ->assertFormSet([
                'name' => $data->name,
            ]);
    }

    public function test_can_save_form(): void
    {
        $data = $this->generateModel();

        $new = TargetModel::make($this->generateModel(true));

        Livewire::test(TargetResource\Pages\EditUser::class, [
            'record' => $data->getRoutekey(),
        ])
            ->fillForm([
                'name' => $new->name,
                'email' => $new->email,
                'password' => $new->password,
                'roles' => $new->roles,
            ])
            ->call('save')
            ->assertHasNoFormErrors();
        $updatedTime = now();

        $data->refresh();
        $this->assertEquals($data->name, $new->name);
        $this->assertEquals($data->updated_at->format('Y-m-d H:i'), $updatedTime->format('Y-m-d H:i'));
    }

    public function generateModel($raw = false): TargetModel|array
    {
        $adminRole = Role::where('name', 'Admin')->first();

        $data = [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'password' => $this->faker->password,
            'roles' => [$adminRole->id],
        ];

        if ($raw) {
            return $data;
        }

        return TargetModel::create($data);
    }
}
