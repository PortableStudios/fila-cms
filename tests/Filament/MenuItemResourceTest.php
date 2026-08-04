<?php

namespace Portable\FilaCms\Tests\Filament;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Orchestra\Testbench\Attributes\DefineRoute;
use Portable\FilaCms\Filament\Resources\FormResource;
use Portable\FilaCms\Filament\Resources\PageResource;
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

    /** Registered during app setup, so the route name lookup includes it. */
    protected function defineFormIndexRoute($router)
    {
        $router->get('/form-listing', fn () => '')->name(FormResource::getFrontendIndexRoute());
    }

    #[DefineRoute('defineFormIndexRoute')]
    public function test_index_url()
    {
        $indexRoute = FormResource::getFrontendIndexRoute();

        $menu = Menu::factory()->create();
        $data = MenuItem::factory()->create([
            'menu_id' => $menu->id,
            'type' => 'index-page',
            'reference_page' => FormResource::class,
        ]);

        $this->assertEquals(route($indexRoute), $data->url);
    }

    /**
     * PageResource registers no frontend routes (empty prefix) and FormResource opts out of
     * an index route, yet both are offered as menu item targets. Resolving the url used to
     * throw RouteNotFoundException, 500ing every page that rendered the menu.
     */
    public function test_index_url_without_a_registered_route()
    {
        $menu = Menu::factory()->create();

        foreach ([PageResource::class, FormResource::class] as $resourceClass) {
            $data = MenuItem::factory()->create([
                'menu_id' => $menu->id,
                'type' => 'index-page',
                'reference_page' => $resourceClass,
            ]);

            $this->assertFalse(Route::has($resourceClass::getFrontendIndexRoute()));
            $this->assertEquals('#', $data->url);
        }
    }

    public function test_page_url()
    {
        $page = Page::factory()->create();
        $menu = Menu::factory()->create();
        $data = MenuItem::factory()->create([
            'menu_id' => $menu->id,
            'type' => 'content',
            'reference_page' => PageResource::class,
            'reference_content' => $page->id
        ]);

        // PageResource has an empty frontend prefix, so pages live at the site root.
        $this->assertEquals('/' . $page->slug, $data->url);
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
