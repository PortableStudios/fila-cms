<?php

namespace Portable\FilaCms\Tests\Filament;

use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Mockery;
use Portable\FilaCms\Filament\Resources\TaxonomyResource\Pages\EditTaxonomy;
use Portable\FilaCms\Filament\Resources\TaxonomyResource\RelationManagers\TermsRelationManager;
use Portable\FilaCms\Models\Page;
use Portable\FilaCms\Models\TaxonomyTerm;
use Portable\FilaCms\Tests\Factories\TaxonomyFactory;
use Portable\FilaCms\Tests\TestCase;
use Spatie\Permission\Models\Role;

class TaxonomyTermResourceTest extends TestCase
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
        $taxonomy = TaxonomyFactory::new()->create();
        $data = [];
        for ($i = 0; $i < 5; $i++) {
            $data[] = TaxonomyTerm::factory()->create(['taxonomy_id' => $taxonomy->id]);
        }

        Livewire::test(TermsRelationManager::class, [
            'ownerRecord' => $taxonomy,
            'pageClass' => EditTaxonomy::class
        ])->assertCanSeeTableRecords($data);
    }

    public function test_cant_delete_terms_in_use()
    {
        $taxonomy = TaxonomyFactory::new()->create();
        $term = TaxonomyTerm::factory()->create(['taxonomy_id' => $taxonomy->id]);
        $page = Page::factory()->create();
        $term->taxonomyables()->create(['taxonomyable_id' => $page->id, 'taxonomyable_type' => Page::class]);


        Livewire::test(TermsRelationManager::class, [
            'ownerRecord' => $taxonomy,
            'pageClass' => EditTaxonomy::class
        ])->callTableAction(DeleteAction::class, $term->id);

        $this->assertDatabaseHas('taxonomy_terms', ['id' => $term->id]);
    }

    public function test_can_delete_terms_not_in_use()
    {
        $taxonomy = TaxonomyFactory::new()->create();
        $term = TaxonomyTerm::factory()->create(['taxonomy_id' => $taxonomy->id]);

        Livewire::test(TermsRelationManager::class, [
            'ownerRecord' => $taxonomy,
            'pageClass' => EditTaxonomy::class
        ])->callTableAction(DeleteAction::class, $term->id);

        $this->assertSoftDeleted('taxonomy_terms', ['id' => $term->id]);
    }

    public function test_can_reorder_taxonomy_terms()
    {

        $taxonomy = TaxonomyFactory::new()->create();

        // Step 1: Create some TaxonomyTerms
        $term1 = TaxonomyTerm::factory()->create(['taxonomy_id' => $taxonomy->id]);
        $term2 = TaxonomyTerm::factory()->create(['taxonomy_id' => $taxonomy->id]);
        $term3 = TaxonomyTerm::factory()->create(['taxonomy_id' => $taxonomy->id]);

        // Assert initial order
        expect($term1->fresh()->order)->toBe(1);
        expect($term2->fresh()->order)->toBe(2);
        expect($term3->fresh()->order)->toBe(3);

        // Step 2: Reorder the terms
        Livewire::test(TermsRelationManager::class, [
            'ownerRecord' => $taxonomy,
            'pageClass' => EditTaxonomy::class
        ])->call('reorderTable', ['3', '2', '1']);

        // Step 3: Check the default order
        $terms = $taxonomy->terms()->get();

        expect($terms->pluck('id')->toArray())->toBe([
            $term3->id, // Should now be first
            $term2->id, // Should now be second
            $term1->id, // Should now be third
        ]);
    }
}
