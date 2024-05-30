<?php

namespace Portable\FilaCms\Tests\Feature;

use Schema;
use Illuminate\Support\Str;
use Portable\FilaCms\Models\Page;
use Portable\FilaCms\Tests\TestCase;
use Portable\FilaCms\Models\ShortUrl;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ShortUrlTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    private function _table_name(): string
    {
        return (new ShortUrl())->getTable();
    }

    public function test_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('short_urls'));
    }

    public function test_can_add_short_url(): void
    {
        $page = Page::factory()->create();
        $sampleUrl = Str::slug(Str::random(10));
        $page->shortUrls()->create([
            'url' => $sampleUrl
        ]);
        $this->assertDatabaseHas($this->_table_name(), [ 'url' => $sampleUrl ]);
    }

    public function test_can_add_multiple_urls(): void
    {
        $page = Page::factory()->create();
        $sampleUrl = Str::slug(Str::random(10));
        $sampleUrl2 = Str::slug(Str::random(10));

        $page->shortUrls()->createMany([
            [
                'url' => $sampleUrl
            ],
            [
                'url' => $sampleUrl2
            ]
        ]);

        $this->assertDatabaseHas($this->_table_name(), [ 'url' => $sampleUrl ]);
        $this->assertDatabaseHas($this->_table_name(), [ 'url' => $sampleUrl2 ]);
    }

    public function test_duplicate_values(): void
    {
        $page = Page::factory()->create();
        $sampleUrl = Str::slug(Str::random(10));

        $page->shortUrls()->create([
            'url' => $sampleUrl
        ]);

        $newPage = Page::factory()->create();
        try {
            $newPage->shortUrls()->create([
                'url' => $sampleUrl
            ]);
        } catch (\Exception $e) {
            // Check if the exception thrown is due to a unique constraint violation
            $this->assertInstanceOf(\Illuminate\Database\QueryException::class, $e);
            $this->assertStringContainsString('UNIQUE', $e->getMessage());
        }
    }
}
