<?php

namespace Portable\FilaCms\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Portable\FilaCms\Models\Author;
use Portable\FilaCms\Models\Page;
use Portable\FilaCms\Tests\TestCase;

class PageEventsTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected $author = null;

    public function test_create_sets_publish()
    {
        $author = Author::create([
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'is_individual' => 1
        ]);

        $data = [
            'title'     => $this->faker->words(15, true),
            'is_draft'  => 0,
            'contents'  => $this->faker->words($this->faker->numberBetween(50, 150), true),
            'author_Id' => $author->id,
        ];
        $page = Page::create($data);

        $this->assertNotNull($page->publish_at);
    }

    public function test_update_sets_publish()
    {
        $author = Author::create([
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'is_individual' => 1
        ]);
        $data = [
            'title'     => $this->faker->words(15, true),
            'is_draft'  => 1,
            'contents'  => $this->faker->words($this->faker->numberBetween(50, 150), true),
            'author_Id' => $author->id,
        ];
        $page = Page::create($data);
        $this->assertNull($page->publish_at);
        $page->is_draft = 0;
        $page->save();
        $this->assertNotNull($page->publish_at);
    }

    public function test_update_sets_slug()
    {
        $author = Author::create([
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'is_individual' => 1
        ]);

        $data = [
            'title'     => $this->faker->words(15, true),
            'is_draft'  => 0,
            'contents'  => $this->faker->words($this->faker->numberBetween(50, 150), true),
            'author_Id' => $author->id,
        ];
        $page = Page::create($data);
        $this->assertNotNull($page->slug);
        $newTitle = $this->faker->words(15, true);
        $page->title = $newTitle;
        $page->slug = null;
        $page->save();
        $this->assertModelExists($page);
        $this->assertDatabaseHas('pages', ['slug' => Str::slug($newTitle)]);
    }
}
