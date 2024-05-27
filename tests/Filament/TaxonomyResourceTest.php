<?php

namespace Portable\FilaCms\Tests\Filament;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Portable\FilaCms\Facades\FilaCms;
use Portable\FilaCms\Filament\Resources\TaxonomyResource as TargetResource;
use Portable\FilaCms\Models\Page;
use Portable\FilaCms\Models\Taxonomy as TargetModel;
use Portable\FilaCms\Models\Taxonomy;
use Portable\FilaCms\Models\TaxonomyTerm;
use Portable\FilaCms\Tests\TestCase;
use Spatie\Permission\Models\Role;

class TaxonomyResourceTest extends TestCase
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
        $data = [];
        for ($i = 0; $i < 5; $i++) {
            $data[] = $this->generateModel();
        }

        Livewire::test(TargetResource\Pages\ListTaxonomies::class)->assertCanSeeTableRecords($data);
    }

    public function test_can_create_record(): void
    {
        Livewire::test(TargetResource\Pages\CreateTaxonomy::class)
            ->fillForm([
                'code' => fake()->regexify('[A-Z]{3}'),
                'name' => $this->faker->firstName,
                'taxonomy_resources' => array_keys(FilaCms::getContentModels())
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        Livewire::test(TargetResource\Pages\CreateTaxonomy::class)
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

        $data = TargetModel::first();

        $this->get(TargetResource::getUrl('edit', ['record' => $data]))->assertSuccessful();
    }

    public function test_can_retrieve_edit_data(): void
    {
        $this->generateModel();
        $data = TargetModel::first();

        Livewire::test(
            TargetResource\Pages\EditTaxonomy::class,
            ['record' => $data->getRouteKey()]
        )
            ->assertFormSet([
                'name'  => $data->name,
            ]);
    }

    public function test_can_save_form(): void
    {
        $data = $this->generateModel();

        $new = TargetModel::make([
            'name' => $this->faker->firstName,
        ]);

        Livewire::test(TargetResource\Pages\EditTaxonomy::class, [
            'record' => $data->getRoutekey(),
        ])
        ->fillForm([
            'code' => fake()->regexify('[A-Z]{3}'),
            'name'  => $new->name,
            'taxonomy_resources' => array_keys(FilaCms::getContentModels())
        ])
        ->call('save')
        ->dumpSession()
        ->assertHasNoFormErrors();

        $data->refresh();
        $this->assertEquals($data->name, $new->name);
    }

    public function test_cant_delete_with_terms_in_use()
    {
        $taxonomy = Taxonomy::factory()->create();
        $term = TaxonomyTerm::factory()->create([
            'taxonomy_id' => $taxonomy->id
        ]);
        $page = Page::factory()->create();
        $term->taxonomyables()->create([
            'taxonomyable_id' => $page->id,
            'taxonomyable_type' => Page::class
        ]);

        $livewireResponse = Livewire::test(TargetResource\Pages\EditTaxonomy::class, [
            'record' => $taxonomy->getRouteKey()
            ])
            ->call('mountAction', 'delete')
            ->call('callMountedAction');

        $livewireResponse->assertSessionHas('filament.notifications', function ($notifications) {
            return collect($notifications)->first()['title'] === 'Unable to delete Taxonomy';
        });

        $taxonomy = Taxonomy::find($taxonomy->id);
        $this->assertNotNull($taxonomy);
    }


    public function test_can_delete_without_terms_in_use()
    {
        $taxonomy = Taxonomy::factory()->create();
        $term = TaxonomyTerm::factory()->create([
            'taxonomy_id' => $taxonomy->id
        ]);

        $livewireResponse = Livewire::test(TargetResource\Pages\EditTaxonomy::class, [
            'record' => $taxonomy->getRouteKey()
            ])
            ->call('mountAction', 'delete')
            ->call('callMountedAction');

        $livewireResponse->assertSessionHas('filament.notifications', function ($notifications) {
            return collect($notifications)->first()['title'] === 'Deleted';
        });

        $taxonomy = Taxonomy::find($taxonomy->id);
        $this->assertNull($taxonomy);
        $term = TaxonomyTerm::find($term->id);
        $this->assertNull($term);
    }

    public function generateModel(): TargetModel
    {
        return TargetModel::create([
            'name' => $this->faker->firstName,
            'taxonomy_resources' => array_keys(FilaCms::getContentModels())
        ]);
    }
}
