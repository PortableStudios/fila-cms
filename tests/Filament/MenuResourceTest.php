<?php

namespace Portable\FilaCms\Tests\Filament;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Portable\FilaCms\Filament\Resources\MenuResource;
use Portable\FilaCms\Models\Menu;
use Portable\FilaCms\Tests\TestCase;
use Spatie\Permission\Models\Role;

class MenuResourceTest extends TestCase
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
        $this->get(MenuResource::getUrl('index'))->assertSuccessful();
    }

    public function test_forbidden(): void
    {
        $user = $this->createUser();
        $this->be($user);
        $this->get(MenuResource::getUrl('index'))->assertForbidden();
    }

    public function test_can_list_data(): void
    {
        $data = [];
        for ($i = 0; $i < 5; $i++) {
            $data[] = $this->generateModel();
        }

        Livewire::test(MenuResource\Pages\ListMenus::class)->assertCanSeeTableRecords($data);
    }

    public function test_can_create_record(): void
    {
        Livewire::test(MenuResource\Pages\CreateMenu::class)
            ->fillForm([
                'name' => $this->faker->firstName,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        Livewire::test(MenuResource\Pages\CreateMenu::class)
            ->fillForm([
                'name' => '',
            ])
            ->call('create')
            ->assertHasFormErrors([
                'name' => 'required',
            ]);
    }

    public function test_can_render_edit_page(): void
    {
        $this->generateModel();

        $data = Menu::first();

        $this->get(MenuResource::getUrl('edit', ['record' => $data]))->assertSuccessful();
    }

    public function test_can_retrieve_edit_data(): void
    {
        $this->generateModel();
        $data = Menu::first();

        Livewire::test(
            MenuResource\Pages\EditMenu::class,
            ['record' => $data->getRouteKey()]
        )
            ->assertFormSet([
                'name'  => $data->name,
            ]);
    }

    public function test_can_save_form(): void
    {
        $data = $this->generateModel();

        $new = Menu::make([
            'name' => $this->faker->firstName,
        ]);

        Livewire::test(MenuResource\Pages\EditMenu::class, [
            'record' => $data->getRoutekey(),
        ])
        ->fillForm([
            'name'  => $new->name,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

        $data->refresh();
        $this->assertEquals($data->name, $new->name);
    }

    public function generateModel(): Menu
    {
        return Menu::factory()->create();
    }
}
