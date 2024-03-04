<?php

namespace Portable\FilaCms\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Portable\FilaCms\Models\Author;
use Portable\FilaCms\Models\Page;
use Portable\FilaCms\Tests\TestCase;

class PageTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    protected $userModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userModel = config('auth.providers.users.model');
        $user = $this->userModel::create([
            'name'  => 'Jeremy Layson',
            'email' => 'jeremy.layson@portable.com.au',
            'password'  => 'password'
        ]);

        $this->be($user);

        Author::create([
            'first_name'    => 'Portable',
            'is_individual' => 0
        ]);

    }
    public function test_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('pages'));
    }

    public function test_can_add_page(): void
    {
        $user = $this->userModel::first();

        $secondUser = $this->userModel::create([
            'name'  => 'John Santos',
            'email' => 'johnsantos@portable.com.au',
            'password'  => 'password'
        ]);

        $title = $this->faker->text;
        $page = Page::create([
            'title'     => $title,
            'is_draft'  => 1,
            'contents'  => $this->faker->paragraph(5),
        ]);

        $this->assertModelExists($page);
        $this->assertDatabaseHas('pages', [ 'slug' => Str::slug($title) ]);
        $this->assertEquals($page->created_user_id, $user->id);

        $this->be($secondUser);
        $page->title = 'Edit';
        $page->save();
        $page = $page->fresh();

        $this->assertEquals($page->updated_user_id, $secondUser->id);
    }

    public function test_custom_slug(): void
    {
        $author = Author::first();
        $user = $this->userModel::first();

        $title = $this->faker->text;
        $page = Page::create([
            'title'     => $title,
            'slug'      => 'test-slug',
            'is_draft'  => 1,
            'contents'  => $this->faker->paragraph(5),
        ]);

        $this->assertDatabaseHas('pages', [ 'slug' => 'test-slug' ]);
        $this->assertDatabaseMissing('pages', [ 'slug' => Str::slug($title) ]);
    }

    public function test_draft_status(): void
    {
        $author = Author::first();
        $user = $this->userModel::first();

        $title = $this->faker->text;
        $page = Page::create([
            'title'     => $title,
            'is_draft'  => 1,
            'publish_at'    => now()->subDays(10),
            'expire_at'    => now()->addDays(10),
            'contents'      => $this->faker->paragraph(5),
        ]);

        $this->assertEquals($page->status, 'Draft');
    }

    public function test_published_status(): void
    {
        $author = Author::first();
        $user = $this->userModel::first();

        $title = $this->faker->text;
        $page = Page::create([
            'title'     => $title,
            'is_draft'  => 0,
            'publish_at'    => now()->subDays(10),
            'expire_at'    => now()->addDays(10),
            'contents'      => $this->faker->paragraph(5),
        ]);

        $this->assertEquals($page->status, 'Published');
    }

    public function test_expired_status(): void
    {
        $author = Author::first();
        $user = $this->userModel::first();

        $title = $this->faker->text;
        $page = Page::create([
            'title'     => $title,
            'is_draft'  => 0,
            'publish_at'    => now()->subDays(10),
            'expire_at'    => now()->subDays(10),
            'contents'      => $this->faker->paragraph(5),
        ]);

        $this->assertEquals($page->status, 'Expired');
    }

    public function test_pending_status(): void
    {
        $author = Author::first();
        $user = $this->userModel::first();

        $title = $this->faker->text;
        $page = Page::create([
            'title'     => $title,
            'is_draft'  => 0,
            'publish_at'    => now()->addDays(10),
            'expire_at'    => now()->addDays(10),
            'contents'      => $this->faker->paragraph(5),
        ]);

        $this->assertEquals($page->status, 'Pending');
    }
}
