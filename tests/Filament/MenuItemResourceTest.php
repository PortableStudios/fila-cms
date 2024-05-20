<?php

namespace Portable\FilaCms\Tests\Filament;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Portable\FilaCms\Filament\Resources\MenuResource\Pages\EditMenu;
use Portable\FilaCms\Filament\Resources\MenuResource\RelationManagers\ItemsRelationManager;
use Portable\FilaCms\Models\Form;
use Portable\FilaCms\Models\Menu;
use Portable\FilaCms\Models\MenuItem;
use Portable\FilaCms\Models\Page;
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
                'name' => 'Item ' . $i,
                'order' => $i,
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

        // item 4 is already order = 5, if sorted by order, order 5 comes first
        // because they have the same order, and order 5 comes first via ID
        $data[0]->update(['name' => 'Item 5', 'order' => 6]);

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

    public function test_index_url()
    {
        $menu = Menu::factory()->create();
        $data = MenuItem::factory()->create([
            'menu_id' => $menu->id,
            'type' => 'index-page',
            'reference_page' => \Portable\FilaCms\Filament\Resources\PageResource::class,
        ]);

        $resourceClass = $data->reference_page;
        $route = route($resourceClass::getFrontendIndexRoute());

        $this->assertEquals($route, $data->url);
    }

    public function test_page_url()
    {
        $page = Page::factory()->create();
        $menu = Menu::factory()->create();
        $data = MenuItem::factory()->create([
            'menu_id' => $menu->id,
            'type' => 'content',
            'reference_page' => \Portable\FilaCms\Filament\Resources\PageResource::class,
            'reference_content' => $page->id
        ]);

        $resourceClass = $data->reference_page;
        $route = route($resourceClass::getFrontendShowRoute(), $page->slug);

        $this->assertEquals($route, $data->url);
    }

    public function test_form_url()
    {
        $form = Form::factory()->create();
        $menu = Menu::factory()->create();
        $data = MenuItem::factory()->create([
            'menu_id' => $menu->id,
            'type' => 'content',
            'reference_page' => \Portable\FilaCms\Filament\Resources\FormResource::class,
            'reference_content' => $form->id
        ]);

        $resourceClass = $data->reference_page;
        $route = route($resourceClass::getFrontendShowRoute(), $form->slug);

        $this->assertEquals($route, $data->url);
    }
}
