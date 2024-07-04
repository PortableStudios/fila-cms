<?php

namespace Portable\FilaCms\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Portable\FilaCms\Filament\Resources\PageResource;
use Portable\FilaCms\Models\Page;
use Portable\FilaCms\Models\Taxonomy;
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

        $model = Page::factory()->create();
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

        $model = Page::factory()->create();
        $model->terms()->delete();
        $model->terms()->attach($taxonomy->terms->pluck('id'));
        $model->refresh();

        $model->title = 'New Title';
        $model->save();
        $model->refresh();


        $this->assertEquals($taxonomy->terms->pluck('id')->toArray(), $model->property_ones_ids->toArray());
    }
}
