<?php

namespace Portable\FilaCms\Tests\Filament;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Portable\FilaCms\Filament\Resources\MenuResource\Pages\EditMenu;
use Portable\FilaCms\Filament\Resources\MenuResource\RelationManagers\ItemsRelationManager;
use Portable\FilaCms\Models\Menu;
use Portable\FilaCms\Models\MenuItem;
use Portable\FilaCms\Tests\TestCase;
use Spatie\Permission\Models\Role;

class MenuItemResourceTest extends TestCase
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

    public function test_can_list_data(): void
    {
        $menu = Menu::factory()->create();
        $data = [];
        for ($i = 0; $i < 5; $i++) {
            $data[] = MenuItem::factory()->create(['menu_id' => $menu->id]);
        }

        Livewire::test(ItemsRelationManager::class, [
            'ownerRecord' => $menu,
            'pageClass' => EditMenu::class
        ])->assertCanSeeTableRecords($data);
    }

    public function test_can_reorder_data(): void
    {
        $menu = Menu::factory()->create();
        $data = [];
        for ($i = 0; $i < 5; $i++) {
            $data[] = MenuItem::factory()->create([
                'menu_id' => $menu->id,
                'name' => 'Item ' . $i
            ]);
        }

        Livewire::test(ItemsRelationManager::class, [
            'ownerRecord' => $menu,
            'pageClass' => EditMenu::class
        ])->assertCanSeeTableRecords($data)->assertSeeHtmlInOrder([
            'Item 0',
            'Item 1',
            'Item 2',
            'Item 3',
            'Item 4',
        ]);

        $data[0]->update(['name' => 'Item 5', 'order' => 5]);

        Livewire::test(ItemsRelationManager::class, [
            'ownerRecord' => $menu,
            'pageClass' => EditMenu::class
        ])->assertCanSeeTableRecords($data)->assertSeeHtmlInOrder([
            'Item 1',
            'Item 2',
            'Item 3',
            'Item 4',
            'Item 5',
        ]);
    }
}
