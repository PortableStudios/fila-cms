<?php

namespace Portable\FilaCms\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Portable\FilaCms\Exceptions\InvalidStatusException;
use Portable\FilaCms\Models\Page;
use Portable\FilaCms\Tests\TestCase;

class AbstractContentModelTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected $author = null;

    public function test_duplicate_slugs_prevented()
    {
        $page = Page::factory()->create();
        $page2 = Page::factory()->create(['title' => $page->title, 'slug' => $page->slug]);

        $this->assertNotEquals($page->slug, $page2->slug);
    }

    public function test_can_edit_slugs_dont_change()
    {
        $page = Page::factory()->create();
        $originalSlug = $page->slug;
        $page->title = $this->faker->sentence;
        $page->save();
        $page->refresh();

        $this->assertEquals($originalSlug, $page->slug);
    }

    public function test_can_supply_slug()
    {
        $page = Page::factory()->create();
        $page->slug = 'test-slug';
        $page->save();
        $page->refresh();

        $this->assertEquals('test-slug', $page->slug);
    }

    public function test_with_pending()
    {
        $page = Page::factory()->create();
        $page->is_draft = false;
        $page->publish_at = now()->addDays(1);
        $page->expire_at = now()->addDays(10);
        $page->save();
        $page = Page::withoutGlobalScopes()->find($page->id);

        $this->assertEquals('Pending', $page->status);
        $this->assertNull(Page::find($page->id));
        $this->assertNotNull(Page::withPending()->where('id', $page->id)->first());
    }

    public function test_updated_by()
    {
        $page = Page::factory()->create();

        $this->assertEquals('SYSTEM', $page->updatedBy->name);
    }

    public function test_invalid_status()
    {
        $this->expectException(InvalidStatusException::class);
        $page = Page::factory()->create();
        DB::table('pages')->where('id', $page->id)->update(['is_draft' => 0, 'publish_at' => now()->addDays(1), 'expire_at' => now()->subDays(1)]);
        $page = Page::withoutGlobalScopes()->find($page->id);
        $page->status;
    }

    public function test_with_draft()
    {
        $page = Page::factory()->create();
        $page->is_draft = 1;
        $page->save();

        $this->assertEquals('Draft', $page->status);
        $this->assertNull(Page::find($page->id));
        $this->assertNotNull(Page::withDrafts()->where('id', $page->id)->first());
    }

    public function test_with_expired()
    {
        $page = Page::factory()->create();
        $page->is_draft = 0;
        $page->expire_at = now()->subDays(1);
        $page->publish_at = now()->subDays(2);
        $page->save();

        $this->assertEquals('Expired', $page->status);
        $this->assertNull(Page::find($page->id));
        $this->assertNotNull(Page::withExpired()->where('id', $page->id)->first());
    }
}
