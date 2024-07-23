<?php

namespace Portable\FilaCms\Tests\Unit;

use Illuminate\Console\Events\CommandFinished;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Portable\FilaCms\Filament\Resources\PageResource;
use Portable\FilaCms\Models\Page;
use Portable\FilaCms\Models\Taxonomy;
use Portable\FilaCms\Tests\TestCase;

use function Pest\Laravel\artisan;

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

    public function test_no_stop_words()
    {
        $meili = app(\Laravel\Scout\EngineManager::class)->createMeilisearchDriver();
        $stopWords = $meili->index('pages')->getStopWords();
        $this->assertEquals([], $stopWords);
    }

    public function test_set_stop_words()
    {
        $stopWords = ['parrots'];
        config(['settings.search.stop-words' => json_encode($stopWords)]);

        artisan('fila-cms:sync-search')->assertExitCode(0);
        $input = new \Symfony\Component\Console\Input\ArrayInput([]);
        $output = new \Symfony\Component\Console\Output\BufferedOutput();
        Event::dispatch(new CommandFinished('fila-cms:sync-search', $input, $output, 0));

        $meili = app(\Laravel\Scout\EngineManager::class)->createMeilisearchDriver();
        $stopWords = $meili->index('pages')->getStopWords();
        $this->assertEquals(['parrots'], $stopWords);
    }
}
