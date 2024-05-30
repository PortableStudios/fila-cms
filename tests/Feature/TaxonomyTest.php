<?php

namespace Portable\FilaCms\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Portable\FilaCms\Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Portable\FilaCms\Models\Taxonomy;
use Schema;

class TaxonomyTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function test_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('taxonomies'));
        $this->assertTrue(Schema::hasTable('taxonomy_terms'));
    }

    public function test_can_add_taxonomy(): void
    {
        $taxonomy = Taxonomy::create(['name' => 'Taxonomy A']);
        $term = $taxonomy->terms()->create(['name' => 'Term A']);
        $this->assertModelExists($taxonomy);
        $this->assertModelExists($term);

        // test edit
        $taxonomy = Taxonomy::orderBy('id', 'DESC')->limit(1)->first();
        $taxonomy->update(['name' => 'Taxonomy Edited']);

        $this->assertDatabaseHas('taxonomies', [ 'name' => 'Taxonomy Edited' ]);
        $this->assertDatabaseMissing('taxonomies', [ 'name' => 'Taxonomy A' ]);

        $term = $taxonomy->terms()->first();
        $term->update(['name' => 'Term B']);

        $this->assertDatabaseHas('taxonomy_terms', [ 'name' => 'Term B' ]);
        $this->assertDatabaseMissing('taxonomy_terms', [ 'name' => 'Term A' ]);
    }
}
