<?php

namespace Portable\FilaCms\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Portable\FilaCms\Filament\Resources\PageResource;
use Portable\FilaCms\Models\Page;
use Portable\FilaCms\Models\Taxonomy;
use Portable\FilaCms\Models\TaxonomyResource;
use Portable\FilaCms\Tests\TestCase;

class HasTaxonomiesTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected $author = null;

    public function test_retrieves_tags()
    {
        $taxonomy = Taxonomy::factory()->withTerms()->forResources([
            PageResource::class
        ])->create(['name' => 'property_one']);

        $model = Page::factory()->isPublished()->create();
        $model->terms()->delete();
        $model->terms()->attach($taxonomy->terms->pluck('id'));
        $model->refresh();

        $this->assertEquals($taxonomy->terms->pluck('id')->toArray(), $model->property_ones_ids->toArray());
    }

    public function test_save_keeps_tags()
    {
        $taxonomy = Taxonomy::factory()->withTerms()->forResources([
            PageResource::class
        ])->create(['name' => 'property_one']);

        $model = Page::factory()->isPublished()->create();
        $model->terms()->delete();
        $model->terms()->attach($taxonomy->terms->pluck('id'));
        $model->refresh();

        $model->title = 'New Title';
        $model->save();
        $model->refresh();


        $this->assertEquals($taxonomy->terms->pluck('id')->toArray(), $model->property_ones_ids->toArray());
    }

    public function test_initialize_skips_soft_deleted_taxonomy()
    {
        $taxonomy = Taxonomy::factory()->forResources([
            PageResource::class
        ])->create(['name' => 'soft_deleted_tag']);

        // Soft-delete the taxonomy, leaving the taxonomy_resources row as a dangling reference
        $taxonomy->delete();

        // Instantiating a model that uses HasTaxonomies must not throw an error
        $this->assertNull((new Page())->soft_deleted_tags_ids ?? null);
    }

    public function test_soft_delete_taxonomy_cascades_to_resources()
    {
        $taxonomy = Taxonomy::factory()->forResources([
            PageResource::class
        ])->create(['name' => 'cascade_tag']);

        $this->assertDatabaseHas('taxonomy_resources', ['taxonomy_id' => $taxonomy->id]);

        $taxonomy->delete();

        $this->assertDatabaseMissing('taxonomy_resources', ['taxonomy_id' => $taxonomy->id]);
    }
}
