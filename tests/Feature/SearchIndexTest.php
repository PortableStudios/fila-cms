<?php

namespace Portable\FilaCms\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Portable\FilaCms\Filament\Resources\PageResource;
use Portable\FilaCms\Models\Page;
use Portable\FilaCms\Models\Setting;
use Portable\FilaCms\Models\Taxonomy;

use Portable\FilaCms\Tests\TestCase;

class SearchIndexTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected $author = null;

    public function test_retrieves_tags()
    {
        Taxonomy::factory()->withTerms()->forResources([
            PageResource::class
        ])->create(['name' => 'property_one']);

        $searchableAttributes = Page::getSearchableAttributes();
        $this->assertContains('property_ones', $searchableAttributes);
        $filterableAttributes = Page::getFilterableAttributes();
        $this->assertContains('property_ones_ids', $filterableAttributes);
    }

    public function test_without_tags()
    {
        $searchableAttributes = Page::getSearchableAttributes();
        $this->assertEquals(['title','contents'], $searchableAttributes);
        $filterableAttributes = Page::getFilterableAttributes();
        $this->assertEquals([], $filterableAttributes);
    }

    public function test_stop_search()
    {
        Page::factory()->isPublished()->create(['title' => 'flighty parrots']);
        Page::factory()->isPublished()->create(['title' => 'flighty finches']);

        $flightyParrotSearch = Page::search('flighty parrots')->raw();
        $this->assertEquals($flightyParrotSearch['query'], 'flighty parrots');
        $flightySearch = Page::search('flighty')->raw();
        $this->assertEquals($flightySearch['query'], 'flighty');

        $stopWords = ['parrots'];
        Setting::set('settings.search.stop_words', json_encode($stopWords));

        $flightyParrotSearch = Page::search('flighty parrots')->raw();
        $this->assertEquals($flightyParrotSearch['query'], 'flighty');
        $flightySearch = Page::search('flighty')->raw();
        $this->assertEquals($flightySearch['query'], 'flighty');
    }
}
